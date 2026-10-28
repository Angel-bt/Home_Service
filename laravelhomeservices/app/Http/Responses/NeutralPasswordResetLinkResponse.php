<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Support\Facades\Password;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;

/**
 * Answer "unknown e-mail" exactly like a sent link, so the form doesn't reveal which
 * addresses are registered. Throttling is still reported as an error.
 */
final class NeutralPasswordResetLinkResponse implements FailedPasswordResetLinkRequestResponse
{
    public function __construct(protected string $status)
    {
    }

    public function toResponse($request)
    {
        if ($this->status === Password::RESET_THROTTLED) {
            return back()->withInput($request->only('email'))->withErrors(['email' => trans($this->status)]);
        }

        return back()->with('status', trans(Password::RESET_LINK_SENT));
    }
}
