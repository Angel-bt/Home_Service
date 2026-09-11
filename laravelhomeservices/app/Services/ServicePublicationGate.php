<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use DomainException;

final class ServicePublicationGate
{
    public function ensureCanPublish(User $user): void
    {
        if (! $user->canPublishService()) {
            throw new DomainException('Your current plan has reached its service publication limit.');
        }
    }
}
