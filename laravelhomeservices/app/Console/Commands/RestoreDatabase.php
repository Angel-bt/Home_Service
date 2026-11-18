<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\DatabaseBackup;
use Illuminate\Console\Command;
use Illuminate\Support\Number;

/**
 * Lista y restaura copias. Sin --real solo prueba la restauración (no toca los datos).
 * Con --real reemplaza todos los datos de la base conectada: es para recuperar el sitio tras un desastre.
 */
final class RestoreDatabase extends Command
{
    protected $signature = 'backup:restore
        {name? : Archivo de la copia (por defecto, la más nueva)}
        {--list : Solo mostrar las copias guardadas}
        {--real : Reemplazar los datos de esta base por los de la copia}';

    protected $description = 'Prueba o hace la restauración de una copia de la base';

    public function handle(DatabaseBackup $backup): int
    {
        $copies = $backup->list();

        if ($this->option('list')) {
            $this->table(['Copia', 'Fecha', 'Tamaño'], array_map(fn (array $copy) => [$copy['name'], $copy['date']->format('d/m/Y H:i'), Number::fileSize($copy['size'])], $copies));

            return self::SUCCESS;
        }

        $name = $this->argument('name') ?? ($copies[0]['name'] ?? null);
        if ($name === null) {
            $this->error('No hay copias guardadas todavía.');

            return self::FAILURE;
        }

        if (! $this->option('real')) {
            $restored = $backup->testRestore($name);
            $this->info("Restauración de prueba de {$name}: " . array_sum($restored) . ' filas en ' . count($restored) . ' tablas. Los datos reales no se tocaron.');

            return self::SUCCESS;
        }

        $database = config('database.connections.' . config('database.default') . '.host') ?: config('database.connections.' . config('database.default') . '.database');
        $this->warn("Esto BORRA todos los datos de la base «{$database}» y los reemplaza por los de {$name}.");
        if (! $this->confirm('¿Seguro? Escribe «yes» para continuar', false)) {
            $this->info('Cancelado. No se tocó nada.');

            return self::FAILURE;
        }

        $restored = $backup->restore($name);
        $this->info('Restauradas ' . array_sum($restored) . ' filas en ' . count($restored) . ' tablas. Corre `php artisan cache:clear`.');

        return self::SUCCESS;
    }
}
