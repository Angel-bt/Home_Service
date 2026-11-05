<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ActivityEvent;
use Illuminate\Console\Command;

/**
 * La actividad no se guarda para siempre: lo que dice la política de privacidad se cumple aquí.
 */
class PruneActivity extends Command
{
    protected $signature = 'activity:prune';

    protected $description = 'Borra la actividad más antigua que el plazo de conservación';

    public function handle(): int
    {
        $days = (int) config('marketplace.activity_retention_days');
        $deleted = ActivityEvent::where('created_at', '<', now()->subDays($days))->delete();

        $this->info("Actividad borrada: {$deleted} registros de hace más de {$days} días.");

        return self::SUCCESS;
    }
}
