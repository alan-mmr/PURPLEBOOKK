<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     * Proteksi akses berdasarkan role user yang sedang login.
     *
     * Cara pakai di route:
     *   Route::middleware('role:admin')->...
     *   Route::middleware('role:vendor')->...
     *   Route::middleware('role:admin,vendor')->... (multi-role)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Pastikan user sudah login
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $userRole = auth()->user()->role;

        // Cek apakah role user ada di daftar yang diizinkan
        if (!in_array($userRole, $roles)) {
            abort(403, 'Akses ditolak. Role kamu tidak memiliki izin untuk halaman ini.');
        }

        return $next($request);
    }
}
