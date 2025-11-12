<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionController extends Controller
{
    public function index(): View
    {
        $roles = Role::orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();

        return view('master.permissions.index', compact('roles', 'permissions'));
    }

    public function update(Request $request, string $roleName): RedirectResponse
    {
        $role = Role::where('name', $roleName)->firstOrFail();

        // Master admin always has all permissions; skip editing it
        if ($role->name === 'master_admin') {
            return back()->with('error', 'Master Admin sudah memiliki semua akses.');
        }

        $validated = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ]);

        $perms = $validated['permissions'] ?? [];
        $role->syncPermissions($perms);

        // Clear Spatie permission cache to apply changes immediately
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return back()->with('success', 'Izin berhasil diperbarui.');
    }
}
