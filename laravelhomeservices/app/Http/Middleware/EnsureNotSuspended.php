<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Una cuenta suspendida mientras tenía la sesión abierta sale en su próxima petición.
 * El ingreso de cuentas suspendidas lo frena FortifyServiceProvider.
 */
class EnsureNotSuspended
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $user->suspended_at === null) {
            return $next($request);
        }

        $message = $user->suspensionMessage();
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if (! $request->isMethod('GET') || $request->expectsJson()) {
            abort(403, $message);
        }

        return redirect()->route('login')->withErrors(['email' => $message]);
    }
}
