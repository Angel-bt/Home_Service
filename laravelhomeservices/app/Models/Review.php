<?php

declare(strict_types=1);

namespace App\Models;

use App\Http\Livewire\HomeComponent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class Review extends Model
{
    protected $fillable = ['service_id', 'service_request_id', 'user_id', 'rating', 'comment'];

    protected $casts = [
        'rating' => 'integer',
        'hidden_at' => 'datetime',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    protected static function booted(): void
    {
        // The landing shows recent reviews and the average rating.
        static::saved(static fn () => Cache::forget(HomeComponent::CACHE_KEY));
        static::deleted(static fn () => Cache::forget(HomeComponent::CACHE_KEY));
    }
}
