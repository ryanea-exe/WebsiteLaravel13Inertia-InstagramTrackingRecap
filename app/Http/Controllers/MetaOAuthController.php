<?php

namespace App\Http\Controllers;

use App\Services\InstagramOAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class MetaOAuthController extends Controller
{
    public function __construct(private InstagramOAuthService $oauthService)
    {
    }

    public function connect(Request $request)
    {
        // 1. Generate random state
        $state = Str::random(40);
        
        // 2. Store in session
        $request->session()->put('meta_oauth_state', $state);
        
        // 3. Redirect to Meta authorization URL
        $url = $this->oauthService->getAuthorizationUrl($state);
        
        return redirect()->away($url);
    }

    public function callback(Request $request)
    {
        // 1. User denied authorization
        if ($request->has('error')) {
            Log::warning('Meta OAuth denied by user', [
                'error' => $request->query('error'),
                'error_reason' => $request->query('error_reason'),
                'error_description' => $request->query('error_description'),
            ]);
            return redirect()->route('home')->with('error', 'Koneksi Instagram dibatalkan oleh pengguna: ' . $request->query('error_description', 'Akses ditolak.'));
        }

        // 2. Validate state
        $sessionState = $request->session()->pull('meta_oauth_state');
        $requestState = $request->query('state');

        if (!$sessionState || $sessionState !== $requestState) {
            Log::warning('Meta OAuth invalid state', ['session' => $sessionState, 'request' => $requestState]);
            return redirect()->route('home')->with('error', 'Invalid OAuth state. Silakan coba lagi.');
        }

        // 3. Ensure code exists
        $code = $request->query('code');
        if (!$code) {
            Log::error('Meta OAuth missing code');
            return redirect()->route('home')->with('error', 'Missing authorization code dari Meta.');
        }

        // 4. Strip appended #_ if present
        if (str_ends_with($code, '#_')) {
            $code = substr($code, 0, -2);
        }

        // 5. Process token exchange and upsert
        try {
            $account = $this->oauthService->processCallbackAndUpsert($code);

            return redirect()->route('home')->with('success', "Instagram @{$account->username} berhasil terhubung.");
        } catch (Exception $e) {
            Log::error('Meta OAuth processing failed', ['message' => $e->getMessage()]);
            return redirect()->route('home')->with('error', 'Gagal menghubungkan akun Instagram. Silakan cek log untuk detail.');
        }
    }
}
