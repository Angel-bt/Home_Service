<?php

declare(strict_types=1);

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ServiceRequest extends Model
{
    public const PENDING = 'pending';
    public const ACCEPTED = 'accepted';
    public const REJECTED = 'rejected';
    public const COMPLETED = 'completed';
    public const CANCELLED = 'cancelled';

    public const LABELS = [
        self::PENDING => 'Pendiente',
        self::ACCEPTED => 'Aceptada',
        self::REJECTED => 'Rechazada',
        self::COMPLETED => 'Completada',
        self::CANCELLED => 'Cancelada',
    ];

    /**
     * Allowed status changes: [from => [to => who]], where who is "provider" or "customer".
     */
    private const TRANSITIONS = [
        self::PENDING => [self::ACCEPTED => 'provider', self::REJECTED => 'provider', self::CANCELLED => 'customer'],
        self::ACCEPTED => [self::COMPLETED => 'provider', self::CANCELLED => 'customer'],
    ];

    protected $fillable = [
        'service_id',
        'provider_id',
        'customer_id',
        'name',
        'phone',
        'zone',
        'address',
        'preferred_date',
        'message',
        'status',
    ];

    protected $casts = [
        'preferred_date' => 'date',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function statusLabel(): string
    {
        return self::LABELS[$this->status] ?? $this->status;
    }

    public function zoneLabel(): string
    {
        return (string) (config('marketplace.zones')[$this->zone] ?? $this->zone);
    }

    public function canTransitionTo(string $status, User $user): bool
    {
        $who = self::TRANSITIONS[$this->status][$status] ?? null;

        if ($who === 'provider') {
            return $user->utype === 'ADM' || $user->id === $this->provider_id;
        }

        if ($who === 'customer') {
            return $user->id === $this->customer_id;
        }

        return false;
    }

    public function transitionTo(string $status, User $user): void
    {
        if (! $this->canTransitionTo($status, $user)) {
            throw new DomainException('No puedes cambiar esta solicitud a ese estado.');
        }

        $this->status = $status;
        $this->save();
    }

    public function canBeReviewedBy(User $user): bool
    {
        return $this->status === self::COMPLETED
            && $this->customer_id === $user->id
            && ! $this->review()->exists();
    }
}
