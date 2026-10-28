<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Auth;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $user = Auth::user();

        if ($user->utype === 'ADM') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->utype === 'SVP') {
            return redirect()->route('sprovider.dashboard');
        } elseif ($user->utype === 'CST') {
            return redirect()->route('customer.dashboard');
        }

        return redirect('/');
    }
}
