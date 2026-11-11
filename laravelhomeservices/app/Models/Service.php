<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\WhatsApp;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
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
        'paused_at' => 'datetime',
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
        // Las reseñas que ocultó la administración no se ven ni cuentan en el promedio.
        return $this->hasMany(Review::class)->whereNull('hidden_at');
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

        // Mucha gente busca sin tildes. El cotejo de MySQL ya las ignora; Postgres no, así que ahí se quitan a ambos lados.
        $postgres = $query->getConnection()->getDriverName() === 'pgsql';
        $term = $postgres ? mb_strtolower(Str::ascii($term)) : $term;
        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term) . '%';

        $contains = fn (Builder $query, string $column, string $boolean = 'and'): Builder => $postgres
            ? $query->whereRaw("translate(lower({$column}), 'áéíóúüñ', 'aeiouun') like ?", [$like], $boolean)
            : $query->whereLike($column, $like, false, $boolean);

        return $query->where(function (Builder $query) use ($contains): void {
            $contains($query, 'name');
            $contains($query, 'tagline', 'or');
            $contains($query, 'description', 'or');
            $query->orWhereHas('category', fn (Builder $category) => $contains($category, 'name'));
        });
    }

    /**
     * Lo que ve el público: no oculto por la administración, no pausado por el proveedor y de una cuenta no suspendida.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true)
            ->whereNull('paused_at')
            ->whereDoesntHave('user', fn (Builder $users) => $users->whereNotNull('suspended_at'));
    }

    public function isPublic(): bool
    {
        return $this->status && $this->paused_at === null && $this->user?->suspended_at === null;
    }

    /**
     * Por qué no se ve en el sitio, para mostrárselo a su dueño o a la administración.
     */
    public function hiddenReason(): ?string
    {
        return match (true) {
            $this->user?->suspended_at !== null => 'La cuenta de quien lo publicó está suspendida.',
            ! $this->status => 'La administración lo ocultó.',
            $this->paused_at !== null => 'Está pausado por quien lo publicó.',
            default => null,
        };
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
