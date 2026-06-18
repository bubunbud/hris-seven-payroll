<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\LoginHistory;
use App\Models\User;
use App\Models\UserSession;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        // Jika sudah login, redirect ke dashboard sesuai konfigurasi user
        if (Auth::check()) {
            $user = Auth::user();
            return redirect()->route($user->getDashboardRoute());
        }
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 6 karakter',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        // Cek apakah user ada dan aktif
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'Email atau password tidak ditemukan',
            ])->withInput();
        }

        if (!$user->is_active) {
            return back()->withErrors([
                'email' => 'Akun Anda tidak aktif. Silakan hubungi administrator.',
            ])->withInput();
        }

        // Cek password
        if (!Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'Email atau password tidak ditemukan',
            ])->withInput();
        }

        // Login user
        Auth::login($user, $remember);

        // Redirect ke dashboard sesuai konfigurasi user
        $request->session()->regenerate();

        $this->recordLoginSession($request, $user);

        // Gunakan intended untuk menghormati redirect yang diminta sebelumnya
        // Jika tidak ada intended, gunakan dashboard route dari user
        $dashboardRoute = $user->getDashboardRoute();
        return redirect()->intended(route($dashboardRoute));
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        $sessionId = $request->session()->getId();

        if ($user) {
            LoginHistory::create([
                'user_id' => $user->id,
                'event' => 'logout',
                'ip_address' => $request->ip(),
                'user_agent' => $this->truncateUserAgent($request->userAgent()),
                'created_at' => now(),
            ]);
        }

        UserSession::where('session_id', $sessionId)->delete();

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function recordLoginSession(Request $request, User $user): void
    {
        $now = now();

        UserSession::updateOrCreate(
            ['session_id' => $request->session()->getId()],
            [
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $this->truncateUserAgent($request->userAgent()),
                'last_activity_at' => $now,
            ]
        );

        LoginHistory::create([
            'user_id' => $user->id,
            'event' => 'login',
            'ip_address' => $request->ip(),
            'user_agent' => $this->truncateUserAgent($request->userAgent()),
            'created_at' => $now,
        ]);
    }

    private function truncateUserAgent(?string $agent): ?string
    {
        if ($agent === null || $agent === '') {
            return null;
        }

        return mb_substr($agent, 0, 512);
    }
}

