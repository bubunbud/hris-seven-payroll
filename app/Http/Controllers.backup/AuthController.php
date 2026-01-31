<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

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
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
