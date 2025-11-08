<?php

use Illuminate\Support\Facades\Auth;

if (!function_exists('role_view')) {

    function role_view(string $path): string
    {
        $roleEnum = Auth::check() ? Auth::user()->role : null;

        // Ambil value dari enum kalau memang enum, atau fallback ke string
        $role = $roleEnum instanceof \BackedEnum ? strtolower($roleEnum->value) : strtolower($roleEnum ?? 'guest');

        $prefix = match ($role) {
            'master_admin', 'master' => 'master',
            'admin' => 'admin',
            'guru' => 'guru',
            default => 'guru',
        };

        $roleViewPath = "{$prefix}.{$path}";

        if (view()->exists($roleViewPath)) {
            return $roleViewPath;
        }

        if (view()->exists($path)) {
            return $path;
        }

        return $roleViewPath;
    }
}
