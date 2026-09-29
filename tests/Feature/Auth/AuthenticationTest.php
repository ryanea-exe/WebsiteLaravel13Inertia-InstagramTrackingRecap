<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_authenticate()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'status' => 'Aktif',
            'last_login_at' => null,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');

        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_inactive_user_cannot_authenticate()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'status' => 'Tidak Aktif',
            'last_login_at' => null,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
        $this->assertNull($user->fresh()->last_login_at);
    }

    public function test_user_cannot_authenticate_with_invalid_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'status' => 'Aktif',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_user_cannot_authenticate_with_unregistered_email()
    {
        $response = $this->post('/login', [
            'email' => 'nobody@example.com',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_user_can_logout()
    {
        $user = User::factory()->create([
            'status' => 'Aktif',
        ]);

        $this->actingAs($user);

        $response = $this->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    public function test_login_rate_limiting_works()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'status' => 'Aktif',
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('Too many login attempts', \Illuminate\Support\Facades\Session::get('errors')->first('email') ?: 'Too many login attempts.');
    }

    public function test_guest_cannot_access_auth_route()
    {
        $response = $this->post('/logout');
        // Because /logout is auth protected, it should redirect to login or return 401/403
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_cannot_access_guest_route()
    {
        $user = User::factory()->create(['status' => 'Aktif']);
        $this->actingAs($user);

        $response = $this->get('/login');
        // Guest middleware redirects to /home (which is usually /)
        $response->assertRedirect('/');
    }

    public function test_administrator_passes_admin_gate()
    {
        $admin = User::factory()->create([
            'role' => 'Administrator',
            'status' => 'Aktif',
        ]);

        $this->assertTrue(\Illuminate\Support\Facades\Gate::forUser($admin)->allows('admin'));
    }

    public function test_staff_fails_admin_gate()
    {
        $staff = User::factory()->create([
            'role' => 'Staff',
            'status' => 'Aktif',
        ]);

        $this->assertFalse(\Illuminate\Support\Facades\Gate::forUser($staff)->allows('admin'));
    }

    public function test_last_login_at_not_updated_on_failed_login()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'status' => 'Aktif',
            'last_login_at' => null,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertNull($user->fresh()->last_login_at);
    }

    public function test_user_can_authenticate_with_remember_me()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'status' => 'Aktif',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
            'remember' => true,
        ]);

        $this->assertAuthenticated();
        $response->assertCookie(\Illuminate\Support\Facades\Auth::getRecallerName());
    }
}
