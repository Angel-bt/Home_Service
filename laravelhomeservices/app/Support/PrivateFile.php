<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Entrega un archivo privado (comprobante, documento de verificación) solo para verlo:
 * sin caché, sin indexar y, si es una imagen, aislado (sandbox) para que nada en él pueda
 * ejecutarse con la sesión de quien lo abre. Los PDF no llevan sandbox: Chrome se niega a
 * mostrarlos con esa cabecera; SecurityHeaders tampoco les pone su política.
 */
final class PrivateFile
{
    public static function response(Filesystem $disk, string $path): StreamedResponse
    {
        $headers = ['Cache-Control' => 'private, no-store', 'X-Robots-Tag' => 'noindex, nofollow'];

        if (str_starts_with((string) $disk->mimeType($path), 'image/')) {
            $headers['Content-Security-Policy'] = "sandbox; default-src 'none'; img-src 'self' data: blob:; style-src 'unsafe-inline'";
        }

        return $disk->response($path, null, $headers);
    }
}
