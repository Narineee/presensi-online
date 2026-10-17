<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(
        Request $request,
        Closure $next,
        ...$roles
    ): Response {
        // Cek apakah pengguna sudah login
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        // Ambil data pengguna yang sedang login
        $user = Auth::user();

        // Cek role pengguna
        if (! $user || ! in_array($user->role, $roles)) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        // Cek keaktifan akun pengguna
        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'username' => 'Akun Anda sedang berstatus non-aktif. Silakan hubungi Administrator.',
            ]);
        }

        // Sinkronisasi status magang menjadi 'selesai' jika periode telah berakhir
        if ($user->role === 'magang' && $user->magang) {
            $user->magang->syncStatusKeaktifan();
        }

        return $next($request);
    }
}
