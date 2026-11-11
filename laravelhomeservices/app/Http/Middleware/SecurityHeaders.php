<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cabeceras de seguridad de todas las respuestas.
 *
 * La política de contenido (CSP) permite scripts en línea y eval porque Alpine y Livewire los
 * necesitan; aun así impide cargar scripts, estilos o marcos de otros sitios, incrustar la página
 * en otro dominio, cambiar la base de los enlaces y enviar formularios a sitios ajenos.
 * Los hosts permitidos salen de la configuración (bucket de fotos, Google Maps si hay clave).
 */
final class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self), payment=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        // Las descargas privadas (comprobantes, documentos) traen su propia política, más estricta.
        $isPdf = str_starts_with((string) $response->headers->get('Content-Type'), 'application/pdf');
        if (! $isPdf && ! $response->headers->has('Content-Security-Policy')) {
            $response->headers->set('Content-Security-Policy', self::policy());
        }

        // No anunciar la versión de PHP.
        $response->headers->remove('X-Powered-By');
        if (! headers_sent()) {
            header_remove('X-Powered-By');
        }

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    public static function policy(): string
    {
        $storage = self::hosts([
            config('filesystems.disks.s3.url'),
            config('filesystems.disks.s3.endpoint'),
            config('filesystems.disks.receipts.endpoint'),
        ]);

        $maps = config('services.google_maps.key') ? ['https://maps.googleapis.com', 'https://maps.gstatic.com', 'https://*.googleapis.com', 'https://*.gstatic.com', 'https://*.google.com'] : [];
        $payphone = config('billing.payphone.enabled') ? self::hosts([config('billing.payphone.base_url')]) : [];

        $directives = [
            'default-src' => ["'self'"],
            'script-src' => ["'self'", "'unsafe-inline'", "'unsafe-eval'", ...$maps],
            'style-src' => ["'self'", "'unsafe-inline'", 'https://fonts.googleapis.com'],
            'font-src' => ["'self'", 'data:', 'https://fonts.gstatic.com', ...$maps],
            'img-src' => ["'self'", 'data:', 'blob:', 'https://images.unsplash.com', ...$storage, ...$maps],
            // Las fotos se suben directo al bucket con una dirección firmada.
            'connect-src' => ["'self'", ...$storage, ...$maps],
            'frame-src' => ["'self'", ...$maps],
            'object-src' => ["'none'"],
            'base-uri' => ["'self'"],
            'form-action' => ["'self'", ...$payphone],
            'frame-ancestors' => ["'self'"],
        ];

        $policy = [];
        foreach ($directives as $name => $sources) {
            $policy[] = $name . ' ' . implode(' ', array_values(array_unique($sources)));
        }

        return implode('; ', $policy);
    }

    /**
     * @param  array<int, mixed>  $urls
     * @return list<string>  esquema://host de cada dirección válida
     */
    private static function hosts(array $urls): array
    {
        $hosts = [];
        foreach ($urls as $url) {
            $parts = is_string($url) ? parse_url($url) : false;
            if (is_array($parts) && isset($parts['scheme'], $parts['host'])) {
                $hosts[] = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
            }
        }

        return array_values(array_unique($hosts));
    }
}
