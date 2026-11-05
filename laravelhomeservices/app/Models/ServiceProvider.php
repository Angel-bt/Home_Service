<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceProvider extends Model
{
    use HasFactory;

    protected $fillable = ['user_id'];

    protected $casts = [
        'identity_verified_at' => 'datetime',
        'background_checked_at' => 'datetime',
        'background_valid_until' => 'datetime',
        'cv_reviewed_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function documents()
    {
        return $this->hasMany(ProviderDocument::class);
    }

    /**
     * El sello vigente de ese tipo, o null. Los cambia solo App\Services\ProviderVerification.
     *
     * @return array{label: string, detail: string}|null
     */
    public function badgeFor(string $type): ?array
    {
        $label = ProviderDocument::TYPES[$type]['badge'] ?? '';

        return match ($type) {
            ProviderDocument::IDENTITY => $this->identity_verified_at
                ? ['label' => $label, 'detail' => 'Cédula revisada el ' . $this->identity_verified_at->format('d/m/Y')]
                : null,
            ProviderDocument::BACKGROUND => $this->background_checked_at && $this->background_valid_until?->isFuture()
                ? ['label' => $label, 'detail' => 'Certificado oficial revisado el ' . $this->background_checked_at->format('d/m/Y')]
                : null,
            ProviderDocument::CV => $this->cv_reviewed_at
                ? ['label' => $label, 'detail' => 'Documentos revisados el ' . $this->cv_reviewed_at->format('d/m/Y')]
                : null,
            default => null,
        };
    }

    /**
     * @return list<array{label: string, detail: string}>
     */
    public function trustBadges(): array
    {
        return array_values(array_filter(array_map(fn (string $type) => $this->badgeFor($type), array_keys(ProviderDocument::TYPES))));
    }

    public function isVerified(): bool
    {
        return $this->badgeFor(ProviderDocument::IDENTITY) !== null || $this->badgeFor(ProviderDocument::BACKGROUND) !== null;
    }

    public function zones()
    {
        return $this->hasMany(ProviderZone::class);
    }

    // Reemplaza las zonas que cubre el proveedor por las indicadas (solo slugs válidos).
    public function syncZones(array $zones)
    {
        $valid = array_values(array_intersect(array_keys(config('marketplace.zones')), $zones));

        $this->zones()->whereNotIn('zone', $valid)->delete();

        foreach ($valid as $zone) {
            $this->zones()->firstOrCreate(['zone' => $zone]);
        }
    }
}
