<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * Pregunta frecuente que la administración edita en /admin/faq.
 * Las respuestas pueden llevar datos que cambian solos: ver PLACEHOLDERS.
 */
class Faq extends Model
{
    public const GROUPS = ['Para clientes', 'Para proveedores', 'General'];

    public const PLACEHOLDERS = [
        '{limite_gratis}' => 'servicios gratis por proveedor',
        '{precio_pro}' => 'precio del plan Pro',
        '{dias_pro}' => 'días que dura el plan Pro',
        '{zonas}' => 'zonas donde funciona',
        '{whatsapp}' => 'WhatsApp de Malakplace',
        '{correo}' => 'correo de contacto',
    ];

    protected $fillable = ['group', 'question', 'answer', 'position'];

    protected $casts = ['position' => 'integer'];

    public function renderedAnswer(): string
    {
        $zones = array_values(config('marketplace.zones'));
        $zonesText = count($zones) > 1 ? implode(', ', array_slice($zones, 0, -1)) . ' y ' . Arr::last($zones) : (string) ($zones[0] ?? '');

        return strtr($this->answer, [
            '{limite_gratis}' => (string) config('marketplace.free_service_limit'),
            '{precio_pro}' => '$' . number_format((float) config('billing.pro_price'), 2),
            '{dias_pro}' => (string) config('billing.pro_period_days'),
            '{zonas}' => $zonesText,
            '{whatsapp}' => (string) config('marketplace.contact_phone'),
            '{correo}' => (string) config('marketplace.contact_email'),
        ]);
    }
}
