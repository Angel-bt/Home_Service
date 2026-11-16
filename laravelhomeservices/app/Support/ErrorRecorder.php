<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * Guarda cada error del sitio en `error_logs` para verlo en /admin/errors sin entrar a Vercel.
 * Los repetidos (misma clase, archivo y línea) se agrupan y suman. Si la base no responde, no
 * hace nada: el error ya queda en el registro del servidor y esto nunca debe causar otro.
 */
final class ErrorRecorder
{
    private static bool $busy = false;

    public static function capture(Throwable $exception): void
    {
        if (self::$busy) {
            return;
        }
        self::$busy = true;

        try {
            $location = Str::after($exception->getFile(), base_path() . DIRECTORY_SEPARATOR) . ':' . $exception->getLine();
            $fingerprint = hash('sha256', $exception::class . '|' . $location);
            $now = now();
            $url = app()->runningInConsole() ? 'consola: ' . implode(' ', array_slice($_SERVER['argv'] ?? [], 1, 3)) : request()->method() . ' ' . request()->path();
            $message = Str::limit($exception->getMessage() ?: $exception::class, 1000);

            $updated = DB::table('error_logs')->where('fingerprint', $fingerprint)->update([
                'count' => DB::raw('count + 1'),
                'message' => $message,
                'url' => Str::limit($url, 250),
                'last_seen_at' => $now,
                'resolved_at' => null,
            ]);

            if ($updated === 0) {
                DB::table('error_logs')->insert([
                    'fingerprint' => $fingerprint,
                    'exception' => Str::limit($exception::class, 250),
                    'message' => $message,
                    'location' => Str::limit($location, 250),
                    'url' => Str::limit($url, 250),
                    'count' => 1,
                    'first_seen_at' => $now,
                    'last_seen_at' => $now,
                ]);
            }
        } catch (Throwable) {
            // Sin base de datos no hay dónde guardarlo; el servidor ya lo registró.
        } finally {
            self::$busy = false;
        }
    }
}
