<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen()
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        // LoginResponse sends each role to its own dashboard; factory users are customers.
        $response->assertRedirect(route('customer.dashboard'));
    }

    public function test_admins_and_providers_are_redirected_to_their_dashboards()
    {
        $admin = User::factory()->create(['utype' => 'ADM']);
        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->post('/logout');

        $provider = User::factory()->create(['utype' => 'SVP']);
        $this->post('/login', ['email' => $provider->email, 'password' => 'password'])
            ->assertRedirect(route('sprovider.dashboard'));
    }

    public function test_users_can_not_authenticate_with_invalid_password()
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }
}
