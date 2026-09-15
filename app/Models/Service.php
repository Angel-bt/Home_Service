<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    protected static function booted(): void
    {
        $forgetHomeServiceCaches = static function (): void {
            Cache::forget('home:featured-services');
            Cache::forget('home:appliance-services');
            Cache::forget('home:category-modals');
        };

        static::saved($forgetHomeServiceCaches);
        static::deleted($forgetHomeServiceCaches);
    }
}
