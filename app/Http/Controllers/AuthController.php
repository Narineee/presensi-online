<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Menampilkan formulir login.
     * Jika pengguna telah terotentikasi, alihkan langsung ke dashboard masing-masing role.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user()->role);
        }

        return view('auth.login');
    }

    /**
     * Memproses percobaan login pengguna.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return $this->redirectBasedOnRole(Auth::user()->role);
    }

    /**
     * Menangani proses logout dan membersihkan sesi.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('status', 'Anda telah berhasil keluar dari sistem.');
    }

    /**
     * Pengalihan rute dashboard terpusat sesuai hak akses role.
     */
    private function redirectBasedOnRole(string $role): RedirectResponse
    {
        return match ($role) {
            'admin' => redirect()->route('admin.dashboard'),
            'pembimbing' => redirect()->route('pembimbing.dashboard'),
            'magang' => redirect()->route('magang.dashboard'),
            default => redirect('/'),
        };
    }
}
