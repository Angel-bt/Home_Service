<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Faq;
use App\Models\ProviderZone;
use App\Support\Activity;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Textos que el sistema arma solo: búsquedas normalizadas, respuestas con datos y nombres de zonas.
 */
class TextHelpersTest extends TestCase
{
    public static function terms(): array
    {
        return [
            'mayúsculas' => ['PLOMERO', 'plomero'],
            'tildes' => ['Plomería', 'plomeria'],
            'la ñ se queda' => ['Albañil', 'albañil'],
            'espacios de más' => ['  corte   de  césped ', 'corte de cesped'],
            'diéresis' => ['Pingüino', 'pinguino'],
        ];
    }

    #[DataProvider('terms')]
    public function test_searches_are_grouped_regardless_of_how_they_were_typed(string $typed, string $stored): void
    {
        $this->assertSame($stored, Activity::normalizeTerm($typed));
    }

    public function test_long_searches_are_cut_to_fit_the_column(): void
    {
        $this->assertSame(100, mb_strlen(Activity::normalizeTerm(str_repeat('a', 300))));
    }

    public function test_faq_answers_fill_in_current_values(): void
    {
        config([
            'marketplace.free_service_limit' => 5,
            'billing.pro_price' => 9.5,
            'billing.pro_period_days' => 30,
            'marketplace.zones' => ['malacatos' => 'Malacatos', 'vilcabamba' => 'Vilcabamba', 'quinara' => 'Quinara'],
            'marketplace.contact_phone' => '0991234567',
            'marketplace.contact_email' => 'ayuda@example.test',
        ]);

        $faq = new Faq(['answer' => 'Gratis {limite_gratis}; Pro {precio_pro} por {dias_pro} días; en {zonas}; {whatsapp} o {correo}. {otro}']);

        $this->assertSame('Gratis 5; Pro $9.50 por 30 días; en Malacatos, Vilcabamba y Quinara; 0991234567 o ayuda@example.test. {otro}', $faq->renderedAnswer());
    }

    public function test_a_single_zone_reads_naturally(): void
    {
        config(['marketplace.zones' => ['malacatos' => 'Malacatos']]);

        $this->assertSame('En Malacatos.', (new Faq(['answer' => 'En {zonas}.']))->renderedAnswer());
    }

    public function test_zone_labels_follow_the_settings(): void
    {
        config(['marketplace.zones' => ['malacatos' => 'Malacatos centro']]);

        $this->assertSame('Malacatos centro', (new ProviderZone(['zone' => 'malacatos']))->label());
        $this->assertSame('zona-borrada', (new ProviderZone(['zone' => 'zona-borrada']))->label());
    }
}
