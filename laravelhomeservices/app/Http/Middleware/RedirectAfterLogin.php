<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectAfterLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Verifica si el usuario está autenticado
        if (auth()->check()) {
            // Establece un mensaje de sesión de éxito
            session()->flash('success', 'Login successful!');
        }

        return $response;
    }
}