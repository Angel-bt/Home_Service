<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Service;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Precio final y visibilidad de un servicio.
 */
class ServiceRulesTest extends TestCase
{
    public static function prices(): array
    {
        return [
            'sin descuento' => [20, null, null, 20.0],
            'descuento fijo' => [20, 5, 'fixed', 15.0],
            'descuento en porcentaje' => [20, 10, 'percent', 18.0],
            'porcentaje con decimales' => [33.33, 15, 'percent', 28.33],
            'descuento mayor que el precio' => [20, 50, 'fixed', 0.0],
            'descuento cero' => [20, 0, 'fixed', 20.0],
        ];
    }

    #[DataProvider('prices')]
    public function test_final_price(float $price, ?float $discount, ?string $type, float $expected): void
    {
        $service = (new Service())->forceFill(['price' => $price, 'discount' => $discount, 'discount_type' => $type]);

        $this->assertSame($expected, $service->finalPrice());
    }

    private function service(array $attributes = [], array $owner = []): Service
    {
        $service = (new Service())->forceFill($attributes + ['status' => true, 'paused_at' => null]);
        $service->setRelation('user', (new User())->forceFill($owner + ['utype' => 'SVP', 'suspended_at' => null]));

        return $service;
    }

    public function test_who_sees_a_service(): void
    {
        $this->assertTrue($this->service()->isPublic());
        $this->assertNull($this->service()->hiddenReason());

        $hidden = $this->service(['status' => false]);
        $this->assertFalse($hidden->isPublic());
        $this->assertSame('La administración lo ocultó.', $hidden->hiddenReason());

        $paused = $this->service(['paused_at' => now()]);
        $this->assertFalse($paused->isPublic());
        $this->assertSame('Está pausado por quien lo publicó.', $paused->hiddenReason());

        $suspended = $this->service([], ['suspended_at' => now()]);
        $this->assertFalse($suspended->isPublic());
        $this->assertSame('La cuenta de quien lo publicó está suspendida.', $suspended->hiddenReason());
    }

    public function test_suspension_explains_first_when_several_reasons_apply(): void
    {
        $service = $this->service(['status' => false, 'paused_at' => now()], ['suspended_at' => now()]);

        $this->assertSame('La cuenta de quien lo publicó está suspendida.', $service->hiddenReason());
    }
}
