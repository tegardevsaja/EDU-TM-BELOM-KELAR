<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next, ...$allowedRoles): Response
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Ambil nilai role sebagai string (baik dari enum maupun string biasa)
        $userRoleValue = $user->role instanceof \BackedEnum
            ? $user->role->value
            : $user->role;

        // Cek apakah role user ada di daftar yang diizinkan
        if (! in_array($userRoleValue, $allowedRoles, true)) {
            abort(403, 'Akses ditolak');
        }

        return $next($request);
    }
}