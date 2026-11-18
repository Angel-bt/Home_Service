<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Connection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Copias de seguridad de la base, sin herramientas externas (Vercel no tiene pg_dump).
 *
 * Cada copia es un único archivo con todas las tablas que importan, comprimido y cifrado con
 * APP_KEY, guardado en el disco privado `backups` (fuera de la base). Al guardarla se vuelve a leer
 * para comprobar que se puede abrir; la prueba de restauración la carga entera en un esquema
 * temporal de Postgres y compara fila por fila la cantidad. Se guardan KEEP_DAYS días.
 *
 * Sin APP_KEY no se puede abrir ninguna copia: debe guardarse también fuera de Vercel.
 */
final class DatabaseBackup
{
    /** Tablas que se rehacen solas: sesiones, caché y colas. */
    public const SKIP = ['sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs'];

    public const KEEP_DAYS = 30;

    private const FORMAT = 1;

    private const PREFIX = 'malakplace-';

    private const EXTENSION = '.copia';

    public function __construct(private readonly ?Connection $connection = null) {}

    public static function disk(): Filesystem
    {
        return Storage::disk('backups');
    }

    /**
     * Hace la copia, la guarda, la vuelve a leer y borra las vencidas.
     *
     * @return array{name: string, size: int, tables: array<string, int>, rows: int}
     */
    public function create(): array
    {
        $tables = [];
        foreach ($this->tables() as $table) {
            $tables[$table] = $this->db()->table($table)->get()->map(fn ($row) => (array) $row)->all();
        }

        $json = json_encode([
            'format' => self::FORMAT,
            'created_at' => now()->toIso8601String(),
            'driver' => $this->db()->getDriverName(),
            'tables' => $tables,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);

        $name = self::PREFIX . now()->format('Y-m-d-His') . self::EXTENSION;
        $payload = Crypt::encryptString((string) gzencode($json, 9));
        if (! self::disk()->put($name, $payload)) {
            throw new RuntimeException('No se pudo guardar la copia en el almacén (ver «Fotos y documentos» en Estado del sistema).');
        }

        $counts = array_map('count', $tables);
        $saved = $this->read($name);
        if (array_map('count', $saved['tables']) !== $counts) {
            throw new RuntimeException('La copia guardada no coincide con la base: se volvió a leer y faltan filas.');
        }

        $this->prune();

        return ['name' => $name, 'size' => strlen($payload), 'tables' => $counts, 'rows' => array_sum($counts)];
    }

    /**
     * Copias guardadas, de la más nueva a la más vieja.
     *
     * @return list<array{name: string, size: int, date: Carbon}>
     */
    public function list(): array
    {
        $disk = self::disk();

        return collect($disk->files())
            ->filter(fn (string $file) => self::dateOf($file) !== null)
            ->sortDesc()
            ->values()
            ->map(fn (string $file) => ['name' => $file, 'size' => (int) $disk->size($file), 'date' => self::dateOf($file)])
            ->all();
    }

    /**
     * Abre una copia. Falla con un mensaje claro si el archivo no es una copia o la clave cambió.
     *
     * @return array{format: int, created_at: string, driver: string, tables: array<string, list<array<string, mixed>>>}
     */
    public function read(string $name): array
    {
        $raw = self::disk()->get($name);
        if ($raw === null) {
            throw new RuntimeException('No se encontró la copia ' . $name . ' en el almacén.');
        }

        try {
            $json = gzdecode(Crypt::decryptString($raw));
        } catch (Throwable $exception) {
            throw new RuntimeException('No se pudo abrir la copia ' . $name . ' (¿cambió APP_KEY?): ' . $exception->getMessage(), 0, $exception);
        }

        $data = json_decode((string) $json, true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($data) || ($data['format'] ?? null) !== self::FORMAT || ! is_array($data['tables'] ?? null)) {
            throw new RuntimeException('El archivo ' . $name . ' no es una copia de Malakplace.');
        }

        return $data;
    }

    /**
     * Prueba de restauración: carga la copia entera en un esquema temporal de Postgres con la misma
     * estructura que las tablas reales, cuenta las filas y borra el esquema. No toca los datos reales.
     * En otros motores solo comprueba que la copia se abre completa.
     *
     * @return array<string, int> filas restauradas por tabla
     */
    public function testRestore(string $name): array
    {
        $data = $this->read($name);

        if ($this->db()->getDriverName() !== 'pgsql') {
            return array_map('count', $data['tables']);
        }

        $source = $this->db()->getSchemaBuilder()->getCurrentSchemaName();
        $schema = 'prueba_restauracion_' . Str::lower(Str::random(8));
        $quote = fn (string $identifier) => '"' . str_replace('"', '""', $identifier) . '"';
        $live = $this->db()->getSchemaBuilder()->getTableListing($source, false);

        $this->db()->statement('create schema ' . $quote($schema));

        try {
            $restored = [];
            foreach ($data['tables'] as $table => $rows) {
                if (! in_array($table, $live, true)) {
                    continue;
                }
                $this->db()->statement('create table ' . $quote($schema) . '.' . $quote($table) . ' (like ' . $quote($source) . '.' . $quote($table) . ' including all)');
                $this->insert($schema . '.' . $table, $rows, $this->db()->getSchemaBuilder()->getColumnListing($table));
                $restored[$table] = (int) $this->db()->table($schema . '.' . $table)->count();

                if ($restored[$table] !== count($rows)) {
                    throw new RuntimeException("La tabla {$table} se restauró con {$restored[$table]} filas de " . count($rows) . '.');
                }
            }

            return $restored;
        } finally {
            $this->db()->statement('drop schema if exists ' . $quote($schema) . ' cascade');
        }
    }

    /**
     * Restauración real: reemplaza el contenido de las tablas de esta base por el de la copia.
     * Borra los datos actuales. Solo desde la consola y con confirmación (`backup:restore --real`).
     *
     * @return array<string, int> filas restauradas por tabla
     */
    public function restore(string $name): array
    {
        $data = $this->read($name);
        $schema = $this->db()->getSchemaBuilder();
        $tables = array_values(array_filter($this->ordered(array_keys($data['tables'])), fn (string $table) => $schema->hasTable($table)));

        return $this->db()->transaction(function () use ($data, $tables, $schema) {
            foreach (array_reverse($tables) as $table) {
                $this->db()->table($table)->delete();
            }

            $restored = [];
            foreach ($tables as $table) {
                $this->insert($table, $data['tables'][$table], $schema->getColumnListing($table));
                $restored[$table] = count($data['tables'][$table]);

                if ($this->db()->getDriverName() === 'pgsql' && in_array('id', $schema->getColumnListing($table), true)) {
                    // Que los próximos registros sigan después del último id restaurado.
                    $this->db()->select("select setval(pg_get_serial_sequence(?, 'id'), coalesce(max(id), 1), max(id) is not null) from " . $this->db()->getQueryGrammar()->wrapTable($table), [$table]);
                }
            }

            return $restored;
        });
    }

    /**
     * Fecha de una copia a partir de su nombre, o null si el archivo no es una copia.
     */
    public static function dateOf(string $name): ?Carbon
    {
        if (! preg_match('/^' . preg_quote(self::PREFIX, '/') . '(\d{4}-\d{2}-\d{2}-\d{6})' . preg_quote(self::EXTENSION, '/') . '$/', $name, $match)) {
            return null;
        }

        return Carbon::createFromFormat('Y-m-d-His', $match[1]);
    }

    /**
     * Borra las copias de más de KEEP_DAYS días, pero nunca la más nueva.
     */
    private function prune(): void
    {
        $limit = now()->subDays(self::KEEP_DAYS);

        foreach (array_slice($this->list(), 1) as $backup) {
            if ($backup['date']->lt($limit)) {
                self::disk()->delete($backup['name']);
            }
        }
    }

    /**
     * @return list<string>
     */
    private function tables(): array
    {
        $schema = $this->db()->getSchemaBuilder();
        $tables = array_diff($schema->getTableListing($schema->getCurrentSchemaName(), false), self::SKIP);
        sort($tables);

        return $tables;
    }

    /**
     * Las tablas ordenadas para que cada una vaya después de las que referencia.
     *
     * @param  list<string>  $tables
     * @return list<string>
     */
    private function ordered(array $tables): array
    {
        $schema = $this->db()->getSchemaBuilder();
        $depends = [];
        foreach ($tables as $table) {
            $depends[$table] = $schema->hasTable($table)
                ? array_values(array_intersect(array_column($schema->getForeignKeys($table), 'foreign_table'), $tables))
                : [];
        }

        $ordered = [];
        $visit = function (string $table, array $path) use (&$visit, &$ordered, $depends): void {
            if (in_array($table, $ordered, true) || in_array($table, $path, true)) {
                return;
            }
            foreach ($depends[$table] as $parent) {
                $visit($parent, [...$path, $table]);
            }
            $ordered[] = $table;
        };
        foreach ($tables as $table) {
            $visit($table, []);
        }

        return $ordered;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @param  list<string>  $columns  columnas que existen hoy: las de copias viejas que ya no existen se ignoran
     */
    private function insert(string $table, array $rows, array $columns): void
    {
        $allowed = array_flip($columns);

        foreach (array_chunk($rows, 200) as $chunk) {
            $this->db()->table($table)->insert(array_map(fn (array $row) => array_intersect_key($row, $allowed), $chunk));
        }
    }

    private function db(): Connection
    {
        return $this->connection ?? DB::connection();
    }
}
