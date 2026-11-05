<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Activity;
use App\Support\Notice;
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

        $this->afterTransition($status);
    }

    public function notifyProviderOfNewRequest(): void
    {
        $provider = $this->provider;

        if ($provider?->email) {
            Notice::send($provider->email, 'Tienes una solicitud nueva en Malakplace', [
                $this->name . ' pidió «' . $this->service?->name . '» en ' . $this->zoneLabel() . '.',
                'Quien contesta primero suele quedarse con el trabajo.',
            ], 'Ver la solicitud', route($provider->utype === 'ADM' ? 'admin.service_requests' : 'sprovider.requests'));
        }
    }

    /**
     * Avisa a la otra persona: sin correo, quien no entra al panel nunca se entera.
     */
    private function afterTransition(string $status): void
    {
        if ($status === self::COMPLETED) {
            Activity::record(ActivityEvent::REQUEST_COMPLETED, ['user' => $this->customer, 'service_id' => $this->service_id, 'provider_id' => $this->provider_id]);
        }

        $service = (string) $this->service?->name;
        $customerPanel = route('customer.dashboard');

        match ($status) {
            self::ACCEPTED => $this->tell($this->customer, 'Aceptaron tu solicitud en Malakplace', ["Aceptaron tu solicitud de «{$service}». Te van a escribir para acordar el día y el precio."], 'Ver mi solicitud', $customerPanel),
            self::REJECTED => $this->tell($this->customer, 'Tu solicitud no pudo ser atendida', ["Esta vez no pueden atender tu solicitud de «{$service}». Busca a otra persona del mismo oficio."], 'Buscar otro proveedor', route('search')),
            self::COMPLETED => $this->tell($this->customer, '¿Cómo te fue con el trabajo?', ["Marcaron como terminado el trabajo de «{$service}». Tu opinión ayuda a tus vecinos a elegir bien."], 'Dejar mi reseña', $customerPanel),
            self::CANCELLED => $this->tell($this->provider, 'Cancelaron una solicitud', ["{$this->name} canceló su solicitud de «{$service}»."], 'Ver mis solicitudes', route($this->provider?->utype === 'ADM' ? 'admin.service_requests' : 'sprovider.requests')),
            default => null,
        };
    }

    /**
     * @param  list<string>  $lines
     */
    private function tell(?User $user, string $subject, array $lines, string $label, string $url): void
    {
        if ($user?->email) {
            Notice::send($user->email, $subject, $lines, $label, $url);
        }
    }

    public function canBeReviewedBy(User $user): bool
    {
        return $this->status === self::COMPLETED
            && $this->customer_id === $user->id
            && ! $this->review()->exists();
    }
}
