<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Livewire\Customer\CustomerDashboardComponent;
use App\Http\Livewire\Service\SearchComponent;
use App\Http\Livewire\Service\ServiceDetailsComponent;
use App\Http\Livewire\Service\ServicesByCategoryComponent;
use App\Http\Livewire\Sprovider\EditSproviderProfileComponent;
use App\Http\Livewire\Sprovider\SproviderAddServiceComponent;
use App\Http\Livewire\Sprovider\SproviderEditServiceComponent;
use App\Http\Livewire\Sprovider\SproviderRequestsComponent;
use App\Http\Livewire\Sprovider\SproviderServicesComponent;
use App\Models\Review;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Support\WhatsApp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MarketplaceFlowTest extends TestCase
{
    use RefreshDatabase;

    private ServiceCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = new ServiceCategory();
        $this->category->name = 'Jardinería';
        $this->category->slug = 'jardineria';
        $this->category->image = 'jardineria.png';
        $this->category->save();
    }

    private function provider(array $zones = ['malacatos'], ?string $whatsapp = '0991112233'): User
    {
        $user = User::factory()->create(['utype' => 'SVP', 'phone' => '0990000000']);
        $profile = ServiceProvider::create(['user_id' => $user->id]);
        $profile->whatsapp = $whatsapp;
        $profile->save();
        $profile->syncZones($zones);

        return $user;
    }

    private function service(User $owner, string $name = 'Poda de árboles', array $attributes = []): Service
    {
        $service = new Service();
        $service->forceFill($attributes + [
            'user_id' => $owner->id,
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'tagline' => 'Trabajo garantizado',
            'service_category_id' => $this->category->id,
            'price' => 25,
            'description' => 'Trabajo realizado por personal con experiencia',
            'inclusion' => 'Mano de obra',
            'exclusion' => 'Transporte de ramas',
            'image' => 'image.png',
            'thumbnail' => 'thumb.png',
        ]);
        $service->save();

        return $service;
    }

    private function fillServiceForm($component, string $name)
    {
        return $component
            ->set('name', $name)
            ->call('generateSlug')
            ->set('tagline', 'Rápido y garantizado')
            ->set('service_category_id', $this->category->id)
            ->set('price', '30')
            ->set('description', 'Descripción')
            ->set('inclusion', "Mano de obra\nRevisión")
            ->set('exclusion', 'Materiales');
    }

    public function test_provider_publishes_a_service_with_images(): void
    {
        Storage::fake('local');
        $provider = $this->provider();

        $component = Livewire::actingAs($provider)->test(SproviderAddServiceComponent::class);
        $this->fillServiceForm($component, 'Limpieza de piscinas')
            ->set('image', UploadedFile::fake()->image('piscina.jpg'))
            ->set('thumbnail', UploadedFile::fake()->image('mini.jpg'))
            ->call('createService')
            ->assertHasNoErrors()
            ->assertRedirect(route('sprovider.services'));

        $service = Service::where('slug', 'limpieza-de-piscinas')->firstOrFail();
        $this->assertSame($provider->id, $service->user_id);
        $this->assertSame('Mano de obra|Revisión', $service->inclusion);
        Storage::disk('local')->assertExists('services/' . $service->image);
        Storage::disk('local')->assertExists('services/thumbnails/' . $service->thumbnail);
    }

    public function test_free_plan_blocks_publishing_beyond_the_limit(): void
    {
        config(['marketplace.free_service_limit' => 1]);
        $provider = $this->provider();
        $this->service($provider);

        $component = Livewire::actingAs($provider)->test(SproviderAddServiceComponent::class);
        $this->fillServiceForm($component, 'Segundo servicio')
            ->set('image', UploadedFile::fake()->image('a.jpg'))
            ->set('thumbnail', UploadedFile::fake()->image('b.jpg'))
            ->call('createService')
            ->assertHasErrors('name');

        $this->assertSame(1, $provider->services()->count());
    }

    public function test_provider_cannot_edit_or_delete_someone_elses_service(): void
    {
        $owner = $this->provider();
        $intruder = $this->provider();
        $service = $this->service($owner);

        $this->actingAs($intruder)->get(route('sprovider.edit_service', ['service_slug' => $service->slug]))->assertNotFound();

        Livewire::actingAs($intruder)->test(SproviderServicesComponent::class)->call('deleteService', $service->id);
        $this->assertTrue(Service::whereKey($service->id)->exists());
    }

    public function test_provider_edits_own_service(): void
    {
        $provider = $this->provider();
        $service = $this->service($provider);

        Livewire::actingAs($provider)
            ->test(SproviderEditServiceComponent::class, ['service_slug' => $service->slug])
            ->set('price', '40')
            ->call('updateService')
            ->assertHasNoErrors();

        $this->assertEquals(40, (float) $service->fresh()->price);
    }

    public function test_provider_profile_saves_whatsapp_and_zones(): void
    {
        $provider = $this->provider([]);

        Livewire::actingAs($provider)
            ->test(EditSproviderProfileComponent::class)
            ->set('whatsapp', '099 123 4567')
            ->set('zones', ['vilcabamba', 'quinara', 'zona-falsa'])
            ->call('updateProfile')
            ->assertHasErrors('zones.2');

        Livewire::actingAs($provider)
            ->test(EditSproviderProfileComponent::class)
            ->set('whatsapp', '099 123 4567')
            ->set('zones', ['vilcabamba', 'quinara'])
            ->call('updateProfile')
            ->assertHasNoErrors();

        $profile = $provider->serviceProvider()->first();
        $this->assertSame('099 123 4567', $profile->whatsapp);
        $this->assertEqualsCanonicalizing(['vilcabamba', 'quinara'], $profile->zones()->pluck('zone')->all());
    }

    public function test_full_request_and_review_flow(): void
    {
        $provider = $this->provider();
        $service = $this->service($provider);
        $customer = User::factory()->create(['utype' => 'CST', 'phone' => '0987654321']);

        Livewire::actingAs($customer)
            ->test(ServiceDetailsComponent::class, ['service_slug' => $service->slug])
            ->set('zone', 'malacatos')
            ->set('address', 'Barrio Landangui')
            ->set('message', 'Necesito podar 3 árboles')
            ->call('submitRequest')
            ->assertHasNoErrors();

        $request = ServiceRequest::firstOrFail();
        $this->assertSame($provider->id, $request->provider_id);
        $this->assertSame('pending', $request->status);

        // El cliente no puede reseñar antes de que se complete.
        Livewire::actingAs($customer)->test(CustomerDashboardComponent::class)
            ->call('startReview', $request->id)->set('rating', 5)->call('saveReview')->assertHasErrors('comment');

        // Otro proveedor no puede tocar la solicitud.
        $this->expectNotFound(fn () => Livewire::actingAs($this->provider())->test(SproviderRequestsComponent::class)
            ->call('changeStatus', $request->id, 'accepted'));

        Livewire::actingAs($provider)->test(SproviderRequestsComponent::class)->call('changeStatus', $request->id, 'accepted');
        Livewire::actingAs($provider)->test(SproviderRequestsComponent::class)->call('changeStatus', $request->id, 'completed');
        $this->assertSame('completed', $request->fresh()->status);

        Livewire::actingAs($customer)->test(CustomerDashboardComponent::class)
            ->call('startReview', $request->id)->set('rating', 4)->set('comment', 'Muy puntual')->call('saveReview')
            ->assertHasNoErrors();

        $this->assertSame(4, Review::firstOrFail()->rating);

        // Una sola reseña por solicitud.
        Livewire::actingAs($customer)->test(CustomerDashboardComponent::class)
            ->call('startReview', $request->id)->call('saveReview')->assertHasErrors('comment');
        $this->assertSame(1, Review::count());

        $this->get(route('home.service_details', ['service_slug' => $service->slug]))
            ->assertOk()
            ->assertSee('Muy puntual')
            ->assertSee('wa.me/593991112233', false);
    }

    public function test_guests_are_sent_to_login_and_owners_cannot_request_their_service(): void
    {
        $provider = $this->provider();
        $service = $this->service($provider);

        Livewire::test(ServiceDetailsComponent::class, ['service_slug' => $service->slug])
            ->call('submitRequest')
            ->assertRedirect(route('login'));

        Livewire::actingAs($provider)
            ->test(ServiceDetailsComponent::class, ['service_slug' => $service->slug])
            ->set('zone', 'malacatos')
            ->call('submitRequest')
            ->assertHasErrors('message');

        $this->assertSame(0, ServiceRequest::count());
    }

    public function test_customer_can_cancel_but_provider_cannot_cancel(): void
    {
        $provider = $this->provider();
        $customer = User::factory()->create();
        $request = ServiceRequest::create([
            'service_id' => $this->service($provider)->id,
            'provider_id' => $provider->id,
            'customer_id' => $customer->id,
            'name' => 'Ana', 'phone' => '0991234567', 'zone' => 'malacatos',
        ]);

        $this->assertFalse($request->canTransitionTo('cancelled', $provider));
        $this->assertFalse($request->canTransitionTo('completed', $provider));

        Livewire::actingAs($customer)->test(CustomerDashboardComponent::class)->call('cancelRequest', $request->id);
        $this->assertSame('cancelled', $request->fresh()->status);
    }

    public function test_search_and_zone_filters(): void
    {
        $malacatos = $this->provider(['malacatos']);
        $vilcabamba = $this->provider(['vilcabamba']);
        $admin = User::factory()->create(['utype' => 'ADM']);
        $this->service($malacatos, 'Poda de árboles');
        $this->service($vilcabamba, 'Corte de césped');
        $this->service($admin, 'Fumigación de jardín');

        Livewire::test(SearchComponent::class)
            ->set('q', 'poda')
            ->assertSee('Poda de árboles')
            ->assertDontSee('Corte de césped');

        // Buscar por nombre de categoría.
        Livewire::test(SearchComponent::class)
            ->set('q', 'Jardinería')
            ->assertSee('Poda de árboles')
            ->assertSee('Corte de césped');

        // Los servicios de la administración aparecen en todas las zonas.
        Livewire::test(ServicesByCategoryComponent::class, ['category_slug' => 'jardineria'])
            ->set('zona', 'vilcabamba')
            ->assertSee('Corte de césped')
            ->assertSee('Fumigación de jardín')
            ->assertDontSee('Poda de árboles');

        $this->assertSame('vilcabamba', session('zone'));

        $this->get('/buscar?q=poda&zona=malacatos')->assertOk()->assertSee('Poda de árboles');
        $this->post('/search', ['q' => 'poda'])->assertRedirect(route('search', ['q' => 'poda']));
        $this->getJson('/autocomplete?query=cort')->assertExactJson([['name' => 'Corte de césped']]);
    }

    public function test_provider_and_admin_pages_render(): void
    {
        $provider = $this->provider();
        $service = $this->service($provider);
        $admin = User::factory()->create(['utype' => 'ADM']);

        foreach (['sprovider.dashboard', 'sprovider.services', 'sprovider.add_service', 'sprovider.requests'] as $route) {
            $this->actingAs($provider)->get(route($route))->assertOk();
        }
        $this->actingAs($provider)->get(route('sprovider.edit_service', ['service_slug' => $service->slug]))->assertOk();
        $this->actingAs($admin)->get(route('admin.service_requests'))->assertOk();
        $this->actingAs(User::factory()->create(['utype' => 'CST']))->get(route('sprovider.services'))->assertRedirect('/login');
    }

    public function test_whatsapp_numbers_are_normalised_for_ecuador(): void
    {
        $this->assertSame('593991234567', WhatsApp::normalize('099 123 4567'));
        $this->assertSame('593991234567', WhatsApp::normalize('+593 99 123 4567'));
        $this->assertSame('593991234567', WhatsApp::normalize('00593991234567'));
        $this->assertSame('593991234567', WhatsApp::normalize('991234567'));
        $this->assertNull(WhatsApp::normalize('123'));
        $this->assertNull(WhatsApp::normalize(null));
    }

    public function test_service_details_page_renders(): void
    {
        $service = $this->service($this->provider());

        $this->get(route('home.service_details', ['service_slug' => $service->slug]))
            ->assertOk()
            ->assertSee('Solicitar este servicio')
            ->assertSee('Escribir por WhatsApp');
    }

    private function expectNotFound(callable $callback): void
    {
        try {
            $callback();
            $this->fail('Se esperaba un 404.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            $this->addToAssertionCount(1);
        }
    }
}
