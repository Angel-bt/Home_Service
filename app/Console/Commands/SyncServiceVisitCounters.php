<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ServiceVisitCounter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

final class SyncServiceVisitCounters extends Command
{
    protected $signature = 'services:sync-visit-counters {--chunk=500 : Maximum services synchronized per batch}';

    protected $description = 'Synchronize pending Redis service visit counters to MySQL.';

    public function handle(ServiceVisitCounter $counter): int
    {
        $deltas = [];

        foreach (array_slice($counter->pendingServiceIds(), 0, (int) $this->option('chunk')) as $serviceId) {
            $visits = $counter->claim($serviceId);

            if ($visits > 0) {
                $deltas[$serviceId] = $visits;
            }
        }

        if ($deltas === []) {
            $this->info('No pending service visits.');

            return self::SUCCESS;
        }

        try {
            DB::transaction(function () use ($deltas): void {
                $whenClauses = [];
                $bindings = [];

                foreach ($deltas as $serviceId => $visits) {
                    $whenClauses[] = 'WHEN ? THEN ?';
                    $bindings[] = $serviceId;
                    $bindings[] = $visits;
                }

                $placeholders = implode(', ', array_fill(0, count($deltas), '?'));
                $bindings = [...$bindings, ...array_keys($deltas)];
                $sql = sprintf(
                    'UPDATE services SET views = views + CASE id %s ELSE 0 END WHERE id IN (%s)',
                    implode(' ', $whenClauses),
                    $placeholders
                );

                DB::update($sql, $bindings);
            });
        } catch (Throwable $exception) {
            foreach ($deltas as $serviceId => $visits) {
                $counter->restore($serviceId, $visits);
            }

            report($exception);
            $this->error('Synchronization failed; Redis counters were restored.');

            return self::FAILURE;
        }

        foreach (array_keys($deltas) as $serviceId) {
            $counter->forgetWhenEmpty($serviceId);
        }

        $this->info(sprintf('Synchronized %d visits for %d services.', array_sum($deltas), count($deltas)));

        return self::SUCCESS;
    }
}
