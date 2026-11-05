<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\ActivityEvent;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;

/**
 * Registro de actividad para el panel de análisis. Nunca debe tumbar la acción del usuario:
 * si falla, se reporta y la página sigue. No se cuenta a robots ni a la administración, y un
 * proveedor que mira sus propios servicios tampoco suma.
 */
final class Activity
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function record(string $event, array $attributes = []): ?ActivityEvent
    {
        try {
            $user = $attributes['user'] ?? Auth::user();
            unset($attributes['user']);

            if (self::isBot() || $user?->utype === 'ADM') {
                return null;
            }

            if ($user !== null && isset($attributes['provider_id']) && (int) $attributes['provider_id'] === (int) $user->id) {
                return null;
            }

            return ActivityEvent::create($attributes + [
                'event' => $event,
                'user_id' => $user?->id,
                'user_role' => $user?->utype,
                'zone' => self::zone(),
                'created_at' => now(),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    public static function forService(string $event, Service $service): ?ActivityEvent
    {
        return self::record($event, ['service_id' => $service->id, 'provider_id' => $service->user_id]);
    }

    /**
     * Minúsculas y sin tildes, para que «Plomería» y «plomeria» cuenten como lo mismo. La ñ se queda.
     */
    public static function normalizeTerm(string $term): string
    {
        $term = Str::lower(trim((string) preg_replace('/\s+/', ' ', $term)));

        return Str::limit(strtr($term, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u']), 100, '');
    }

    private static function zone(): ?string
    {
        $zone = request()->hasSession() ? request()->session()->get('zone') : null;

        return is_string($zone) && $zone !== '' ? $zone : null;
    }

    private static function isBot(): bool
    {
        return Str::contains(Str::lower((string) request()->userAgent()), ['bot', 'crawl', 'spider', 'slurp', 'facebookexternalhit', 'whatsapp/', 'preview']);
    }
}
