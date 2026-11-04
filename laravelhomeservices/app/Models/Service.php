<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\WhatsApp;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Service extends Model
{
    use HasFactory;

    protected $table = 'services';

    protected $casts = [
        'views' => 'integer',
        'status' => 'boolean',
        'featured' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    /**
     * Services available in a zone: those of providers covering it, plus services
     * not owned by a provider (published by the administration for every zone).
     */
    public function scopeInZone(Builder $query, ?string $zone): Builder
    {
        if ($zone === null || $zone === '') {
            return $query;
        }

        return $query->where(function (Builder $query) use ($zone): void {
            $query->whereHas('user.serviceProvider.zones', fn (Builder $zones) => $zones->where('zone', $zone))
                ->orWhereDoesntHave('user', fn (Builder $users) => $users->where('utype', 'SVP'));
        });
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term) . '%';

        return $query->where(function (Builder $query) use ($like): void {
            $query->where('name', 'like', $like)
                ->orWhere('tagline', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhereHas('category', fn (Builder $category) => $category->where('name', 'like', $like));
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function finalPrice(): float
    {
        $price = (float) $this->price;
        $discount = (float) $this->discount;

        if ($discount <= 0) {
            return $price;
        }

        $total = $this->discount_type === 'percent' ? $price - ($price * $discount / 100) : $price - $discount;

        return max(0.0, round($total, 2));
    }

    public function whatsappLink(): ?string
    {
        $phone = $this->user !== null && $this->user->utype === 'SVP'
            ? $this->user->whatsappNumber()
            : config('marketplace.contact_phone');

        return WhatsApp::link($phone, 'Hola, vi su servicio "' . $this->name . '" en Malakplace y quisiera más información.');
    }

    protected static function booted(): void
    {
        $forgetHomeServiceCaches = static function (): void {
            Cache::forget(\App\Http\Livewire\HomeComponent::CACHE_KEY);
        };

        static::saved($forgetHomeServiceCaches);
        static::deleted($forgetHomeServiceCaches);
    }
}
