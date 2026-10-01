<?php

namespace App\Services;

use App\Models\InstagramAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class InstagramOAuthService
{
    /**
     * Get the Authorization URL.
     */
    public function getAuthorizationUrl(string $state): string
    {
        $baseUrl = 'https://www.instagram.com/oauth/authorize';
        
        $params = [
            'client_id' => config('services.meta.app_id'),
            'redirect_uri' => config('services.meta.redirect_uri'),
            'scope' => 'instagram_business_basic,instagram_business_manage_comments,instagram_business_manage_insights',
            'response_type' => 'code',
            'state' => $state,
        ];

        return $baseUrl . '?' . http_build_query($params);
    }

    /**
     * Exchange the authorization code for a short-lived access token.
     */
    public function getShortLivedToken(string $code): ?string
    {
        $response = Http::asForm()->post('https://api.instagram.com/oauth/access_token', [
            'client_id' => config('services.meta.app_id'),
            'client_secret' => config('services.meta.app_secret'),
            'grant_type' => 'authorization_code',
            'redirect_uri' => config('services.meta.redirect_uri'),
            'code' => $code,
        ]);

        if ($response->failed()) {
            Log::error('Meta short-lived token exchange failed', [
                'status' => $response->status(),
                // DO NOT log full response which might contain secrets if request echoed back, 
                // but usually error messages are safe. Still, keeping it minimal.
                'error' => $response->json('error_message') ?? $response->json('error.message') ?? 'Unknown error',
            ]);
            throw new Exception('Failed to exchange authorization code for short-lived token.');
        }

        return $response->json('access_token');
    }

    /**
     * Exchange short-lived token for long-lived token.
     */
    public function getLongLivedToken(string $shortLivedToken): array
    {
        $response = Http::get('https://graph.instagram.com/access_token', [
            'grant_type' => 'ig_exchange_token',
            'client_secret' => config('services.meta.app_secret'),
            'access_token' => $shortLivedToken,
        ]);

        if ($response->failed()) {
            Log::error('Meta long-lived token exchange failed', [
                'status' => $response->status(),
                'error' => $response->json('error.message') ?? 'Unknown error',
            ]);
            throw new Exception('Failed to exchange for long-lived token.');
        }

        return [
            'access_token' => $response->json('access_token'),
            'expires_in' => $response->json('expires_in'),
        ];
    }

    /**
     * Fetch user profile from /me
     */
    public function getAccountProfile(string $accessToken): array
    {
        $version = config('services.meta.graph_api_version', 'v26.0');
        $response = Http::get("https://graph.instagram.com/{$version}/me", [
            'fields' => 'id,username,name,account_type',
            'access_token' => $accessToken,
        ]);

        if ($response->failed()) {
            Log::error('Meta account discovery failed', [
                'status' => $response->status(),
                'error' => $response->json('error.message') ?? 'Unknown error',
            ]);
            throw new Exception('Failed to fetch Instagram account profile.');
        }

        return $response->json();
    }

    /**
     * Process full OAuth callback and upsert account.
     */
    public function processCallbackAndUpsert(string $code): InstagramAccount
    {
        $shortLivedToken = $this->getShortLivedToken($code);
        $longLivedData = $this->getLongLivedToken($shortLivedToken);
        $profile = $this->getAccountProfile($longLivedData['access_token']);

        // Upsert logic
        return InstagramAccount::updateOrCreate(
            ['instagram_user_id' => (string) $profile['id']],
            [
                'facebook_page_id' => null, // Explicitly null for Instagram Login flow
                'username' => $profile['username'],
                'name' => $profile['name'] ?? null,
                'account_type' => $profile['account_type'] ?? null,
                'connection_status' => 'CONNECTED',
                'access_token' => $longLivedData['access_token'],
                'token_expires_at' => isset($longLivedData['expires_in']) ? now()->addSeconds($longLivedData['expires_in']) : null,
                'last_synced_at' => now(),
            ]
        );
    }
}
