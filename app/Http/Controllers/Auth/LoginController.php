<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    /**
     * Tampilkan halaman login.
     */
    public function create()
    {
        return \Inertia\Inertia::render('Auth/Login');
    }

    /**
     * Proses autentikasi login.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        $this->ensureIsNotRateLimited($request);

        // Tambahkan constraint status = 'Aktif'
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
            'status' => 'Aktif',
        ];

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            RateLimiter::clear($this->throttleKey($request));

            $request->session()->regenerate();

            // Update last_login_at
            $user = Auth::user();
            $user->last_login_at = now();
            $user->save();

            // Redirect ke root karena dashboard belum dibuat
            return redirect()->intended('/');
        }

        RateLimiter::hit($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => __('Email atau password salah.'),
        ]);
    }

    /**
     * Proses logout.
     */
    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Pastikan user tidak terkena rate limit.
     */
    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Generate key unik untuk rate limiting.
     */
    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());
    }
}
