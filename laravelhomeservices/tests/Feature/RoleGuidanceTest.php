<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Livewire\Sprovider\SproviderAddServiceComponent;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use App\Models\SubscriptionPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Each role must know what to do next, and every failure must explain itself.
 */
class RoleGuidanceTest extends TestCase
{
    use RefreshDatabase;

    private function provider(): User
    {
        $provider = User::factory()->create(['utype' => 'SVP', 'name' => 'Pedro Jaramillo']);
        ServiceProvider::create(['user_id' => $provider->id]);

        return $provider;
    }

    private function service(User $owner, string $slug = 'poda'): Service
    {
        $category = ServiceCategory::query()->firstOrNew(['slug' => 'jardineria']);
        $category->forceFill(['name' => 'Jardinería', 'image' => 'j.png'])->save();

        $service = new Service();
        $service->forceFill([
            'user_id' => $owner->id, 'name' => 'Poda de árboles', 'slug' => $slug, 'tagline' => 'Rápido',
            'service_category_id' => $category->id, 'price' => 20, 'image' => 'i.png', 'thumbnail' => 't.png',
        ])->save();

        return $service;
    }

    public function test_wrong_role_goes_to_its_own_panel_with_an_explanation_and_stays_signed_in(): void
    {
        $provider = $this->provider();

        $this->actingAs($provider)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('sprovider.dashboard'))
            ->assertSessionHas('error');
        $this->assertAuthenticatedAs($provider);

        $this->actingAs($provider)
            ->followingRedirects()
            ->get(route('admin.payments'))
            ->assertOk()
            ->assertSee('Esa página es solo para la administración');
    }

    public function test_each_role_lands_on_its_panel_after_signing_in(): void
    {
        $provider = $this->provider();

        $this->post('/login', ['email' => $provider->email, 'password' => 'password'])
            ->assertRedirect(route('sprovider.dashboard'));
    }

    public function test_new_providers_see_their_first_steps_until_they_are_done(): void
    {
        $provider = $this->provider();

        $this->actingAs($provider)->get(route('sprovider.dashboard'))
            ->assertOk()
            ->assertSee('Para empezar a recibir clientes')
            ->assertSee('Llevas 0 de 3')
            ->assertSee('Pon tu WhatsApp y las zonas donde trabajas');

        $provider->serviceProvider->forceFill(['whatsapp' => '0991112233', 'image' => 'p.png', 'about' => 'Jardinero'])->save();
        $provider->serviceProvider->syncZones(['malacatos']);
        $this->service($provider);

        $this->actingAs($provider)->get(route('sprovider.dashboard'))
            ->assertOk()
            ->assertDontSee('Para empezar a recibir clientes');
    }

    public function test_provider_panel_points_at_pending_requests(): void
    {
        $provider = $this->provider();
        $customer = User::factory()->create(['utype' => 'CST']);
        $request = ServiceRequest::create([
            'service_id' => $this->service($provider)->id, 'provider_id' => $provider->id, 'customer_id' => $customer->id,
            'name' => 'Carla', 'phone' => '0987654321', 'zone' => 'malacatos', 'status' => ServiceRequest::PENDING,
        ]);

        $this->actingAs($provider)->get(route('sprovider.dashboard'))->assertOk()->assertSee('Un cliente espera');
        $this->actingAs($provider)->get(route('sprovider.requests'))
            ->assertOk()
            ->assertSee('Este cliente espera tu respuesta')
            ->assertSee("changeStatus({$request->id}, 'accepted')", false)
            ->assertSee("changeStatus({$request->id}, 'rejected')", false)
            // Only offered once the work was accepted.
            ->assertDontSee("changeStatus({$request->id}, 'completed')", false);
    }

    public function test_customers_without_requests_learn_how_it_works(): void
    {
        $this->actingAs(User::factory()->create(['utype' => 'CST']))
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('Busca lo que necesitas')
            ->assertSee(route('search'), false);
    }

    public function test_customer_request_explains_its_stage(): void
    {
        $provider = $this->provider();
        $customer = User::factory()->create(['utype' => 'CST']);
        ServiceRequest::create([
            'service_id' => $this->service($provider)->id, 'provider_id' => $provider->id, 'customer_id' => $customer->id,
            'name' => 'Carla', 'phone' => '0987654321', 'zone' => 'malacatos', 'status' => ServiceRequest::REJECTED,
        ]);

        $this->actingAs($customer)->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('Prueba con otro proveedor')
            ->assertSee('Buscar otro proveedor');
    }

    public function test_admin_home_lists_what_is_pending(): void
    {
        $admin = User::factory()->create(['utype' => 'ADM']);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('Todo al día');

        $provider = $this->provider();
        SubscriptionPayment::create(['user_id' => $provider->id, 'method' => 'cash', 'periods' => 1, 'amount' => 8, 'status' => SubscriptionPayment::PENDING]);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Todo al día')
            ->assertSee('1 pago del plan Pro por revisar')
            ->assertSee('1 proveedor con el perfil incompleto');
    }

    public function test_validation_says_what_is_missing_in_plain_words(): void
    {
        Livewire::actingAs($this->provider())
            ->test(SproviderAddServiceComponent::class)
            ->call('createService', app(\App\Services\ServicePublicationGate::class))
            ->assertHasErrors(['name', 'price'])
            ->assertHasNoErrors('slug')
            ->assertSee('Falta el nombre.');
    }

    public function test_service_links_number_themselves_when_the_name_is_taken(): void
    {
        $provider = $this->provider();
        $this->service($provider, 'poda-de-arboles');

        Livewire::actingAs($provider)
            ->test(SproviderAddServiceComponent::class)
            ->set('name', 'Poda de árboles')
            ->call('generateSlug')
            ->assertSet('slug', 'poda-de-arboles-2');
    }

    public function test_error_pages_explain_what_to_do(): void
    {
        $this->get('/esta-pagina-no-existe')->assertNotFound()->assertSee('No encontramos')->assertSee('Buscar servicios');
    }

    public function test_inner_pages_no_longer_load_the_old_theme(): void
    {
        $this->get(route('search'))
            ->assertOk()
            ->assertDontSee('assets/css/style.css', false)
            ->assertDontSee('jquery', false)
            ->assertDontSee('font-awesome', false);
    }
}
