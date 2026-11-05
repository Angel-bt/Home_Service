<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Jetstream\DeleteUser;
use App\Http\Livewire\AcceptTermsComponent;
use App\Http\Livewire\Admin\AdminActivityComponent;
use App\Http\Livewire\Admin\AdminVerificationsComponent;
use App\Http\Livewire\Service\SearchComponent;
use App\Http\Livewire\Service\ServiceDetailsComponent;
use App\Http\Livewire\Sprovider\SproviderDashboardComponent;
use App\Http\Livewire\Sprovider\SproviderRequestsComponent;
use App\Http\Livewire\Sprovider\SproviderVerificationComponent;
use App\Mail\NoticeMail;
use App\Models\ActivityEvent;
use App\Models\ProviderDocument;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\ProviderVerification;
use App\Support\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TrustAndActivityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array<string, string>>
     */
    private function registration(array $extra = []): array
    {
        return $extra + [
            'name' => 'Ana Pérez', 'email' => 'ana@example.test', 'phone' => '0991234567',
            'password' => 'una-clave-larga', 'password_confirmation' => 'una-clave-larga',
        ];
    }

    private function provider(): User
    {
        $user = User::factory()->create(['utype' => 'SVP', 'phone' => '0991112233']);
        ServiceProvider::create(['user_id' => $user->id]);

        return $user;
    }

    private function service(User $owner, string $name = 'Destape de tuberías'): Service
    {
        $category = ServiceCategory::query()->first() ?? tap(new ServiceCategory())->forceFill(['name' => 'Gasfitería', 'slug' => 'gasfiteria', 'image' => 'g.png']);
        $category->save();

        $service = new Service();
        $service->forceFill([
            'user_id' => $owner->id, 'name' => $name, 'slug' => \Illuminate\Support\Str::slug($name), 'tagline' => 'x', 'service_category_id' => $category->id,
            'price' => 20, 'image' => 'i.png', 'thumbnail' => 't.png',
        ])->save();

        return $service;
    }

    private function uploadDocument(User $provider, string $type): ProviderDocument
    {
        return app(ProviderVerification::class)->submit($provider->serviceProvider, $type, UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'));
    }

    // --- Términos antes de registrarse ---

    public function test_nobody_registers_without_accepting_the_terms(): void
    {
        $this->post('/register', $this->registration(['registeras' => 'CST']))->assertSessionHasErrors('terms');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_a_provider_cannot_register_without_allowing_publication(): void
    {
        $this->post('/register', $this->registration(['registeras' => 'SVP', 'terms' => '1']))->assertSessionHasErrors('publication_consent');

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('service_providers', 0);
    }

    public function test_registration_records_what_was_accepted_and_when(): void
    {
        $this->post('/register', $this->registration(['registeras' => 'SVP', 'terms' => '1', 'publication_consent' => '1']))
            ->assertRedirect(route('sprovider.dashboard'));

        $user = User::where('email', 'ana@example.test')->firstOrFail();
        $this->assertSame(config('marketplace.terms_version'), $user->terms_version);
        $this->assertNotNull($user->terms_accepted_at);
        $this->assertNotNull($user->publication_consent_at);
    }

    public function test_customers_only_accept_the_terms(): void
    {
        $this->post('/register', $this->registration(['registeras' => 'CST', 'terms' => '1']))->assertRedirect();

        $user = User::where('email', 'ana@example.test')->firstOrFail();
        $this->assertNotNull($user->terms_accepted_at);
        $this->assertNull($user->publication_consent_at);
    }

    public function test_register_page_says_the_platform_is_only_an_intermediary(): void
    {
        $this->get('/register')->assertOk()
            ->assertSee('Malakplace solo conecta a las personas')
            ->assertSee('Malakplace no responde por robos')
            ->assertSee('Tu perfil será público');
    }

    // --- Cuentas que ya existían ---

    public function test_existing_accounts_must_accept_before_continuing(): void
    {
        $user = User::factory()->create(['utype' => 'CST', 'terms_version' => null, 'terms_accepted_at' => null]);

        $this->actingAs($user)->get('/customer/dashboard')->assertRedirect(route('terms.accept'));
        $this->get('/terms')->assertOk();
        $this->get('/privacy')->assertOk();
        $this->get('/user/profile')->assertOk();

        Livewire::actingAs($user)->test(AcceptTermsComponent::class)->call('accept')->assertHasErrors('terms');
        Livewire::actingAs($user)->test(AcceptTermsComponent::class)->set('terms', true)->call('accept')->assertRedirect();

        $this->actingAs($user->fresh())->get('/customer/dashboard')->assertOk();
    }

    public function test_existing_providers_must_also_allow_publication(): void
    {
        $user = $this->provider();
        $user->forceFill(['terms_version' => null])->save();

        Livewire::actingAs($user)->test(AcceptTermsComponent::class)->set('terms', true)->call('accept')->assertHasErrors('publication');
        $this->assertNull($user->fresh()->terms_version);

        Livewire::actingAs($user)->test(AcceptTermsComponent::class)->set('terms', true)->set('publication', true)->call('accept')->assertRedirect();
        $this->assertNotNull($user->fresh()->publication_consent_at);
    }

    public function test_a_new_version_of_the_terms_asks_everyone_again_except_the_admin(): void
    {
        $customer = User::factory()->create(['utype' => 'CST']);
        $admin = User::factory()->create(['utype' => 'ADM']);

        config(['marketplace.terms_version' => '2030-01-01']);

        $this->actingAs($customer)->get('/customer/dashboard')->assertRedirect(route('terms.accept'));
        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
    }

    // --- Verificación ---

    public function test_a_document_needs_explicit_consent_and_stays_private(): void
    {
        Storage::fake('receipts');
        $provider = $this->provider();

        $component = Livewire::actingAs($provider)->test(SproviderVerificationComponent::class)
            ->call('startUpload', ProviderDocument::BACKGROUND)
            ->set('document', UploadedFile::fake()->create('cert.pdf', 100, 'application/pdf'))
            ->call('submit')
            ->assertHasErrors('consent');
        $this->assertDatabaseCount('provider_documents', 0);

        $component->set('consent', true)->call('submit')->assertHasNoErrors();

        $document = ProviderDocument::firstOrFail();
        $this->assertSame(ProviderDocument::PENDING, $document->status);
        $this->assertNotNull($document->consent_at);
        Storage::disk('receipts')->assertExists($document->path);
        $this->assertSame([], $provider->serviceProvider->fresh()->trustBadges());
    }

    public function test_only_the_admin_can_see_a_document(): void
    {
        Storage::fake('receipts');
        $provider = $this->provider();
        $document = $this->uploadDocument($provider, ProviderDocument::BACKGROUND);
        $url = route('admin.provider_document', $document);

        $this->get($url)->assertRedirect(route('login'));
        $this->actingAs($provider)->get($url)->assertRedirect();
        $this->actingAs(User::factory()->create(['utype' => 'ADM']))->get($url)->assertOk();
    }

    public function test_only_the_admin_can_review_documents(): void
    {
        Storage::fake('receipts');
        $provider = $this->provider();
        $document = $this->uploadDocument($provider, ProviderDocument::BACKGROUND);

        Livewire::actingAs($provider)->test(AdminVerificationsComponent::class)
            ->call('approve', $document->id)
            ->assertForbidden();

        $this->assertSame(ProviderDocument::PENDING, $document->fresh()->status);
    }

    public function test_approval_publishes_the_badge_and_deletes_the_file(): void
    {
        Storage::fake('receipts');
        Mail::fake();
        $provider = $this->provider();
        $service = $this->service($provider);
        $document = $this->uploadDocument($provider, ProviderDocument::BACKGROUND);
        $path = $document->path;

        Livewire::actingAs(User::factory()->create(['utype' => 'ADM']))->test(AdminVerificationsComponent::class)
            ->call('approve', $document->id);

        Storage::disk('receipts')->assertMissing($path);
        $this->assertNull($document->fresh()->path);
        Mail::assertSent(NoticeMail::class, fn (NoticeMail $mail) => $mail->hasTo($provider->email));

        auth()->logout();
        $this->get(route('home.service_details', ['service_slug' => $service->slug]))->assertOk()
            ->assertSee('Sin antecedentes penales')
            ->assertSee('Certificado oficial revisado el ' . now()->format('d/m/Y'))
            ->assertDontSee('verificaciones/');
        $this->get('/buscar?q=Destape')->assertSee('Verificado');
    }

    public function test_a_rejected_document_is_deleted_gives_no_badge_and_shows_nothing_publicly(): void
    {
        Storage::fake('receipts');
        Mail::fake();
        $provider = $this->provider();
        $service = $this->service($provider);
        $document = $this->uploadDocument($provider, ProviderDocument::BACKGROUND);
        $path = $document->path;

        Livewire::actingAs(User::factory()->create(['utype' => 'ADM']))->test(AdminVerificationsComponent::class)
            ->call('startReject', $document->id)
            ->call('reject')->assertHasErrors('rejectReason')
            ->set('rejectReason', 'No se puede leer')
            ->call('reject')->assertHasNoErrors();

        Storage::disk('receipts')->assertMissing($path);
        $this->assertSame(ProviderDocument::REJECTED, $document->fresh()->status);
        $this->assertSame([], $provider->serviceProvider->fresh()->trustBadges());

        // Sigue en la plataforma: nadie puede deducir nada de que no tenga sello.
        auth()->logout();
        $this->get(route('home.service_details', ['service_slug' => $service->slug]))->assertOk()
            ->assertSee($service->name)
            ->assertDontSee('No se puede leer')
            ->assertDontSee('Sin antecedentes penales');
    }

    public function test_the_background_badge_expires(): void
    {
        Storage::fake('receipts');
        $provider = $this->provider();
        $document = $this->uploadDocument($provider, ProviderDocument::BACKGROUND);
        app(ProviderVerification::class)->approve($document, User::factory()->create(['utype' => 'ADM']));

        $profile = $provider->serviceProvider->fresh();
        $this->assertTrue($profile->isVerified());

        $this->travel((int) config('marketplace.verification.background_valid_days') + 1)->days();

        $this->assertFalse($profile->fresh()->isVerified());
        $this->assertSame([], $profile->fresh()->trustBadges());
    }

    public function test_the_provider_can_withdraw_consent_at_any_time(): void
    {
        Storage::fake('receipts');
        $provider = $this->provider();
        $document = $this->uploadDocument($provider, ProviderDocument::BACKGROUND);
        $path = $document->path;

        Livewire::actingAs($provider)->test(SproviderVerificationComponent::class)->call('withdraw', ProviderDocument::BACKGROUND);

        Storage::disk('receipts')->assertMissing($path);
        $this->assertDatabaseCount('provider_documents', 0);
    }

    public function test_deleting_the_account_deletes_pending_documents(): void
    {
        Storage::fake('receipts');
        $provider = $this->provider();
        $path = $this->uploadDocument($provider, ProviderDocument::IDENTITY)->path;

        app(DeleteUser::class)->delete($provider);

        Storage::disk('receipts')->assertMissing($path);
        $this->assertDatabaseCount('provider_documents', 0);
    }

    public function test_the_admin_is_told_when_a_document_arrives(): void
    {
        Storage::fake('receipts');
        Mail::fake();
        config(['marketplace.lead_notification_address' => 'admin@example.test']);

        $this->uploadDocument($this->provider(), ProviderDocument::IDENTITY);

        Mail::assertSent(NoticeMail::class, fn (NoticeMail $mail) => $mail->hasTo('admin@example.test'));
    }

    // --- Registro de actividad ---

    public function test_typing_a_search_counts_once_with_its_results(): void
    {
        $this->service($this->provider());

        Livewire::test(SearchComponent::class)
            ->set('q', 'Des')->set('q', 'Destape')->set('q', 'Dest');
        Livewire::withQueryParams(['q' => 'albañil'])->test(SearchComponent::class);

        $this->assertDatabaseCount('activity_events', 2);
        $this->assertDatabaseHas('activity_events', ['event' => 'search', 'term' => 'destape', 'results' => 1]);
        $this->assertDatabaseHas('activity_events', ['event' => 'search', 'term' => 'albañil', 'results' => 0]);
    }

    public function test_admin_page_shows_top_and_missing_searches(): void
    {
        Activity::record(ActivityEvent::SEARCH, ['term' => 'plomero', 'results' => 2]);
        Activity::record(ActivityEvent::SEARCH, ['term' => 'plomero', 'results' => 2]);
        Activity::record(ActivityEvent::SEARCH, ['term' => 'cerrajero', 'results' => 0]);

        $component = Livewire::actingAs(User::factory()->create(['utype' => 'ADM']))->test(AdminActivityComponent::class);

        $component->assertSee('plomero')->assertSee('cerrajero');
        $this->assertSame(['cerrajero'], $component->viewData('missingSearches')->pluck('term')->all());
        $this->assertSame('plomero', $component->viewData('topSearches')->first()->term);
    }

    public function test_old_activity_does_not_count_in_the_period_and_is_pruned(): void
    {
        ActivityEvent::create(['event' => 'search', 'term' => 'vieja', 'created_at' => now()->subDays(400)]);
        ActivityEvent::create(['event' => 'search', 'term' => 'nueva', 'created_at' => now()]);

        Livewire::actingAs(User::factory()->create(['utype' => 'ADM']))->test(AdminActivityComponent::class)
            ->assertSee('nueva')->assertDontSee('vieja');

        $this->artisan('activity:prune')->assertSuccessful();

        $this->assertDatabaseMissing('activity_events', ['term' => 'vieja']);
        $this->assertDatabaseHas('activity_events', ['term' => 'nueva']);
    }

    #[DataProvider('notCounted')]
    public function test_some_visits_do_not_count(string $who): void
    {
        $owner = $this->provider();
        $service = $this->service($owner);
        $url = route('home.service_details', ['service_slug' => $service->slug]);

        match ($who) {
            'owner' => $this->actingAs($owner)->get($url),
            'admin' => $this->actingAs(User::factory()->create(['utype' => 'ADM']))->get($url),
            'bot' => $this->withHeader('User-Agent', 'Mozilla/5.0 (compatible; Googlebot/2.1)')->get($url),
        };

        $this->assertDatabaseCount('activity_events', 0);
    }

    public static function notCounted(): array
    {
        return ['su dueño' => ['owner'], 'la administración' => ['admin'], 'un robot' => ['bot']];
    }

    public function test_a_failure_recording_activity_does_not_break_the_page(): void
    {
        $service = $this->service($this->provider());
        \Illuminate\Support\Facades\Schema::drop('activity_events');

        $this->get(route('home.service_details', ['service_slug' => $service->slug]))->assertOk();
    }

    public function test_the_whatsapp_button_counts_the_contact_and_goes_to_whatsapp(): void
    {
        $owner = $this->provider();
        $service = $this->service($owner);

        $this->get(route('home.service_whatsapp', ['service_slug' => $service->slug]))
            ->assertRedirectContains('wa.me/593991112233');

        $this->assertDatabaseHas('activity_events', ['event' => 'whatsapp_click', 'service_id' => $service->id, 'provider_id' => $owner->id]);
    }

    public function test_the_provider_sees_the_interest_of_the_last_30_days(): void
    {
        $owner = $this->provider();
        $service = $this->service($owner);
        Activity::forService(ActivityEvent::SERVICE_VIEW, $service);
        Activity::forService(ActivityEvent::SERVICE_VIEW, $service);
        Activity::forService(ActivityEvent::WHATSAPP_CLICK, $service);

        Livewire::actingAs($owner)->test(SproviderDashboardComponent::class)
            ->assertSee('2 visitas')
            ->assertSee('1 toque');
    }

    public function test_the_export_is_admin_only_anonymous_and_safe_for_excel(): void
    {
        $customer = User::factory()->create(['utype' => 'CST', 'email' => 'secreto@example.test']);
        ActivityEvent::create(['event' => 'search', 'term' => '=cmd()', 'user_id' => $customer->id, 'created_at' => now()]);

        $this->actingAs($customer)->get(route('admin.activity_export'))->assertRedirect();

        $response = $this->actingAs(User::factory()->create(['utype' => 'ADM']))->get(route('admin.activity_export'))->assertOk();
        $csv = $response->streamedContent();

        $this->assertStringContainsString("'=cmd()", $csv);
        $this->assertStringNotContainsString('secreto@example.test', $csv);
    }

    // --- Avisos para que la gente vuelva ---

    public function test_the_provider_is_told_about_a_new_request(): void
    {
        Mail::fake();
        $owner = $this->provider();
        $service = $this->service($owner);
        $customer = User::factory()->create(['utype' => 'CST', 'phone' => '0990000000']);

        Livewire::actingAs($customer)->test(ServiceDetailsComponent::class, ['service_slug' => $service->slug])
            ->set('zone', 'malacatos')->set('message', 'Gotea')
            ->call('submitRequest')->assertHasNoErrors();

        $this->assertDatabaseCount('service_requests', 1);
        $this->assertDatabaseHas('activity_events', ['event' => 'request_created', 'service_id' => $service->id, 'user_id' => $customer->id]);
        Mail::assertSent(NoticeMail::class, fn (NoticeMail $mail) => $mail->hasTo($owner->email));
    }

    public function test_the_customer_is_told_when_the_request_moves_and_invited_to_review(): void
    {
        Mail::fake();
        $owner = $this->provider();
        $service = $this->service($owner);
        $customer = User::factory()->create(['utype' => 'CST']);
        $request = ServiceRequest::create([
            'service_id' => $service->id, 'provider_id' => $owner->id, 'customer_id' => $customer->id,
            'name' => 'Luis', 'phone' => '0990000000', 'zone' => 'malacatos', 'status' => ServiceRequest::PENDING,
        ]);

        $component = Livewire::actingAs($owner)->test(SproviderRequestsComponent::class);
        $component->call('changeStatus', $request->id, ServiceRequest::ACCEPTED);
        Mail::assertSent(NoticeMail::class, fn (NoticeMail $mail) => $mail->hasTo($customer->email) && $mail->title === 'Aceptaron tu solicitud en Malakplace');

        $component->call('changeStatus', $request->id, ServiceRequest::COMPLETED);
        Mail::assertSent(NoticeMail::class, fn (NoticeMail $mail) => $mail->hasTo($customer->email) && $mail->title === '¿Cómo te fue con el trabajo?');
        $this->assertDatabaseHas('activity_events', ['event' => 'request_completed', 'user_id' => $customer->id]);
    }

    public function test_a_mail_failure_does_not_undo_the_request(): void
    {
        config(['mail.default' => 'caido', 'mail.mailers.caido' => ['transport' => 'smtp', 'host' => '127.0.0.1', 'port' => 1, 'timeout' => 1]]);
        $service = $this->service($this->provider());
        $customer = User::factory()->create(['utype' => 'CST', 'phone' => '0990000000']);

        Livewire::actingAs($customer)->test(ServiceDetailsComponent::class, ['service_slug' => $service->slug])
            ->set('zone', 'malacatos')->call('submitRequest')->assertHasNoErrors();

        $this->assertDatabaseCount('service_requests', 1);
    }
}
