<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Models\ServiceProvider;
use App\Jobs\SendWelcomeEmail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Illuminate\Support\Facades\Session;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array  $input
     * @return \App\Models\User
     */
    public function create(array $input): User
    {
        // Freno a registros automáticos. Generoso: en Ecuador mucha gente comparte IP con su operador.
        $key = 'register:' . request()->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 20)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['email' => 'Se crearon muchas cuentas desde esta conexión. Espera un rato e inténtalo otra vez.']);
        }
        \Illuminate\Support\Facades\RateLimiter::hit($key, 3600);

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', \Illuminate\Validation\Rules\Password::default()],
            'phone' => ['required', 'string', 'max:20', function ($attribute, $value, $fail) {
                if (\App\Support\WhatsApp::normalize($value) === null) {
                    $fail('Escribe un celular válido, por ejemplo 0991234567: con él te contactan por WhatsApp.');
                }
            }],
            'registeras' => ['nullable', 'in:CST,SVP'],
            'terms' => ['accepted'],
            'publication_consent' => ['accepted_if:registeras,SVP'],
        ])->validate();

        $registeras = ($input['registeras'] ?? 'CST') === 'SVP' ? 'SVP' : 'CST';

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'phone' => $input['phone'],
            'utype' => $registeras,
            'subscription_plan' => 'free',
        ]);

        $user->acceptTerms($registeras === 'SVP');

        if ($registeras === 'SVP') {
            ServiceProvider::create([
                'user_id' => $user->id
            ]);
        }

        \App\Support\Activity::record(\App\Models\ActivityEvent::SIGNUP, ['user' => $user]);

        // Sin cola (QUEUE_CONNECTION=sync) el correo sale ahora: si falla, la cuenta ya está creada.
        try {
            SendWelcomeEmail::dispatch($user);
        } catch (\Throwable $exception) {
            report($exception);
        }

        // Establecer un mensaje de sesión de éxito
        Session::flash('success', '¡Registro exitoso! Bienvenido a Malakplace.');
        return $user;
    }
}
