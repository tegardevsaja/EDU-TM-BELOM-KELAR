<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        if ($request->wantsJson()) {
            return new JsonResponse('', 204);
        }

        if (method_exists($user, 'hasRole')) {
            if ($user->hasRole('master_admin')) {
                return redirect()->route('master.permissions.index');
            }
            if ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            }
            if ($user->hasRole('guru')) {
                return redirect()->route('guru.dashboard');
            }
        }

        return redirect()->intended(route('dashboard'));
    }
}
