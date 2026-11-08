<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Livewire\HomeComponent;
use App\Models\Review;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        $category = new ServiceCategory();
        $category->forceFill(['name' => 'Gasfitería', 'slug' => 'gasfiteria', 'image' => 'g.png', 'featured' => true])->save();

        $provider = User::factory()->create(['utype' => 'SVP', 'name' => 'Pedro Jaramillo']);
        ServiceProvider::create(['user_id' => $provider->id])->syncZones(['malacatos', 'vilcabamba']);

        $this->service = new Service();
        $this->service->forceFill([
            'user_id' => $provider->id, 'name' => 'Destape de tuberías', 'slug' => 'destape', 'tagline' => 'Rápido',
            'service_category_id' => $category->id, 'price' => 15, 'featured' => true,
            'image' => 'i.png', 'thumbnail' => 't.png',
        ])->save();
    }

    public function test_landing_shows_real_data_and_explores_by_zone(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Gente de oficio cerca de ti', false)
            ->assertSee('Destape de tuberías')
            ->assertSee('Malacatos · Vilcabamba')
            ->assertSee('Gasfitería')
            ->assertSee(route('search', ['zona' => 'vilcabamba']), false)
            ->assertSee(route('home.services_by_category', ['category_slug' => 'gasfiteria']), false)
            ->assertSee('Zona principal · 1 proveedor')
            ->assertSee('Principal')
            ->assertDontSee('Catamayo');
    }

    public function test_hero_strip_shows_services_and_the_brand(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<title>Malakplace', false)
            ->assertSee('data-hero-services', false)
            // The strip repeats the service to fill the width and links to its page with its thumbnail.
            ->assertSee('images/services/thumbnails/t.png', false)
            ->assertSeeInOrder(['data-hero-services', 'Destape de tuberías', 'Gasfitería', '· Pedro'], false)
            ->assertSee('Desde $15.00')
            ->assertDontSee('Sin intermediarios');
    }

    public function test_landing_is_lightweight(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        // No legacy theme: no jQuery, Bootstrap, slider plugins, Font Awesome or SweetAlert.
        foreach (['jquery', 'bootstrap.min', 'themepunch', 'font-awesome', 'sweetalert'] as $heavy) {
            $this->assertStringNotContainsStringIgnoringCase($heavy, $html, "La landing no debe cargar {$heavy}");
        }
    }

    public function test_guests_get_the_auth_modal_and_users_their_menu(): void
    {
        $this->get('/')->assertSee('lp-auth-title', false)->assertSee('Empezar gratis');

        $provider = User::where('utype', 'SVP')->firstOrFail();
        $this->actingAs($provider)->get('/')
            ->assertOk()
            ->assertDontSee('lp-auth-title', false)
            ->assertSee(route('sprovider.add_service'), false)
            ->assertSee('Mis servicios');
    }

    public function test_landing_cache_is_refreshed_by_new_reviews(): void
    {
        $this->get('/')->assertOk();
        $this->assertTrue(Cache::has(HomeComponent::CACHE_KEY));

        $customer = User::factory()->create(['name' => 'Carla Ruiz']);
        $request = ServiceRequest::create([
            'service_id' => $this->service->id, 'provider_id' => $this->service->user_id, 'customer_id' => $customer->id,
            'name' => 'Carla', 'phone' => '0991234567', 'zone' => 'malacatos', 'status' => 'completed',
        ]);
        Review::create(['service_id' => $this->service->id, 'service_request_id' => $request->id, 'user_id' => $customer->id, 'rating' => 5, 'comment' => 'Impecable y puntual']);

        $this->assertFalse(Cache::has(HomeComponent::CACHE_KEY));
        $this->get('/')->assertSee('Impecable y puntual')->assertSee('Carla');
    }
}
