<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use HasFactory;

    protected $table = 'visit_counter';
    protected $fillable = ['visits', 'visitor_name'];

    // Registro único que guarda el contador global de visitas. Se usa la primera fila, sea cual sea su id:
    // buscar siempre el id 1 creaba una fila nueva por visita si ese id no existía (en Postgres los ids no
    // retroceden, así que basta un intento fallido o una restauración para perderlo).
    public static function counter()
    {
        return static::query()->orderBy('id')->first()
            ?? static::create(['visitor_name' => 'Sistema', 'visits' => 0]);
    }
}
