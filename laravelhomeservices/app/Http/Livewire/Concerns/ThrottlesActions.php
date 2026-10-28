<?php

declare(strict_types=1);

namespace App\Http\Livewire\Concerns;

use Illuminate\Support\Facades\RateLimiter;

/**
 * Rate limiting for Livewire actions (route throttling does not cover /livewire/update).
 */
trait ThrottlesActions
{
    /**
     * Count one attempt; returns false and adds a validation error once the limit is hit.
     */
    protected function withinRateLimit(string $action, string $identity, int $maxAttempts, int $decaySeconds, string $errorField): bool
    {
        $key = 'livewire:' . $action . ':' . $identity;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $minutes = (int) ceil(RateLimiter::availableIn($key) / 60);
            $this->addError($errorField, "Demasiados intentos. Vuelve a intentarlo en {$minutes} " . ($minutes === 1 ? 'minuto' : 'minutos') . '.');

            return false;
        }

        RateLimiter::hit($key, $decaySeconds);

        return true;
    }
}
