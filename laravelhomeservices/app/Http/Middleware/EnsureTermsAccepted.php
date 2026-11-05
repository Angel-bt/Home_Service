<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Quien tiene cuenta y no aceptó la versión vigente de los términos la acepta antes de seguir:
 * las cuentas anteriores a esta regla, y todas cuando cambie config('marketplace.terms_version').
 * Puede leer los textos, salir o eliminar su cuenta. La administración no se acepta a sí misma.
 */
class EnsureTermsAccepted
{
    private const ALWAYS_OPEN = ['terms.accept', 'home.terms', 'home.privacy', 'logout', 'profile.show'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null
            || $user->utype === 'ADM'
            || $user->hasAcceptedCurrentTerms()
            || ! $request->isMethod('GET')
            || $request->expectsJson()
            || $request->routeIs(...self::ALWAYS_OPEN)
        ) {
            return $next($request);
        }

        return redirect()->guest(route('terms.accept'));
    }
}
