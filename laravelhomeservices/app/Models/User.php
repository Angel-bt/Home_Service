<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'utype',
        'subscription_plan',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'plan_expires_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function serviceProvider(): HasOne
    {
        return $this->hasOne(ServiceProvider::class);
    }

    /**
     * Number customers should write to on WhatsApp: the provider's own WhatsApp,
     * then their registration phone.
     */
    public function whatsappNumber(): ?string
    {
        $whatsapp = $this->serviceProvider?->whatsapp;

        return $whatsapp !== null && $whatsapp !== '' ? $whatsapp : $this->phone;
    }

    public function subscriptionPayments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    /**
     * Pro plan in force. A "pro" user without expiry date was upgraded manually and never expires.
     */
    /**
     * Route name of this user's own panel.
     */
    public function dashboardRoute(): string
    {
        return match ($this->utype) {
            'ADM' => 'admin.dashboard',
            'SVP' => 'sprovider.dashboard',
            default => 'customer.dashboard',
        };
    }

    public function isPro(): bool
    {
        return $this->subscription_plan === 'pro'
            && ($this->plan_expires_at === null || $this->plan_expires_at->isFuture());
    }

    public function servicePublicationLimit(): ?int
    {
        if ($this->utype === 'ADM' || $this->isPro()) {
            return null;
        }

        return (int) config('marketplace.free_service_limit');
    }

    public function canPublishService(): bool
    {
        $limit = $this->servicePublicationLimit();

        return $limit === null || $this->services()->count() < $limit;
    }
}
