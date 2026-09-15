<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Slider extends Model
{
    use HasFactory;
    protected static function booted(): void
    {
        static::saved(static function (): void {
            Cache::forget('home:active-sliders');
        });

        static::deleted(static function (): void {
            Cache::forget('home:active-sliders');
        });
    }
}
