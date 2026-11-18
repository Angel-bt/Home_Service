<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\DatabaseBackup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use Throwable;

/**
 * Copia diaria de la base (la corre /cron/diario). Con --test-restore, además la restaura en un
 * esquema temporal para probar que de verdad sirve. Deja el resultado para «Estado del sistema».
 */
final class BackupDatabase extends Command
{
    protected $signature = 'backup:run {--test-restore : Restaurarla en un esquema temporal para comprobar que sirve}';

    protected $description = 'Hace una copia cifrada de la base en el almacén privado y borra las de más de 30 días';

    public function handle(DatabaseBackup $backup): int
    {
        $status = ['at' => now()->toIso8601String(), 'ok' => false];

        try {
            $copy = $backup->create();
            $status += ['name' => $copy['name'], 'size' => $copy['size'], 'rows' => $copy['rows']];
            $this->info("Copia {$copy['name']}: {$copy['rows']} filas de " . count($copy['tables']) . ' tablas, ' . Number::fileSize($copy['size']) . '.');

            if ($this->option('test-restore')) {
                $restored = $backup->testRestore($copy['name']);
                $status['restore_tested_at'] = now()->toIso8601String();
                $this->info('Restauración de prueba: ' . array_sum($restored) . ' filas, igual que la copia.');
            } else {
                $status['restore_tested_at'] = Cache::get('system:last_backup')['restore_tested_at'] ?? null;
            }

            $status['ok'] = true;
        } catch (Throwable $exception) {
            report($exception);
            Log::error('backup:run: ' . $exception->getMessage());
            $this->error('La copia falló: ' . $exception->getMessage());
            $status['error'] = $exception->getMessage();
            $status['restore_tested_at'] = Cache::get('system:last_backup')['restore_tested_at'] ?? null;
            $status['last_ok'] = Cache::get('system:last_backup')['last_ok'] ?? null;
        }

        if ($status['ok']) {
            $status['last_ok'] = $status['at'];
        }

        try {
            Cache::forever('system:last_backup', $status);
        } catch (Throwable $exception) {
            report($exception);
        }

        return $status['ok'] ? self::SUCCESS : self::FAILURE;
    }
}
