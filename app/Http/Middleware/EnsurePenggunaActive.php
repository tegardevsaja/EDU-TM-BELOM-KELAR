<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePenggunaActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $pengguna = method_exists($user, 'pengguna') ? $user->pengguna : null;
            // Jika tidak punya relasi Pengguna atau status bukan "aktif", paksa logout
            if (! $pengguna || ($pengguna->status ?? 'nonaktif') !== 'aktif') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->withErrors([
                    'email' => 'Akun dinonaktifkan oleh admin. Hubungi administrator.',
                ]);
            }
        }
        return $next($request);
    }
}
