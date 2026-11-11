<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Autocomprobación de seguridad (la corre el cron diario, que responde 500 si falla):
 * modo depuración apagado, clave de la app, cookies seguras y, en Supabase, que ninguna
 * tabla quede expuesta a la API pública. Antes de revisar, cierra las tablas nuevas
 * (App\Support\SupabaseLockdown), así una tabla agregada después queda protegida al día siguiente.
 */
class CheckSecurity extends Command
{
    protected $signature = 'security:check';

    protected $description = 'Comprueba que la configuración y la base no expongan nada';

    public function handle(): int
    {
        $problems = [];

        if (app()->environment('production')) {
            if (config('app.debug')) {
                $problems[] = 'APP_DEBUG está activado en producción: los errores mostrarían datos internos.';
            }
            if (! config('session.secure')) {
                $problems[] = 'SESSION_SECURE_COOKIE no está activado: la cookie de sesión podría viajar sin cifrar.';
            }
        }

        if (blank(config('app.key'))) {
            $problems[] = 'Falta APP_KEY.';
        }

        $problems = [...$problems, ...$this->exposedTables()];

        foreach ($problems as $problem) {
            $this->error($problem);
            logger()->error('security:check ' . $problem);
        }

        if ($problems === []) {
            $this->info('security:check ok');

            return self::SUCCESS;
        }

        return self::FAILURE;
    }

    /**
     * @return list<string>
     */
    private function exposedTables(): array
    {
        $db = DB::connection();
        if ($db->getDriverName() !== 'pgsql') {
            return [];
        }

        try {
            if (($fixed = \App\Support\SupabaseLockdown::apply()) > 0) {
                $this->warn("Se activó RLS en {$fixed} tablas nuevas.");
            }

            if ($db->selectOne("select count(*) as total from pg_roles where rolname = 'anon'")->total == 0) {
                return []; // No es Supabase: no hay API pública que proteger.
            }

            $withoutRls = array_column(array_map(fn ($row) => (array) $row, $db->select(
                'select tablename from pg_tables where schemaname = current_schema() and not rowsecurity order by tablename'
            )), 'tablename');

            $granted = array_column(array_map(fn ($row) => (array) $row, $db->select(
                "select distinct table_name from information_schema.role_table_grants where table_schema = current_schema() and grantee in ('anon', 'authenticated') order by table_name"
            )), 'table_name');
        } catch (Throwable $exception) {
            return ['No se pudo revisar la base: ' . $exception->getMessage()];
        }

        $problems = [];
        if ($withoutRls !== []) {
            $problems[] = 'Tablas sin RLS que no se pudieron cerrar (su dueño no es el usuario de la app): ' . implode(', ', $withoutRls) . '. Actívalo desde el panel de Supabase.';
        }
        if ($granted !== []) {
            $problems[] = 'Tablas con permisos para anon/authenticated: ' . implode(', ', $granted) . '.';
        }

        return $problems;
    }
}
