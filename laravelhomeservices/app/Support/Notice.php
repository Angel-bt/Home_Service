<?php

declare(strict_types=1);

namespace App\Support;

use App\Jobs\SendNoticeEmail;
use Throwable;

/**
 * Avisos por correo. Sin cola (QUEUE_CONNECTION=sync) el correo sale en la misma petición:
 * si el servidor de correo falla, la acción del usuario ya está hecha y no se debe deshacer.
 */
final class Notice
{
    /**
     * @param  list<string>  $lines
     */
    public static function send(string $to, string $subject, array $lines, ?string $actionLabel = null, ?string $actionUrl = null): void
    {
        try {
            SendNoticeEmail::dispatch($to, $subject, $lines, $actionLabel, $actionUrl);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
