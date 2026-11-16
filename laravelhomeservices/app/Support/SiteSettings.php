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

    /** grupo => [título, ayuda, pantalla] — pantalla: 'ajustes' (/admin/settings) o 'textos' (/admin/content). */
    public const GROUPS = [
        'contacto' => ['Contacto de Malakplace', 'Lo que ve la gente para escribirles, y adónde llegan los avisos.', 'ajustes'],
        'planes' => ['Plan gratuito y plan Pro', 'Cuánto pueden publicar gratis los proveedores, cuánto cuesta el Pro y cómo se puede pagar.', 'ajustes'],
        'banco' => ['Datos para transferencias', 'Lo que ven los proveedores al pagar el plan Pro por transferencia.', 'ajustes'],
        'verificacion' => ['Verificación', 'Cuánto dura el sello «Sin antecedentes penales».', 'ajustes'],
        'portada' => ['Portada', 'Los textos principales de la página de inicio. Lo que escribas entre *asteriscos* sale destacado.', 'textos'],
        'aviso' => ['Aviso destacado', 'Un mensaje corto que aparece abajo en todas las páginas, por ejemplo por un feriado. Cada persona puede cerrarlo.', 'textos'],
        'buscadores' => ['Google y redes', 'Cómo se ve Malakplace en Google y cuando alguien comparte el enlace por WhatsApp o Facebook.', 'textos'],
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
        'pay_transfer' => ['planes', 'billing.methods.transfer', 'Aceptar pagos por transferencia', 'El proveedor sube la foto del comprobante.', 'checkbox', ['boolean'], 'bool'],
        'pay_cash' => ['planes', 'billing.methods.cash', 'Aceptar pagos en efectivo', 'El proveedor avisa y tú lo apruebas al recibir el dinero.', 'checkbox', ['boolean'], 'bool'],
        'hero_eyebrow' => ['portada', 'content.hero_eyebrow', 'Línea sobre el titular', 'Ej.: Malacatos — Loja, Ecuador', 'text', ['required', 'string', 'max:80'], 'string'],
        'hero_title' => ['portada', 'content.hero_title', 'Titular', 'Ej.: Gente de oficio cerca de ti, *en Malacatos.*', 'text', ['required', 'string', 'max:120'], 'string'],
        'hero_lede' => ['portada', 'content.hero_lede', 'Texto bajo el titular', 'Una o dos frases.', 'textarea', ['required', 'string', 'max:300'], 'string'],
        'about_text' => ['portada', 'content.about_text', 'Presentación de Malakplace', 'Aparece debajo del buscador.', 'textarea', ['required', 'string', 'max:400'], 'string'],
        'providers_title' => ['portada', 'content.providers_title', 'Título de la sección para proveedores', '', 'text', ['required', 'string', 'max:120'], 'string'],
        'footer_tagline' => ['portada', 'content.footer_tagline', 'Frase del pie de página', 'Aparece en todas las páginas.', 'text', ['required', 'string', 'max:160'], 'string'],
        'announcement_enabled' => ['aviso', 'content.announcement.enabled', 'Mostrar el aviso', '', 'checkbox', ['boolean'], 'bool'],
        'announcement_text' => ['aviso', 'content.announcement.text', 'Texto del aviso', 'Ej.: Este feriado respondemos desde el lunes.', 'text', ['nullable', 'string', 'max:160'], 'string'],
        'announcement_link' => ['aviso', 'content.announcement.link', 'Enlace (opcional)', 'Una página para saber más. Déjalo vacío si no hace falta.', 'url', ['nullable', 'url:https,http', 'max:300'], 'string'],
        'seo_title' => ['buscadores', 'content.seo_title', 'Título', 'Unos 60 caracteres.', 'text', ['required', 'string', 'max:70'], 'string'],
        'seo_description' => ['buscadores', 'content.seo_description', 'Descripción', 'Unos 150 caracteres: qué es y dónde.', 'textarea', ['required', 'string', 'max:180'], 'string'],
        'share_image' => ['buscadores', 'content.share_image', 'Imagen al compartir (dirección https)', 'Una foto de 1200×630. Vacío: la de siempre.', 'url', ['nullable', 'url:https', 'max:500'], 'string'],
        'background_valid_days' => ['verificacion', 'marketplace.verification.background_valid_days', 'Días que dura el sello de antecedentes', 'El certificado oficial vale 3 meses (90 días). Entre 30 y 365.', 'number', ['required', 'integer', 'between:30,365'], 'int'],
    ];

    /**
     * Textos largos con su propia pantalla (/admin/legal), y la versión vigente de los términos.
     * clave => config
     */
    public const TEXTS = [
        'legal_terms' => 'content.legal.terms',
        'legal_privacy' => 'content.legal.privacy',
        'legal_summary_all' => 'content.legal.summary_all',
        'legal_summary_provider' => 'content.legal.summary_provider',
        'legal_summary_customer' => 'content.legal.summary_customer',
        'legal_consent' => 'content.legal.consent',
        'terms_version' => 'marketplace.terms_version',
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

        if (isset(self::TEXTS[$key])) {
            return config(self::TEXTS[$key]);
        }

        return null;
    }

    /**
     * Los campos de una pantalla ('ajustes' o 'textos').
     *
     * @return array<string, array>
     */
    public static function fieldsFor(string $page): array
    {
        return array_filter(self::FIELDS, fn (array $field) => (self::GROUPS[$field[0]][2] ?? null) === $page);
    }

    /**
     * @param  array<string, mixed>  $values  clave => valor
     */
    public static function save(array $values): void
    {
        $now = now();
        foreach ($values as $key => $value) {
            $stored = match (true) {
                is_array($value) => json_encode($value, JSON_UNESCAPED_UNICODE),
                is_bool($value) => $value ? '1' : '0',
                $value === null || $value === '' => null,
                default => (string) $value,
            };
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
                'bool' => $value === '1' || $value === 'true',
                default => $value,
            }]);
        }

        foreach (self::TEXTS as $key => $configKey) {
            if (isset($values[$key]) && $values[$key] !== '') {
                config([$configKey => $values[$key]]);
            }
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
