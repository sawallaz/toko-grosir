<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Cek 1: Apakah user sudah login? (Harusnya sudah karena ada auth, tapi jaga-jaga)
        if (! $request->user()) {
            return redirect()->route('login');
        }

        // Cek 2: Apakah role user ada di dalam daftar role yang diizinkan?
        // Contoh: role:admin,kasir -> $roles = ['admin', 'kasir']
        if (! in_array($request->user()->role, $roles)) {
            // Jika role tidak cocok, lempar error 403 (Forbidden)
            abort(403, 'Akses Ditolak! Anda tidak memiliki izin untuk masuk ke halaman ini.');
        }

        return $next($request);
    }
}