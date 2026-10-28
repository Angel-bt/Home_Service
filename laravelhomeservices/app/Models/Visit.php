<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use HasFactory;

    protected $table = 'visit_counter';
    protected $fillable = ['visits', 'visitor_name'];

    // Registro único que guarda el contador global de visitas.
    public static function counter()
    {
        return static::firstOrCreate(
            ['id' => 1],
            ['visitor_name' => 'Sistema', 'visits' => 0]
        );
    }
}
