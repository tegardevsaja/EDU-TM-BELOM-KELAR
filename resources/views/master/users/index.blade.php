@php
    $routePrefix = 'master'; // default
    if (auth()->check() && method_exists(auth()->user(), 'hasRole')) {
        if (auth()->user()->hasRole('master_admin')) {
            $routePrefix = 'master';
        } elseif (auth()->user()->hasRole('admin')) {
            $routePrefix = 'admin';
        } elseif (auth()->user()->hasRole('guru')) {
            $routePrefix = 'guru';
        }
    }
@endphp

<x-layouts.app :title="__('Akun Pengguna')">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-semibold">Daftar Akun Pengguna</h1>
            @can('users.create')
                <a href="{{ route($routePrefix . '.users.create') }}" class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M13 11V6a1 1 0 10-2 0v5H6a1 1 0 100 2h5v5a1 1 0 102 0v-5h5a1 1 0 100-2h-5z"/></svg>
                    Tambah Akun
                </a>
            @endcan
        </div>

        <div class="overflow-hidden rounded-lg border bg-white dark:bg-zinc-800">
            @if ($users->isEmpty())
                <div class="p-10 text-center text-gray-500">
                    Belum ada user yang dibuat.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-700 dark:bg-zinc-700">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">Nama</th>
                                <th class="px-4 py-3 text-left font-semibold">Email</th>
                                <th class="px-4 py-3 text-left font-semibold">Role</th>
                                <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr class="border-t">
                                    <td class="px-4 py-3">{{ $user->name }}</td>
                                    <td class="px-4 py-3">{{ $user->email }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded bg-zinc-100 px-2 py-0.5 text-xs dark:bg-zinc-700">{{ $user->role }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="inline-flex gap-2">
                                            @can('users.update')
                                                <a href="{{ route($routePrefix . '.users.edit', $user->id) }}" class="rounded bg-yellow-500 px-3 py-1.5 text-xs text-white hover:bg-yellow-600">Edit</a>
                                            @endcan
                                            @can('users.delete')
                                                <form action="{{ route($routePrefix . '.users.destroy', $user->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="rounded bg-red-600 px-3 py-1.5 text-xs text-white hover:bg-red-700"
                                                            data-confirm-delete
                                                            data-name="{{ $user->name }}"
                                                            data-title="Hapus Akun?"
                                                            data-confirm-label="Ya, hapus"
                                                            data-cancel-label="Batal">Hapus</button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t p-4">{{ $users->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts.app>
