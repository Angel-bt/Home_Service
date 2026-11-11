<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Jetstream\DeleteUser;
use App\Http\Livewire\Admin\AdminFaqComponent;
use App\Http\Livewire\Admin\AdminReviewsComponent;
use App\Http\Livewire\Admin\AdminSettingsComponent;
use App\Http\Livewire\Admin\AdminUsersComponent;
use App\Http\Livewire\Admin\Service\AdminContactComponent;
use App\Http\Livewire\Admin\Service\AdminServicesComponent;
use App\Http\Livewire\Sprovider\SproviderServicesComponent;
use App\Models\Contact;
use App\Models\Faq;
use App\Models\ProviderZone;
use App\Models\Review;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Support\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class AdminToolsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['utype' => 'ADM']);
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

    // --- Ajustes ---

    public function test_the_admin_changes_settings_without_touching_code(): void
    {
        Livewire::actingAs($this->admin())->test(AdminSettingsComponent::class)
            ->set('values.contact_phone', '0981234567')
            ->set('values.free_service_limit', 5)
            ->set('values.pro_price', '10.5')
            ->set('values.bank_name', 'Banco de Loja')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(5, config('marketplace.free_service_limit'));
        $this->assertSame(10.5, config('billing.pro_price'));

        // En la siguiente petición los valores salen de la base, no de config/*.php.
        Cache::flush();
        config(['marketplace.free_service_limit' => 3, 'billing.bank_transfer.bank' => null]);
        SiteSettings::apply();
        $this->assertSame(5, config('marketplace.free_service_limit'));
        $this->assertSame('Banco de Loja', config('billing.bank_transfer.bank'));
        $this->get('/faq')->assertSee('permite 5 servicios')->assertSee('$10.50');
    }

    public function test_settings_explain_mistakes_and_are_admin_only(): void
    {
        Livewire::actingAs($this->admin())->test(AdminSettingsComponent::class)
            ->set('values.contact_phone', '12')
            ->set('values.contact_email', 'no-es-correo')
            ->set('values.free_service_limit', 0)
            ->call('save')
            ->assertHasErrors(['values.contact_phone', 'values.contact_email', 'values.free_service_limit']);

        Livewire::actingAs($this->provider())->test(AdminSettingsComponent::class)->call('save')->assertForbidden();
    }

    public function test_zones_can_be_renamed_and_added_but_not_removed_while_in_use(): void
    {
        $provider = $this->provider();
        ProviderZone::create(['service_provider_id' => $provider->serviceProvider->id, 'zone' => 'yangana']);

        $component = Livewire::actingAs($this->admin())->test(AdminSettingsComponent::class)
            ->set('zones.vilcabamba', 'Vilcabamba centro')
            ->call('saveZones')
            ->set('newZone', 'San Pedro')
            ->call('addZone')
            ->assertHasNoErrors();

        $this->assertSame('Vilcabamba centro', config('marketplace.zones.vilcabamba'));
        $this->assertSame('San Pedro', config('marketplace.zones.san-pedro'));

        $component->call('removeZone', 'yangana');
        $this->assertArrayHasKey('yangana', config('marketplace.zones'));

        $component->call('removeZone', 'san-pedro');
        $this->assertArrayNotHasKey('san-pedro', config('marketplace.zones'));
    }

    // --- Usuarios ---

    public function test_a_suspended_account_cannot_sign_in_and_its_services_disappear(): void
    {
        $provider = $this->provider();
        $service = $this->service($provider);

        Livewire::actingAs($this->admin())->test(AdminUsersComponent::class)
            ->call('startSuspend', $provider->id)
            ->call('suspend')->assertHasErrors('suspendReason')
            ->set('suspendReason', 'No llegó a tres trabajos')
            ->call('suspend')->assertHasNoErrors();

        $this->assertTrue($provider->fresh()->isSuspended());
        auth()->logout();

        $this->post('/login', ['email' => $provider->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => $provider->fresh()->suspensionMessage()]);
        $this->assertGuest();

        $this->get('/buscar?q=Destape')->assertDontSee($service->name);
        $this->get(route('home.service_details', ['service_slug' => $service->slug]))->assertSee('Este servicio ya no está disponible');

        Livewire::actingAs($this->admin())->test(AdminUsersComponent::class)->call('reactivate', $provider->id);
        auth()->logout();
        $this->get('/buscar?q=Destape')->assertSee($service->name);
    }

    public function test_an_open_session_ends_when_the_account_is_suspended(): void
    {
        $customer = User::factory()->create(['utype' => 'CST']);
        $this->actingAs($customer)->get('/customer/dashboard')->assertOk();

        $customer->forceFill(['suspended_at' => now(), 'suspension_reason' => 'Spam'])->save();

        $this->get('/customer/dashboard')->assertRedirect(route('login'));
        $this->assertGuest('web');
    }

    public function test_nobody_suspends_demotes_or_deletes_themselves_or_the_last_admin(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)->test(AdminUsersComponent::class)
            ->call('startSuspend', $admin->id)->set('suspendReason', 'x')->call('suspend')
            ->call('changeRole', $admin->id, 'CST')
            ->call('deleteUser', $admin->id);

        $this->assertNull($admin->fresh()->suspended_at);
        $this->assertSame('ADM', $admin->fresh()->utype);

        $other = $this->admin();
        Livewire::actingAs($other)->test(AdminUsersComponent::class)->call('changeRole', $admin->id, 'CST');
        $this->assertSame('CST', $admin->fresh()->utype);
        $this->actingAs($admin->fresh())->get('/admin/users')->assertRedirect();
    }

    public function test_changing_roles_keeps_the_rules(): void
    {
        $admin = $this->admin();
        $customer = User::factory()->create(['utype' => 'CST']);
        $provider = $this->provider();
        $this->service($provider);

        Livewire::actingAs($admin)->test(AdminUsersComponent::class)
            ->call('changeRole', $customer->id, 'SVP')
            ->call('changeRole', $provider->id, 'CST');

        // Un cliente que pasa a proveedor tiene perfil, y debe aceptar que se publique antes de seguir.
        $this->assertSame('SVP', $customer->fresh()->utype);
        $this->assertNotNull($customer->fresh()->serviceProvider);
        $this->assertNull($customer->fresh()->terms_version);
        // Un proveedor con servicios no deja de serlo: sus servicios quedarían sin dueño.
        $this->assertSame('SVP', $provider->fresh()->utype);
    }

    public function test_the_admin_finds_accounts_by_name_email_or_phone(): void
    {
        User::factory()->create(['name' => 'Rosa Cueva', 'email' => 'rosa@example.test', 'utype' => 'SVP', 'phone' => '0984445566']);
        User::factory()->create(['name' => 'Carlos Ordóñez', 'utype' => 'CST']);

        $component = Livewire::actingAs($this->admin())->test(AdminUsersComponent::class);
        $component->set('q', 'ROSA')->assertSee('Rosa Cueva')->assertDontSee('Carlos Ordóñez');
        $component->set('q', '4445566')->assertSee('Rosa Cueva');
        $component->set('q', '')->set('role', 'CST')->assertSee('Carlos Ordóñez')->assertDontSee('Rosa Cueva');
    }

    public function test_deleting_an_account_also_deletes_its_services(): void
    {
        $provider = $this->provider();
        $service = $this->service($provider);

        app(DeleteUser::class)->delete($provider);

        $this->assertModelMissing($service);
        $this->get(route('home.service_details', ['service_slug' => $service->slug]))->assertSee('Este servicio ya no está disponible');
    }

    // --- Servicios ---

    public function test_hidden_and_paused_services_are_only_seen_by_their_owner_and_the_admin(): void
    {
        $provider = $this->provider();
        $service = $this->service($provider);
        $url = route('home.service_details', ['service_slug' => $service->slug]);

        Livewire::actingAs($provider)->test(SproviderServicesComponent::class)->call('togglePause', $service->id);
        $this->assertNotNull($service->fresh()->paused_at);

        auth()->logout();
        $this->get($url)->assertSee('Este servicio ya no está disponible');
        $this->get('/buscar?q=Destape')->assertDontSee($service->name);
        $this->actingAs($provider)->get($url)->assertSee('Está pausado por quien lo publicó');

        Livewire::actingAs($this->admin())->test(AdminServicesComponent::class)->call('toggleVisible', $service->id);
        Livewire::actingAs($provider)->test(SproviderServicesComponent::class)->call('togglePause', $service->id)->assertSee('Oculto por la administración');
        $this->assertFalse($service->fresh()->isPublic());
        $this->actingAs($provider)->get($url)->assertSee('La administración lo ocultó');
    }

    public function test_a_provider_cannot_pause_someone_elses_service(): void
    {
        $service = $this->service($this->provider());

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        Livewire::actingAs($this->provider())->test(SproviderServicesComponent::class)->call('togglePause', $service->id);
    }

    public function test_the_admin_highlights_and_searches_services(): void
    {
        $service = $this->service($this->provider());
        $this->service($this->provider(), 'Poda de árboles');

        Livewire::actingAs($this->admin())->test(AdminServicesComponent::class)
            ->call('toggleFeatured', $service->id)
            ->set('filter', 'featured')
            ->assertSee('Destape de tuberías')->assertDontSee('Poda de árboles')
            ->set('filter', '')->set('q', 'poda')
            ->assertSee('Poda de árboles')->assertDontSee('Destape de tuberías');

        $this->assertTrue($service->fresh()->featured);
    }

    // --- Reseñas y mensajes ---

    public function test_a_hidden_review_disappears_and_stops_counting(): void
    {
        $provider = $this->provider();
        $service = $this->service($provider);
        $customer = User::factory()->create(['utype' => 'CST']);
        foreach ([[5, 'Excelente trabajo'], [1, 'Insultos aquí']] as [$rating, $comment]) {
            $request = ServiceRequest::create(['service_id' => $service->id, 'provider_id' => $provider->id, 'customer_id' => $customer->id, 'name' => 'Luis', 'phone' => '0990000000', 'zone' => 'malacatos', 'status' => ServiceRequest::COMPLETED]);
            Review::create(['service_id' => $service->id, 'service_request_id' => $request->id, 'user_id' => $customer->id, 'rating' => $rating, 'comment' => $comment]);
        }
        $bad = Review::where('rating', 1)->firstOrFail();

        Livewire::actingAs($this->admin())->test(AdminReviewsComponent::class)
            ->call('startHide', $bad->id)->set('hideReason', 'Ofensiva')->call('hide')->assertHasNoErrors();

        auth()->logout();
        $this->get(route('home.service_details', ['service_slug' => $service->slug]))
            ->assertSee('Excelente trabajo')->assertDontSee('Insultos aquí')->assertSee('5.0');
    }

    public function test_messages_are_marked_answered_and_deleted(): void
    {
        $contact = new Contact();
        $contact->forceFill(['name' => 'Ana', 'email' => 'ana@example.test', 'phone' => '0991234567', 'message' => 'Hola'])->save();

        $component = Livewire::actingAs($this->admin())->test(AdminContactComponent::class)
            ->assertSee('Ana')
            ->call('toggleAnswered', $contact->id)
            ->assertDontSee('ana@example.test');
        $this->assertNotNull($contact->fresh()->answered_at);

        $component->call('deleteContact', $contact->id);
        $this->assertModelMissing($contact);
    }

    // --- Preguntas frecuentes ---

    public function test_the_admin_edits_the_faq_and_the_public_page_follows(): void
    {
        $component = Livewire::actingAs($this->admin())->test(AdminFaqComponent::class)
            ->call('create', 'General')
            ->call('save')->assertHasErrors(['question', 'answer'])
            ->set('question', '¿Atienden los domingos?')
            ->set('answer', 'Depende de cada proveedor. Escríbenos al {whatsapp}.')
            ->call('save')->assertHasNoErrors();

        $faq = Faq::where('question', '¿Atienden los domingos?')->firstOrFail();
        $this->get('/faq')->assertSee('¿Atienden los domingos?')->assertSee('Escríbenos al ' . config('marketplace.contact_phone'));

        // Recién creada queda última; al subirla pasa al penúltimo lugar.
        $order = fn () => Faq::where('group', 'General')->orderBy('position')->orderBy('id')->pluck('id')->all();
        $this->assertSame($faq->id, last($order()));
        $component->call('move', $faq->id, -1);
        $this->assertSame($faq->id, $order()[count($order()) - 2]);

        $component->call('delete', $faq->id);
        $this->get('/faq')->assertDontSee('¿Atienden los domingos?');
    }

    public function test_the_faq_starts_with_the_previous_questions(): void
    {
        $this->assertGreaterThanOrEqual(13, Faq::count());
        $this->get('/faq')->assertSee('¿Cuánto cuesta usar Malakplace?')->assertSee('En Malacatos, Vilcabamba, Yangana y Quinara.');
    }
}
