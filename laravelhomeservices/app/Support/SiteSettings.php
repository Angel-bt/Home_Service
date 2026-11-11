<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Ajustes que la administración cambia en /admin/settings sin tocar código. Se guardan en la tabla
 * `settings` y, en cada petición, reemplazan los valores de config/*.php (que siguen siendo el valor
 * por defecto). El resto del código sigue leyendo config() como siempre.
 */
final class SiteSettings
{
    public const CACHE_KEY = 'site:settings';

    public const GROUPS = [
        'contacto' => ['Contacto de Malakplace', 'Lo que ve la gente para escribirles, y adónde llegan los avisos.'],
        'planes' => ['Plan gratuito y plan Pro', 'Cuánto pueden publicar gratis los proveedores y cuánto cuesta el Pro.'],
        'banco' => ['Datos para transferencias', 'Lo que ven los proveedores al pagar el plan Pro por transferencia.'],
        'verificacion' => ['Verificación', 'Cuánto dura el sello «Sin antecedentes penales».'],
    ];

    /**
     * clave => [grupo, config, etiqueta, ayuda, tipo de campo, reglas, conversión]
     */
    public const FIELDS = [
        'contact_phone' => ['contacto', 'marketplace.contact_phone', 'WhatsApp de Malakplace', 'Aparece en el pie de página y en «Pedir ayuda». Ej.: 0991234567', 'tel', ['required', 'string', 'max:20'], 'string'],
        'contact_email' => ['contacto', 'marketplace.contact_email', 'Correo de contacto', 'Se muestra en el sitio y en los textos legales.', 'email', ['required', 'email', 'max:120'], 'string'],
        'lead_notification_address' => ['contacto', 'marketplace.lead_notification_address', 'Correo para avisos de administración', 'Aquí llegan los mensajes de contacto y los documentos por revisar. Déjalo vacío para no recibirlos.', 'email', ['nullable', 'email', 'max:120'], 'string'],
        'free_service_limit' => ['planes', 'marketplace.free_service_limit', 'Servicios gratis por proveedor', 'Al llegar a este número, el proveedor necesita el plan Pro para publicar más.', 'number', ['required', 'integer', 'between:1,50'], 'int'],
        'pro_price' => ['planes', 'billing.pro_price', 'Precio del plan Pro (USD)', 'Lo que paga el proveedor por cada periodo.', 'number', ['required', 'numeric', 'between:0,500'], 'float'],
        'pro_period_days' => ['planes', 'billing.pro_period_days', 'Días que dura cada pago', 'Normalmente 30.', 'number', ['required', 'integer', 'between:1,366'], 'int'],
        'bank_name' => ['banco', 'billing.bank_transfer.bank', 'Banco', 'Ej.: Banco de Loja', 'text', ['nullable', 'string', 'max:80'], 'string'],
        'bank_account_type' => ['banco', 'billing.bank_transfer.account_type', 'Tipo de cuenta', 'Ahorros o corriente.', 'text', ['nullable', 'string', 'max:40'], 'string'],
        'bank_account_number' => ['banco', 'billing.bank_transfer.account_number', 'Número de cuenta', '', 'text', ['nullable', 'string', 'max:40'], 'string'],
        'bank_holder' => ['banco', 'billing.bank_transfer.holder', 'A nombre de', '', 'text', ['nullable', 'string', 'max:120'], 'string'],
        'bank_holder_id' => ['banco', 'billing.bank_transfer.holder_id', 'Cédula o RUC del titular', '', 'text', ['nullable', 'string', 'max:20'], 'string'],
        'background_valid_days' => ['verificacion', 'marketplace.verification.background_valid_days', 'Días que dura el sello de antecedentes', 'El certificado oficial vale 3 meses (90 días). Entre 30 y 365.', 'number', ['required', 'integer', 'between:30,365'], 'int'],
    ];

    /** Zonas: guardadas como JSON, reemplazan config('marketplace.zones'). */
    public const ZONES_KEY = 'zones';

    public const MAIN_ZONE_KEY = 'main_zone';

    /**
     * Se llama al arrancar la aplicación. Si la tabla aún no existe (antes de migrar), no hace nada.
     */
    public static function apply(): void
    {
        try {
            $values = Cache::rememberForever(self::CACHE_KEY, fn (): array => DB::table('settings')->pluck('value', 'key')->all());
        } catch (Throwable) {
            return;
        }

        self::merge($values);
    }

    public static function value(string $key): mixed
    {
        if (isset(self::FIELDS[$key])) {
            return config(self::FIELDS[$key][1]);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $values  clave => valor
     */
    public static function save(array $values): void
    {
        $now = now();
        foreach ($values as $key => $value) {
            $stored = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : ($value === null || $value === '' ? null : (string) $value);
            DB::table('settings')->updateOrInsert(['key' => $key], ['value' => $stored, 'updated_at' => $now, 'created_at' => $now]);
        }

        Cache::forget(self::CACHE_KEY);
        Cache::forget(\App\Http\Livewire\HomeComponent::CACHE_KEY);
        self::merge(DB::table('settings')->pluck('value', 'key')->all());
    }

    /**
     * @param  array<string, string|null>  $values
     */
    private static function merge(array $values): void
    {
        foreach (self::FIELDS as $key => [, $configKey, , , , , $cast]) {
            if (! array_key_exists($key, $values)) {
                continue;
            }

            $value = $values[$key];
            config([$configKey => $value === null ? null : match ($cast) {
                'int' => (int) $value,
                'float' => (float) $value,
                default => $value,
            }]);
        }

        if (isset($values[self::ZONES_KEY])) {
            $zones = json_decode((string) $values[self::ZONES_KEY], true);
            if (is_array($zones) && $zones !== []) {
                config(['marketplace.zones' => $zones]);
            }
        }

        if (isset($values[self::MAIN_ZONE_KEY]) && array_key_exists($values[self::MAIN_ZONE_KEY], config('marketplace.zones'))) {
            config(['marketplace.main_zone' => $values[self::MAIN_ZONE_KEY]]);
        }
    }
}
