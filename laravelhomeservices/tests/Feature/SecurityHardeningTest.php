<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Livewire\ContactComponent;
use App\Http\Livewire\Service\ServiceDetailsComponent;
use App\Models\Contact;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_responses_carry_security_headers(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeaderMissing('Strict-Transport-Security');

        $this->get('https://localhost/')->assertHeader('Strict-Transport-Security');
    }

    public function test_contact_form_is_rate_limited(): void
    {
        RateLimiter::clear('livewire:contact:127.0.0.1');

        for ($i = 0; $i < 6; $i++) {
            Livewire::test(ContactComponent::class)
                ->set('name', 'Ana')
                ->set('email', 'ana@example.com')
                ->set('phone', '0991234567')
                ->set('message', 'Hola')
                ->call('sendMessage');
        }

        $this->assertSame(5, Contact::count());
    }

    public function test_service_requests_are_rate_limited_per_user(): void
    {
        $category = new ServiceCategory();
        $category->forceFill(['name' => 'Gasfitería', 'slug' => 'gasfiteria', 'image' => 'x.png'])->save();
        $owner = User::factory()->create(['utype' => 'ADM']);
        $service = new Service();
        $service->forceFill([
            'user_id' => $owner->id, 'name' => 'Destape', 'slug' => 'destape', 'tagline' => 't',
            'service_category_id' => $category->id, 'price' => 10, 'image' => 'i.png', 'thumbnail' => 't.png',
        ])->save();
        $customer = User::factory()->create(['phone' => '0991234567']);

        for ($i = 0; $i < 6; $i++) {
            $component = Livewire::actingAs($customer)
                ->test(ServiceDetailsComponent::class, ['service_slug' => 'destape'])
                ->set('zone', 'malacatos')
                ->call('submitRequest');
        }

        $component->assertHasErrors('message');
        $this->assertSame(5, ServiceRequest::count());
    }

    public function test_password_reset_does_not_reveal_unknown_emails(): void
    {
        User::factory()->create(['email' => 'existe@example.com']);

        $known = $this->from('/forgot-password')->post('/forgot-password', ['email' => 'existe@example.com']);
        $unknown = $this->from('/forgot-password')->post('/forgot-password', ['email' => 'noexiste@example.com']);

        $known->assertSessionHas('status')->assertSessionHasNoErrors();
        $unknown->assertSessionHas('status', $known->getSession()->get('status'))->assertSessionHasNoErrors();
    }

    public function test_session_uses_json_serialization(): void
    {
        $this->assertSame('json', config('session.serialization'));
        $this->assertContains(Service::class, config('cache.serializable_classes'));
    }
}
