<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{
    public function create(): View
    {
        // Ambil pengguna yang belum punya akun dan AKTIF saja
        $penggunas = Pengguna::doesntHave('user')->where('status', 'aktif')->get();

        // Daftar role sesuai enum
        $roles = ['master_admin', 'admin', 'guru'];

        return view('master.users.create', compact('penggunas', 'roles'));
    }

    public function edit($id)
{
    $user = User::findOrFail($id);
    $roles = ['master_admin', 'admin', 'guru'];
    return view('master.users.edit', compact('user', 'roles'));
}

        public function update(Request $request, $id)
        {
            $user = User::findOrFail($id);

            $validated = $request->validate([
                'name'  => 'required|string|max:100',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'role'  => 'required|string',
                'password' => 'nullable|string|min:8|confirmed',
            ]);

            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
            ];

            // Update password if provided
            if (!empty($validated['password'])) {
                $updateData['password'] = bcrypt($validated['password']);
            }

            $user->update($updateData);

            // Sync Spatie role with enum/string role column so @role/@can works
            if (method_exists($user, 'syncRoles')) {
                $user->syncRoles([$validated['role']]);
            }

            // Clear Spatie permission cache
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

            return redirect()->route('master.users')->with('success', 'Data berhasil diperbarui!');
        }

        public function destroy($id)
        {
            $user = User::findOrFail($id);
            $user->delete();

            return redirect()->route('master.users')->with('success', 'Data berhasil dihapus!');
        }


    public function store(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'pengguna_id' => 'required|exists:penggunas,id',
                'role' => 'required|in:master_admin,admin,guru',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $pengguna = Pengguna::findOrFail($validated['pengguna_id']);
            if (($pengguna->status ?? 'aktif') !== 'aktif') {
                return back()->withErrors(['pengguna_id' => 'Pengguna nonaktif tidak dapat dibuatkan akun.'])->withInput();
            }

            $user = User::create([
                'name' => $pengguna->nama,
                'email' => $pengguna->email,
                'pengguna_id' => $pengguna->id,
                'role' => $validated['role'],
                'password' => bcrypt($validated['password']),
            ]);

            return redirect()->route('master.users')->with('success', 'Akun berhasil dibuat!');
            
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }

    public function index(): \Illuminate\View\View
    {
        $users = User::with('pengguna')->paginate(10);

        return view('master.users.index', compact('users'));
    }

}
