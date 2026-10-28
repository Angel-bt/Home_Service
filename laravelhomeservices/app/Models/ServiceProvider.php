<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceProvider extends Model
{
    use HasFactory;

    protected $fillable = ['user_id'];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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
