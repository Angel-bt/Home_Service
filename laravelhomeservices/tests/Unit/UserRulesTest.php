<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

/**
 * Reglas de una cuenta que no dependen de la base: plan, límite, panel, términos y suspensión.
 */
class UserRulesTest extends TestCase
{
    private function user(array $attributes): User
    {
        return (new User())->forceFill($attributes + ['utype' => 'SVP', 'subscription_plan' => 'free']);
    }

    public function test_pro_depends_on_the_plan_and_its_expiry(): void
    {
        $this->assertFalse($this->user([])->isPro());
        $this->assertTrue($this->user(['subscription_plan' => 'pro'])->isPro(), 'Pro sin fecha no vence');
        $this->assertTrue($this->user(['subscription_plan' => 'pro', 'plan_expires_at' => now()->addDay()])->isPro());
        $this->assertFalse($this->user(['subscription_plan' => 'pro', 'plan_expires_at' => now()->subMinute()])->isPro(), 'vencido');
    }

    public function test_the_publication_limit(): void
    {
        config(['marketplace.free_service_limit' => 4]);

        $this->assertSame(4, $this->user([])->servicePublicationLimit());
        $this->assertNull($this->user(['subscription_plan' => 'pro'])->servicePublicationLimit());
        $this->assertNull($this->user(['utype' => 'ADM'])->servicePublicationLimit());
        $this->assertSame(4, $this->user(['subscription_plan' => 'pro', 'plan_expires_at' => now()->subDay()])->servicePublicationLimit());
    }

    public function test_each_role_has_its_panel(): void
    {
        $this->assertSame('admin.dashboard', $this->user(['utype' => 'ADM'])->dashboardRoute());
        $this->assertSame('sprovider.dashboard', $this->user(['utype' => 'SVP'])->dashboardRoute());
        $this->assertSame('customer.dashboard', $this->user(['utype' => 'CST'])->dashboardRoute());
    }

    public function test_terms_follow_the_current_version(): void
    {
        config(['marketplace.terms_version' => '2026-11-04']);

        $this->assertTrue($this->user(['terms_version' => '2026-11-04'])->hasAcceptedCurrentTerms());
        $this->assertFalse($this->user(['terms_version' => '2026-01-01'])->hasAcceptedCurrentTerms());
        $this->assertFalse($this->user(['terms_version' => null])->hasAcceptedCurrentTerms());
    }

    public function test_suspension_and_its_message(): void
    {
        config(['marketplace.contact_email' => 'ayuda@example.test']);

        $this->assertFalse($this->user([])->isSuspended());

        $suspended = $this->user(['suspended_at' => now(), 'suspension_reason' => 'No llegó a los trabajos.']);
        $this->assertTrue($suspended->isSuspended());
        $this->assertSame('Tu cuenta está suspendida: No llegó a los trabajos. Si crees que es un error, escríbenos a ayuda@example.test.', $suspended->suspensionMessage());
        $this->assertSame('Tu cuenta está suspendida. Si crees que es un error, escríbenos a ayuda@example.test.', $this->user(['suspended_at' => now()])->suspensionMessage());
    }
}
