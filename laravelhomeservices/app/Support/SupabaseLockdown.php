<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Supabase publica las tablas del esquema en una API web (PostgREST) para los roles anon y
 * authenticated. Malakplace no usa esa API: se conecta directo a Postgres. Esto la cierra:
 * activa RLS (sin políticas: esos roles no ven nada) y les quita los permisos.
 *
 * Seguro para la app: RLS no se aplica al dueño de la tabla, y solo se activa en las tablas cuyo
 * dueño es el usuario con el que se conecta Malakplace. Es idempotente; lo corren la migración
 * 2026_11_11_000001 y el cron diario (security:check), así que también cubre tablas nuevas.
 * En MySQL, SQLite o un Postgres sin los roles de Supabase no hace nada.
 */
final class SupabaseLockdown
{
    public static function applies(): bool
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return false;
        }

        return (int) DB::selectOne("select count(*) as total from pg_roles where rolname in ('anon', 'authenticated')")->total === 2;
    }

    /**
     * @return int  tablas en las que se activó RLS ahora
     */
    public static function apply(): int
    {
        if (! self::applies()) {
            return 0;
        }

        $tables = DB::select('select tablename from pg_tables where schemaname = current_schema() and tableowner = current_user and not rowsecurity');
        foreach ($tables as $table) {
            DB::statement('alter table ' . self::quote($table->tablename) . ' enable row level security');
        }

        $schema = self::quote((string) DB::selectOne('select current_schema() as name')->name);
        DB::statement("revoke all on all tables in schema {$schema} from anon, authenticated");
        DB::statement("revoke all on all sequences in schema {$schema} from anon, authenticated");
        // Las tablas que se creen después tampoco reciben permisos para esos roles.
        DB::statement("alter default privileges in schema {$schema} revoke all on tables from anon, authenticated");
        DB::statement("alter default privileges in schema {$schema} revoke all on sequences from anon, authenticated");

        return count($tables);
    }

    private static function quote(string $identifier): string
    {
        return '"' . str_replace('"', '""', $identifier) . '"';
    }
}
