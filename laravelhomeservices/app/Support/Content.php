<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Muestra los textos que escribe la administración sin que puedan romper la página ni
 * meter código: todo se escapa, y solo se aceptan unas pocas marcas sencillas.
 *
 * - rich(): una línea con *asteriscos* para destacar (titulares de la portada).
 * - markdown(): textos largos (legales) con ## títulos, **negritas**, listas y enlaces.
 * - fill(): datos que cambian solos ({correo}, {zonas}…), tomados de los ajustes.
 */
final class Content
{
    public const PLACEHOLDERS = [
        '{limite_gratis}' => 'servicios gratis por proveedor',
        '{precio_pro}' => 'precio del plan Pro',
        '{dias_pro}' => 'días que dura el plan Pro',
        '{zonas}' => 'zonas donde funciona',
        '{whatsapp}' => 'WhatsApp de Malakplace',
        '{correo}' => 'correo de contacto',
        '{dias_actividad}' => 'días que se guarda la actividad',
    ];

    public static function fill(string $text): string
    {
        $zones = array_values(config('marketplace.zones'));
        $zonesText = count($zones) > 1 ? implode(', ', array_slice($zones, 0, -1)) . ' y ' . Arr::last($zones) : (string) ($zones[0] ?? '');

        return strtr($text, [
            '{limite_gratis}' => (string) config('marketplace.free_service_limit'),
            '{precio_pro}' => '$' . number_format((float) config('billing.pro_price'), 2),
            '{dias_pro}' => (string) config('billing.pro_period_days'),
            '{zonas}' => $zonesText,
            '{whatsapp}' => (string) config('marketplace.contact_phone'),
            '{correo}' => (string) config('marketplace.contact_email'),
            '{dias_actividad}' => (string) config('marketplace.activity_retention_days'),
        ]);
    }

    /**
     * Una línea de texto donde lo que va entre *asteriscos* sale destacado.
     */
    public static function rich(?string $text, string $accentClass = 'lp-accent'): HtmlString
    {
        $safe = e(self::fill((string) $text));
        $html = preg_replace('/\*(.+?)\*/u', '<span class="' . e($accentClass) . '">$1</span>', $safe);

        return new HtmlString((string) $html);
    }

    /**
     * Texto largo con formato sencillo. El HTML se escapa, los enlaces raros se descartan
     * y cada título recibe un id para poder enlazarlo (#verificacion-y-sellos).
     */
    public static function markdown(?string $text): HtmlString
    {
        $text = self::fill((string) $text);
        // Los correos sueltos se vuelven enlaces.
        $text = (string) preg_replace('/(?<![<\w.@])([\w.+-]+@[\w-]+(?:\.[\w-]+)+)(?![>\w])/u', '<$1>', $text);

        $html = Str::markdown($text, ['html_input' => 'escape', 'allow_unsafe_links' => false]);

        $html = (string) preg_replace_callback('/<(h[2-4])>(.*?)<\/\1>/s', static function (array $match): string {
            $slug = Str::slug(preg_replace('/^\s*\d+[.)]\s*/', '', strip_tags($match[2])));

            return '<' . $match[1] . ' id="' . $slug . '" class="scroll-mt-28">' . $match[2] . '</' . $match[1] . '>';
        }, $html);

        return new HtmlString($html);
    }

    /**
     * Fecha de una versión de los términos ("2026-11-04" o "2026-11-04-2") para mostrarla.
     */
    public static function versionDate(?string $version): string
    {
        $date = substr((string) $version, 0, 10);

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1 ? \Illuminate\Support\Carbon::parse($date)->format('d/m/Y') : (string) $version;
    }
}
