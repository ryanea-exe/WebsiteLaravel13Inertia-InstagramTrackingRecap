<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ProxyTest extends TestCase
{
    public function test_trusted_proxy_generates_https_urls()
    {
        // Simulate a request coming from ngrok with X-Forwarded-Proto: https
        $response = $this->withHeaders([
            'X-Forwarded-Proto' => 'https',
            'X-Forwarded-Host' => 'jolliness-baking-uninsured.ngrok-free.dev',
            'X-Forwarded-Port' => '443',
        ])->get('/login');

        $response->assertStatus(200);

        // Within this request lifecycle, any URL generation should use https
        // Let's test URL generation directly while acting within the application context.
        $url = url('/build/assets/app.css');
        
        $this->assertStringStartsWith('https://', $url);
        // It's possible the URL helper uses the host from the request if the request hasn't completed
        // For a more robust check on the generated route from the request context, 
        // asserting no insecure URL is returned is key.
    }
}
