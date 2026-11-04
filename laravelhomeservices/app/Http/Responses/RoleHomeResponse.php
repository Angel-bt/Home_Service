<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse;
use Laravel\Fortify\Fortify;

/**
 * After signing in or registering, providers and admins land on their panel, where
 * they see what to do next. Customers keep going where they were (or to the home page).
 */
class RoleHomeResponse implements LoginResponse, RegisterResponse, TwoFactorLoginResponse
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return new JsonResponse(['two_factor' => false]);
        }

        $user = $request->user();

        return redirect()->intended(in_array($user?->utype, ['ADM', 'SVP'], true)
            ? route($user->dashboardRoute())
            : Fortify::redirects('login'));
    }
}
