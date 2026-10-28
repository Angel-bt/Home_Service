<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Livewire\Admin\AdminPaymentsComponent;
use App\Http\Livewire\Sprovider\SproviderPlanComponent;
use App\Models\ServiceProvider;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Services\PayphoneGateway;
use App\Services\ProPlanBilling;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProPlanBillingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'billing.pro_price' => 8.00,
            'billing.pro_period_days' => 30,
            'billing.tax_rate' => 0,
            'marketplace.free_service_limit' => 3,
        ]);
    }

    private function provider(): User
    {
        $user = User::factory()->create(['utype' => 'SVP']);
        ServiceProvider::create(['user_id' => $user->id]);

        return $user;
    }

    private function admin(): User
    {
        return User::factory()->create(['utype' => 'ADM']);
    }

    public function test_provider_uploads_a_receipt_that_stays_private(): void
    {
        Storage::fake('receipts');
        $provider = $this->provider();

        Livewire::actingAs($provider)->test(SproviderPlanComponent::class)
            ->set('method', 'deuna')
            ->set('periods', 3)
            ->set('reference', 'DEUNA-123')
            ->set('receipt', UploadedFile::fake()->image('comprobante.jpg'))
            ->call('submitReceipt')
            ->assertHasNoErrors();

        $payment = SubscriptionPayment::firstOrFail();
        $this->assertSame('pending', $payment->status);
        $this->assertEquals(24.00, (float) $payment->amount);
        Storage::disk('receipts')->assertExists($payment->receipt_path);

        // Only one manual payment under review at a time.
        Livewire::actingAs($provider)->test(SproviderPlanComponent::class)
            ->set('reference', 'OTRO')
            ->set('receipt', UploadedFile::fake()->image('otro.jpg'))
            ->call('submitReceipt')
            ->assertHasErrors('reference');
        $this->assertSame(1, SubscriptionPayment::count());

        // The receipt is only reachable by administrators.
        $this->actingAs($provider)->get(route('admin.payment_receipt', $payment))->assertRedirect('/login');
        $this->actingAs($this->admin())->get(route('admin.payment_receipt', $payment))->assertOk();
    }

    public function test_receipt_validation(): void
    {
        Livewire::actingAs($this->provider())->test(SproviderPlanComponent::class)
            ->set('periods', 2)
            ->set('receipt', UploadedFile::fake()->create('virus.exe', 10))
            ->call('submitReceipt')
            ->assertHasErrors(['periods', 'reference', 'receipt']);
    }

    public function test_admin_approval_activates_and_renewal_extends_pro(): void
    {
        Carbon::setTestNow('2026-11-01 10:00:00');
        $provider = $this->provider();
        $admin = $this->admin();

        $first = $provider->subscriptionPayments()->create(['method' => 'transfer', 'periods' => 1, 'amount' => 8, 'status' => 'pending', 'reference' => 'T1']);
        Livewire::actingAs($admin)->test(AdminPaymentsComponent::class)->call('approve', $first->id);

        $provider->refresh();
        $this->assertTrue($provider->isPro());
        $this->assertNull($provider->servicePublicationLimit());
        $this->assertSame('2026-12-01', $provider->plan_expires_at->toDateString());
        $this->assertSame($admin->id, $first->fresh()->reviewed_by);

        // Approving twice does not add more days.
        app(ProPlanBilling::class)->approve($first->fresh(), $admin);
        $this->assertSame('2026-12-01', $provider->fresh()->plan_expires_at->toDateString());

        // Renewing early adds the days to the current expiry date.
        $second = $provider->subscriptionPayments()->create(['method' => 'deuna', 'periods' => 3, 'amount' => 24, 'status' => 'pending', 'reference' => 'T2']);
        app(ProPlanBilling::class)->approve($second, $admin);
        $this->assertSame('2027-03-01', $provider->fresh()->plan_expires_at->toDateString());

        Carbon::setTestNow();
    }

    public function test_admin_rejects_with_reason_and_non_admins_cannot_review(): void
    {
        $provider = $this->provider();
        $payment = $provider->subscriptionPayments()->create(['method' => 'transfer', 'periods' => 1, 'amount' => 8, 'status' => 'pending', 'reference' => 'X']);

        Livewire::actingAs($this->admin())->test(AdminPaymentsComponent::class)
            ->call('startReject', $payment->id)
            ->call('reject')
            ->assertHasErrors('rejectReason')
            ->set('rejectReason', 'No encontramos la transferencia')
            ->call('reject')
            ->assertHasNoErrors();

        $this->assertSame('rejected', $payment->fresh()->status);
        $this->assertFalse($provider->fresh()->isPro());

        $this->actingAs($provider)->get(route('admin.payments'))->assertRedirect('/login');
    }

    public function test_expired_plans_fall_back_to_the_free_limit(): void
    {
        $provider = $this->provider();
        $provider->forceFill(['subscription_plan' => 'pro', 'plan_expires_at' => now()->subMinute()])->save();

        // Even before the scheduled command runs, an expired plan no longer counts as Pro.
        $this->assertFalse($provider->fresh()->isPro());
        $this->assertSame(3, $provider->fresh()->servicePublicationLimit());

        $abandoned = $provider->subscriptionPayments()->create(['method' => 'payphone', 'periods' => 1, 'amount' => 8, 'status' => 'initiated', 'client_transaction_id' => 'OLD']);
        $abandoned->forceFill(['created_at' => now()->subDays(2)])->save();

        $this->artisan('billing:expire-plans')->assertSuccessful();

        $this->assertSame('free', $provider->fresh()->subscription_plan);
        $this->assertSame('rejected', $abandoned->fresh()->status);
    }

    public function test_manual_pro_without_expiry_never_expires(): void
    {
        $provider = $this->provider();
        $provider->forceFill(['subscription_plan' => 'pro', 'plan_expires_at' => null])->save();

        $this->artisan('billing:expire-plans')->assertSuccessful();

        $this->assertTrue($provider->fresh()->isPro());
    }

    private function enablePayphone(): void
    {
        config([
            'billing.payphone.enabled' => true,
            'billing.payphone.token' => 'test-token',
            'billing.payphone.store_id' => 'store-1',
            'billing.payphone.base_url' => 'https://payphone.test',
        ]);
    }

    public function test_payphone_payment_is_prepared_and_confirmed(): void
    {
        $this->enablePayphone();
        $provider = $this->provider();

        Http::fake([
            'payphone.test/api/button/Prepare' => Http::response(['paymentId' => 'pay-1', 'payWithCard' => 'https://payphone.test/card/pay-1', 'payWithPayPhone' => 'https://payphone.test/app/pay-1']),
            'payphone.test/api/button/V2/Confirm' => function (HttpRequest $request) {
                return Http::response([
                    'statusCode' => 3,
                    'transactionStatus' => 'Approved',
                    'clientTransactionId' => $request['clientTxId'],
                    'amount' => 2400,
                    'transactionId' => 987654,
                    'authorizationCode' => 'W123',
                ]);
            },
        ]);

        $this->actingAs($provider)->post(route('billing.payphone.start'), ['periods' => 3])
            ->assertRedirect('https://payphone.test/card/pay-1');

        $payment = SubscriptionPayment::firstOrFail();
        $this->assertSame('initiated', $payment->status);

        Http::assertSent(fn (HttpRequest $request) => str_ends_with($request->url(), '/api/button/Prepare')
            && $request->hasHeader('Authorization', 'Bearer test-token')
            && $request['amount'] === 2400
            && $request['amountWithoutTax'] === 2400
            && $request['storeId'] === 'store-1'
            && $request['clientTransactionId'] === $payment->client_transaction_id
            && $request['responseUrl'] === route('billing.payphone.return'));

        // Payphone sends the user back, possibly with an expired session.
        $this->app['auth']->forgetGuards();
        $this->flushSession();
        $this->get(route('billing.payphone.return', ['id' => 987654, 'clientTransactionId' => $payment->client_transaction_id]))
            ->assertRedirect(route('sprovider.plan'));

        $payment->refresh();
        $this->assertSame('approved', $payment->status);
        $this->assertSame('987654', $payment->gateway_transaction_id);
        $this->assertTrue($provider->fresh()->isPro());

        // Reloading the return URL does not approve twice.
        $this->get(route('billing.payphone.return', ['id' => 987654, 'clientTransactionId' => $payment->client_transaction_id]))
            ->assertSessionHas('message');
        Http::assertSentCount(2);
    }

    public function test_payphone_rejects_canceled_or_tampered_transactions(): void
    {
        $this->enablePayphone();
        $provider = $this->provider();
        $payment = $provider->subscriptionPayments()->create(['method' => 'payphone', 'periods' => 1, 'amount' => 8, 'status' => 'initiated', 'client_transaction_id' => 'PRO1-ABC']);

        // Approved by Payphone but for a different amount: never trust it.
        Http::fake(['payphone.test/*' => Http::response(['statusCode' => 3, 'transactionStatus' => 'Approved', 'clientTransactionId' => 'PRO1-ABC', 'amount' => 100])]);

        $this->get(route('billing.payphone.return', ['id' => 5, 'clientTransactionId' => 'PRO1-ABC']))
            ->assertSessionHas('error');

        $this->assertSame('rejected', $payment->fresh()->status);
        $this->assertFalse($provider->fresh()->isPro());

        $this->get(route('billing.payphone.return', ['id' => 5, 'clientTransactionId' => 'NO-EXISTE']))->assertSessionHas('error');
    }

    public function test_payphone_errors_and_disabled_gateway(): void
    {
        $provider = $this->provider();
        $this->actingAs($provider)->post(route('billing.payphone.start'), ['periods' => 1])->assertNotFound();

        $this->enablePayphone();
        Http::fake(['payphone.test/*' => Http::response(['message' => 'Token inválido', 'errorCode' => 401], 401)]);

        $this->actingAs($provider)->post(route('billing.payphone.start'), ['periods' => 1])
            ->assertRedirect(route('sprovider.plan'))
            ->assertSessionHas('error');

        $this->assertSame('rejected', SubscriptionPayment::firstOrFail()->status);
    }

    public function test_payphone_amounts_include_tax_when_configured(): void
    {
        config(['billing.tax_rate' => 0.15]);

        $this->assertSame(
            ['amount' => 920, 'amountWithoutTax' => 0, 'amountWithTax' => 800, 'tax' => 120],
            app(PayphoneGateway::class)->amounts(9.20)
        );
    }

    public function test_plan_and_payments_pages_render(): void
    {
        $provider = $this->provider();
        $provider->subscriptionPayments()->create(['method' => 'transfer', 'periods' => 1, 'amount' => 8, 'status' => 'pending', 'reference' => 'R']);

        $this->actingAs($provider)->get(route('sprovider.plan'))->assertOk()->assertSee('Plan Pro')->assertSee('En revisión');
        $this->actingAs($this->admin())->get(route('admin.payments'))->assertOk()->assertSee($provider->name);
        $this->actingAs($this->admin())->get(route('admin.dashboard'))->assertOk()->assertSee('por revisar');
    }
}
