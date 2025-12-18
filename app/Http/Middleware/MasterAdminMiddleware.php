<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class MasterAdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        if (!$user) {
            abort(403, 'Authentication required.');
        }
        
        // Handle both enum and string role values
        $userRole = $user->role instanceof \BackedEnum ? $user->role->value : $user->role;
        
        // Allow access if user is master_admin
        if ($userRole === 'master_admin') {
            return $next($request);
        }
        
        // Otherwise deny access
        abort(403, 'Access denied. Master admin required. Your role: ' . $userRole);
    }
}
