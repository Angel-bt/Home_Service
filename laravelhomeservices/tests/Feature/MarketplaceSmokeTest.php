<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke tests for the base tutorial flows: every page renders for the right role.
 */
class MarketplaceSmokeTest extends TestCase
{
    use RefreshDatabase;

    private ServiceCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = new ServiceCategory();
        $this->category->name = 'Plomeria';
        $this->category->slug = 'plomeria';
        $this->category->image = 'plomeria.png';
        $this->category->featured = true;
        $this->category->save();

        $service = new Service();
        $service->name = 'Destape de tuberias';
        $service->slug = 'destape-de-tuberias';
        $service->tagline = 'Rapido y garantizado';
        $service->service_category_id = $this->category->id;
        $service->price = 20;
        $service->featured = true;
        $service->description = 'Descripcion';
        $service->inclusion = 'Mano de obra|Revision';
        $service->exclusion = 'Repuestos';
        $service->thumbnail = 'thumb.png';
        $service->image = 'image.png';
        $service->save();
    }

    /**
     * @return array<string, array{string}>
     */
    public function publicUrls(): array
    {
        return [
            'home' => ['/'],
            'categories' => ['/service-category'],
            'services by category' => ['/plomeria/services'],
            'contact' => ['/contact-us'],
            'change location' => ['/change-location'],
            'about' => ['/about-us'],
            'faq' => ['/faq'],
            'terms' => ['/terms'],
            'privacy' => ['/privacy'],
            'login' => ['/login'],
            'register' => ['/register'],
            'autocomplete' => ['/autocomplete?query=Destape'],
        ];
    }

    /**
     * @dataProvider publicUrls
     */
    public function test_public_pages_render(string $url): void
    {
        $this->get($url)->assertOk();
    }

    public function test_admin_pages_render_on_a_fresh_database(): void
    {
        $admin = User::factory()->create(['utype' => 'ADM']);

        foreach ([
            '/admin/dashboard',
            '/admin/service-categories',
            '/admin/service-category/add',
            '/admin/service-category/edit/' . $this->category->id,
            '/admin/all-services',
            '/admin/plomeria/services',
            '/admin/service/add',
            '/admin/service/edit/destape-de-tuberias',
            '/admin/slider',
            '/admin/slide/add',
            '/admin/contacts',
            '/admin/service-providers',
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_admin_pages_return_404_for_unknown_records(): void
    {
        $admin = User::factory()->create(['utype' => 'ADM']);

        foreach ([
            '/admin/service-category/edit/999',
            '/admin/service/edit/no-existe',
            '/admin/slide/edit/999',
            '/admin/no-existe/services',
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertNotFound();
        }
    }

    public function test_provider_and_customer_pages_render(): void
    {
        $provider = User::factory()->create(['utype' => 'SVP']);
        ServiceProvider::create(['user_id' => $provider->id]);

        foreach (['/sprovider/dashboard', '/sprovider/profile', '/sprovider/profile/edit'] as $url) {
            $this->actingAs($provider)->get($url)->assertOk();
        }

        $customer = User::factory()->create(['utype' => 'CST']);
        $this->actingAs($customer)->get('/customer/dashboard')->assertOk();
    }

    public function test_non_admins_are_kicked_out_of_admin_pages(): void
    {
        $customer = User::factory()->create(['utype' => 'CST']);

        $this->actingAs($customer)->get('/admin/dashboard')->assertRedirect('/login');
    }

    public function test_registration_without_role_creates_a_customer(): void
    {
        $this->post('/register', $this->registrationData('cliente@example.com'));

        $this->assertSame('CST', User::where('email', 'cliente@example.com')->value('utype'));
    }

    public function test_registration_as_provider_creates_the_provider_profile(): void
    {
        $this->post('/register', $this->registrationData('proveedor@example.com') + ['registeras' => 'SVP']);

        $user = User::where('email', 'proveedor@example.com')->firstOrFail();
        $this->assertSame('SVP', $user->utype);
        $this->assertTrue(ServiceProvider::where('user_id', $user->id)->exists());
    }

    public function test_registration_rejects_unknown_roles(): void
    {
        $this->post('/register', $this->registrationData('admin@example.com') + ['registeras' => 'ADM'])
            ->assertSessionHasErrors('registeras');

        $this->assertFalse(User::where('email', 'admin@example.com')->exists());
    }

    public function test_deleting_a_category_keeps_provider_profiles(): void
    {
        $provider = User::factory()->create(['utype' => 'SVP']);
        $profile = ServiceProvider::create(['user_id' => $provider->id]);
        $profile->service_category_id = $this->category->id;
        $profile->save();

        $this->category->delete();

        $this->assertNull($profile->fresh()->service_category_id);
    }

    /**
     * @return array<string, string>
     */
    private function registrationData(string $email): array
    {
        return [
            'name' => 'Usuario Prueba',
            'email' => $email,
            'phone' => '0991234567',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => 'on',
        ];
    }
}
