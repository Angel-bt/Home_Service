<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Models\ServiceProvider;
use App\Jobs\SendWelcomeEmail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
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
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'phone' => ['required'],
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        $registeras = $input['registeras'] === 'SVP' ? 'SVP' : 'CST';

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'phone' => $input['phone'],
            'utype' => $registeras,
            'subscription_plan' => 'free',
        ]);

        if ($registeras === 'SVP') {
            ServiceProvider::create([
                'user_id' => $user->id
            ]);
        }

        SendWelcomeEmail::dispatch($user);

        // Establecer un mensaje de sesión de éxito
        Session::flash('success', 'Registration successful!');
        return $user;
    }
}
