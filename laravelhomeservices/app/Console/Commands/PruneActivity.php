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

    protected $description = 'Borra la actividad, el historial y los errores más antiguos que su plazo';

    public function handle(): int
    {
        $days = (int) config('marketplace.activity_retention_days');
        $deleted = ActivityEvent::where('created_at', '<', now()->subDays($days))->delete();

        $this->info("Actividad borrada: {$deleted} registros de hace más de {$days} días.");

        // Historial de la administración: un año. Errores: revisados hace un mes, o sin repetirse en tres.
        $history = \Illuminate\Support\Facades\DB::table('admin_logs')->where('created_at', '<', now()->subYear())->delete();
        $errors = \Illuminate\Support\Facades\DB::table('error_logs')
            ->where(fn ($query) => $query->where('resolved_at', '<', now()->subMonth())->orWhere('last_seen_at', '<', now()->subMonths(3)))
            ->delete();
        $this->info("Historial borrado: {$history}. Errores borrados: {$errors}.");

        return self::SUCCESS;
    }
}
