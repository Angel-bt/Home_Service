<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\ServiceRequest;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Quién puede mover una solicitud y hacia dónde. Es la única regla del flujo (ServiceRequest::transitionTo).
 */
class ServiceRequestTransitionsTest extends TestCase
{
    private const PROVIDER = 10;
    private const CUSTOMER = 20;
    private const OTHER = 30;
    private const ADMIN = 40;

    private function request(string $status): ServiceRequest
    {
        return (new ServiceRequest())->forceFill(['status' => $status, 'provider_id' => self::PROVIDER, 'customer_id' => self::CUSTOMER]);
    }

    private function user(int $id, string $type): User
    {
        return (new User())->forceFill(['id' => $id, 'utype' => $type]);
    }

    /**
     * Todas las combinaciones: [estado actual, estado nuevo, proveedor, cliente, otro, admin].
     */
    public static function matrix(): array
    {
        $p = ServiceRequest::PENDING;
        $a = ServiceRequest::ACCEPTED;
        $r = ServiceRequest::REJECTED;
        $c = ServiceRequest::COMPLETED;
        $x = ServiceRequest::CANCELLED;

        return [
            'pendiente → aceptada' => [$p, $a, true, false, false, true],
            'pendiente → rechazada' => [$p, $r, true, false, false, true],
            'pendiente → cancelada' => [$p, $x, false, true, false, false],
            'pendiente → terminada' => [$p, $c, false, false, false, false],
            'aceptada → terminada' => [$a, $c, true, false, false, true],
            'aceptada → cancelada' => [$a, $x, false, true, false, false],
            'aceptada → rechazada' => [$a, $r, false, false, false, false],
            'aceptada → pendiente' => [$a, $p, false, false, false, false],
            'rechazada → aceptada' => [$r, $a, false, false, false, false],
            'terminada → cancelada' => [$c, $x, false, false, false, false],
            'terminada → aceptada' => [$c, $a, false, false, false, false],
            'cancelada → pendiente' => [$x, $p, false, false, false, false],
            'cancelada → aceptada' => [$x, $a, false, false, false, false],
        ];
    }

    #[DataProvider('matrix')]
    public function test_who_can_move_a_request(string $from, string $to, bool $provider, bool $customer, bool $other, bool $admin): void
    {
        $request = $this->request($from);

        $this->assertSame($provider, $request->canTransitionTo($to, $this->user(self::PROVIDER, 'SVP')), 'proveedor');
        $this->assertSame($customer, $request->canTransitionTo($to, $this->user(self::CUSTOMER, 'CST')), 'cliente');
        $this->assertSame($other, $request->canTransitionTo($to, $this->user(self::OTHER, 'SVP')), 'otro proveedor');
        $this->assertSame($admin, $request->canTransitionTo($to, $this->user(self::ADMIN, 'ADM')), 'administración');
    }

    public function test_a_forbidden_move_throws_and_changes_nothing(): void
    {
        $request = $this->request(ServiceRequest::PENDING);

        try {
            $request->transitionTo(ServiceRequest::COMPLETED, $this->user(self::PROVIDER, 'SVP'));
            $this->fail('Debió lanzar una excepción');
        } catch (\DomainException $exception) {
            $this->assertSame('No puedes cambiar esta solicitud a ese estado.', $exception->getMessage());
        }

        $this->assertSame(ServiceRequest::PENDING, $request->status);
    }

    public function test_every_status_has_a_spanish_label(): void
    {
        foreach ([ServiceRequest::PENDING, ServiceRequest::ACCEPTED, ServiceRequest::REJECTED, ServiceRequest::COMPLETED, ServiceRequest::CANCELLED] as $status) {
            $this->assertNotSame($status, $this->request($status)->statusLabel());
        }
    }

    public function test_only_its_customer_reviews_and_only_when_completed(): void
    {
        $customer = $this->user(self::CUSTOMER, 'CST');

        $this->assertFalse($this->request(ServiceRequest::ACCEPTED)->canTransitionTo(ServiceRequest::COMPLETED, $customer));
    }
}
