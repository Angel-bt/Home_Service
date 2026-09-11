<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class ServiceCategory extends Model
{
    use HasFactory;

    protected $table = 'service_categories';

    protected $fillable = [
        'name',
        'slug',
    ];

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'service_category_id');
    }

    protected static function booted(): void
    {
        $forgetHomeCategoryCaches = static function (): void {
            Cache::forget('home:service-categories');
            Cache::forget('home:featured-categories');
            Cache::forget('home:appliance-services');
        };

        static::saved($forgetHomeCategoryCaches);
        static::deleted($forgetHomeCategoryCaches);
    }
}
