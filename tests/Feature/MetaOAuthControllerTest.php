<?php

namespace Tests\Feature;

use App\Models\InstagramAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MetaOAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.meta.app_id' => 'test_app_id']);
        config(['services.meta.app_secret' => 'test_app_secret']);
        config(['services.meta.redirect_uri' => 'https://test.com/oauth/meta/callback']);
    }

    public function test_admin_can_access_connect_route_and_redirects_to_meta()
    {
        $admin = User::factory()->create(['role' => 'Administrator']);

        $response = $this->actingAs($admin)->get(route('meta.connect'));

        $response->assertRedirectContains('https://www.instagram.com/oauth/authorize');
        $response->assertRedirectContains('client_id=test_app_id');
        
        $this->assertNotNull(session('meta_oauth_state'));
    }

    public function test_staff_gets_403_on_connect_route()
    {
        $staff = User::factory()->create(['role' => 'Staff']);

        $response = $this->actingAs($staff)->get(route('meta.connect'));

        $response->assertStatus(403);
    }

    public function test_callback_rejects_missing_state()
    {
        $admin = User::factory()->create(['role' => 'Administrator']);

        $response = $this->actingAs($admin)->get(route('meta.callback', ['code' => '123']));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error', 'Invalid OAuth state. Silakan coba lagi.');
    }

    public function test_callback_rejects_invalid_state()
    {
        $admin = User::factory()->create(['role' => 'Administrator']);

        $response = $this->actingAs($admin)
            ->withSession(['meta_oauth_state' => 'valid_state'])
            ->get(route('meta.callback', ['code' => '123', 'state' => 'invalid_state']));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error', 'Invalid OAuth state. Silakan coba lagi.');
    }

    public function test_callback_handles_user_cancellation()
    {
        $admin = User::factory()->create(['role' => 'Administrator']);

        $response = $this->actingAs($admin)
            ->get(route('meta.callback', [
                'error' => 'access_denied',
                'error_reason' => 'user_denied',
                'error_description' => 'The user denied your request.',
            ]));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error', 'Koneksi Instagram dibatalkan oleh pengguna: The user denied your request.');
    }

    public function test_callback_rejects_missing_code()
    {
        $admin = User::factory()->create(['role' => 'Administrator']);

        $response = $this->actingAs($admin)
            ->withSession(['meta_oauth_state' => 'valid_state'])
            ->get(route('meta.callback', ['state' => 'valid_state']));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error', 'Missing authorization code dari Meta.');
    }

    public function test_successful_oauth_flow_upserts_account()
    {
        Http::fake([
            'api.instagram.com/oauth/access_token' => Http::response([
                'access_token' => 'short_lived_token',
                'user_id' => 12345,
            ], 200),
            
            'graph.instagram.com/access_token*' => Http::response([
                'access_token' => 'long_lived_token_secret',
                'token_type' => 'bearer',
                'expires_in' => 5184000,
            ], 200),

            'graph.instagram.com/v26.0/me*' => Http::response([
                'id' => 'ig_user_123',
                'username' => 'test_ig_account',
                'name' => 'Test Account',
                'account_type' => 'BUSINESS'
            ], 200),
        ]);

        $admin = User::factory()->create(['role' => 'Administrator']);

        $response = $this->actingAs($admin)
            ->withSession(['meta_oauth_state' => 'valid_state'])
            ->get(route('meta.callback', [
                'state' => 'valid_state',
                'code' => 'auth_code_123#_', // with appended #_
            ]));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('success', 'Instagram @test_ig_account berhasil terhubung.');

        // Assert DB
        $this->assertDatabaseHas('instagram_accounts', [
            'instagram_user_id' => 'ig_user_123',
            'username' => 'test_ig_account',
            'name' => 'Test Account',
            'account_type' => 'BUSINESS',
            'connection_status' => 'CONNECTED',
            'facebook_page_id' => null,
        ]);

        // Assert token encryption
        $account = InstagramAccount::where('instagram_user_id', 'ig_user_123')->first();
        $this->assertEquals('long_lived_token_secret', $account->access_token);
        
        $rawDbValue = \DB::table('instagram_accounts')->where('id', $account->id)->value('access_token');
        $this->assertNotEquals('long_lived_token_secret', $rawDbValue);
    }
    
    public function test_upsert_updates_existing_account()
    {
        InstagramAccount::factory()->create([
            'instagram_user_id' => 'ig_user_123',
            'username' => 'old_username',
            'access_token' => 'old_token',
        ]);

        Http::fake([
            'api.instagram.com/oauth/access_token' => Http::response(['access_token' => 'short_lived_token'], 200),
            'graph.instagram.com/access_token*' => Http::response(['access_token' => 'new_long_lived_token'], 200),
            'graph.instagram.com/v26.0/me*' => Http::response([
                'id' => 'ig_user_123',
                'username' => 'new_username',
                'account_type' => 'BUSINESS',
            ], 200),
        ]);

        $admin = User::factory()->create(['role' => 'Administrator']);

        $response = $this->actingAs($admin)
            ->withSession(['meta_oauth_state' => 'valid_state'])
            ->get(route('meta.callback', ['state' => 'valid_state', 'code' => 'auth_code_123']));

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success', 'Instagram @new_username berhasil terhubung.');

        $this->assertDatabaseHas('instagram_accounts', [
            'instagram_user_id' => 'ig_user_123',
            'username' => 'new_username',
        ]);
        
        $account = InstagramAccount::where('instagram_user_id', 'ig_user_123')->first();
        $this->assertEquals('new_long_lived_token', $account->access_token);
        $this->assertEquals(1, InstagramAccount::count());
    }

    public function test_meta_api_failure_is_handled_gracefully()
    {
        Http::fake([
            'api.instagram.com/oauth/access_token' => Http::response([
                'error_message' => 'Invalid code'
            ], 400),
        ]);

        $admin = User::factory()->create(['role' => 'Administrator']);

        $response = $this->actingAs($admin)
            ->withSession(['meta_oauth_state' => 'valid_state'])
            ->get(route('meta.callback', [
                'state' => 'valid_state',
                'code' => 'auth_code_123',
            ]));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error', 'Gagal menghubungkan akun Instagram. Silakan cek log untuk detail.');
    }
}
