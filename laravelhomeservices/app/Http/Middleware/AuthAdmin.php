<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthAdmin
{
    /**
     * Solo administración. En producción, además, con la verificación en dos pasos activada:
     * una contraseña robada no alcanza para entrar al panel (config marketplace.admin_two_factor).
     *
     * @param  Closure(Request): (Response|RedirectResponse)  $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user->utype !== 'ADM') {
            return redirect()->route($user->dashboardRoute())->with('error', 'Esa página es solo para la administración. Te trajimos a tu panel.');
        }

        if (config('marketplace.admin_two_factor') && ! $user->hasEnabledTwoFactorAuthentication()) {
            return redirect()->to(route('profile.show') . '#dos-pasos')->with('error', 'Para entrar al panel de administración, primero activa la verificación en dos pasos (abajo, en esta página). Necesitas una app de códigos en tu teléfono, como Google Authenticator; toma dos minutos.');
        }

        return $next($request);
    }
}
