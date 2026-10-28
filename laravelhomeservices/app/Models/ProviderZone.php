<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderZone extends Model
{
    protected $fillable = ['service_provider_id', 'zone'];

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function label(): string
    {
        return (string) (config('marketplace.zones')[$this->zone] ?? $this->zone);
    }
}
