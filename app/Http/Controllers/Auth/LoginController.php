<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse; // Import RedirectResponse
use Illuminate\View\View; // Import View


class LoginController extends Controller
{
    /**
     * Display the login view.
     */
    public function showLoginForm(): View // Type hint View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(Request $request): RedirectResponse // Type hint RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Redirect ke dashboard setelah login berhasil
            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'Kredensial yang diberikan tidak cocok dengan catatan kami.',
        ])->onlyInput('email');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse // Type hint RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('status', 'Anda telah berhasil logout.'); // Redirect ke login dengan pesan
    }

    /**
     * Create a new controller instance.
     * Menerapkan middleware guest ke semua method kecuali logout.
     * Pengguna yang sudah login tidak bisa mengakses halaman login.
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}