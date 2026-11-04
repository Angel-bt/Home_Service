<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        // Servidor de correo caído (p. ej. al pedir recuperar la contraseña): explicarlo, no una página de error.
        $this->renderable(function (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e, $request) {
            if ($request->expectsJson()) {
                return null;
            }

            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'No pudimos enviar el correo en este momento. Intenta de nuevo en unos minutos o escríbenos por WhatsApp.',
            ]);
        });
    }
}
