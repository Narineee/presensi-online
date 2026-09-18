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

        return $next($request);
    }
}
