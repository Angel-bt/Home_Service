<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Throwable;

/**
 * Guarda cada error del sitio en `error_logs` para verlo en /admin/errors sin entrar a Vercel.
 * Los repetidos (misma clase, archivo y línea) se agrupan y suman. Si la base no responde, no
 * hace nada: el error ya queda en el registro del servidor y esto nunca debe causar otro.
 */
final class ErrorRecorder
{
    public const ALERTS_PER_HOUR = 5;

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
                $isNew = true;
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

            if ($isNew ?? false) {
                self::alertAdmins($exception::class, $message, $url);
            }
        } catch (Throwable) {
            // Sin base de datos no hay dónde guardarlo; el servidor ya lo registró.
        } finally {
            self::$busy = false;
        }
    }

    /**
     * Un error que nunca había pasado avisa por correo a la administración. Los repetidos no:
     * se suman en /admin/errors. Como mucho ALERTS_PER_HOUR correos por hora, aunque se rompa todo.
     */
    private static function alertAdmins(string $class, string $message, string $url): void
    {
        if (! RateLimiter::attempt('error-alerts', self::ALERTS_PER_HOUR, fn () => true, 3600)) {
            return;
        }

        foreach (User::where('utype', 'ADM')->whereNull('suspended_at')->pluck('email') as $email) {
            Notice::send((string) $email, 'Error nuevo en Malakplace', [
                'Apareció un error que el sitio no había tenido antes.',
                'Dónde: ' . $url,
                'Qué: ' . Str::limit($class . ' — ' . $message, 300),
                'Si se repite, no vuelve a avisar: se suma en «Errores». Envíale el detalle a quien mantiene el sitio.',
            ], 'Ver errores', route('admin.errors'));
        }
    }
}
