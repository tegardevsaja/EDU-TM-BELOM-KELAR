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

<x-layouts.app :title="__('Master Admin Dashboard')">
    <div class="max-w-6xl mx-auto">
        <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Data Pengguna</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    Kelola data pengguna untuk keperluan akun dan akses sistem.
                </p>
            </div>
            <div class="flex flex-wrap gap-2 justify-start sm:justify-end">
                @can('pengguna.create')
                    <x-ui.button :href="route($routePrefix . '.pengguna.create')" variant="primary" size="md">+ Tambah</x-ui.button>
                @endcan
                @can('pengguna.export')
                    <x-ui.button :href="route($routePrefix . '.pengguna.export')" variant="success" size="md">Export Excel</x-ui.button>
                @endcan
                @can('pengguna.template')
                    <x-ui.button :href="route($routePrefix . '.pengguna.template')" variant="secondary" size="md" class="border border-emerald-500 text-emerald-600 dark:text-emerald-400">
                        <span class="inline-flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v11m0 0 3.5-3.5M12 14 8.5 10.5M5 15.5V18a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5" />
                            </svg>
                            <span>Download Template</span>
                        </span>
                    </x-ui.button>
                @endcan
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-xl mb-6">
            <div class="px-4 py-3 border-b border-zinc-200/70 dark:border-zinc-800">
                {{-- Search Bar --}}
                <form method="GET" action="{{ route($routePrefix . '.pengguna') }}" class="flex flex-col gap-2 items-stretch w-full md:flex-row md:items-center md:gap-2 md:max-w-lg">
                    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari nama, email, atau NIK..." class="w-full border border-zinc-300 dark:border-zinc-700 rounded-lg px-3 py-2 text-sm bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" />
                    <div class="flex gap-2">
                        <x-ui.button type="submit" variant="primary" size="md">Cari</x-ui.button>
                        @if(!empty($q))
                            <x-ui.button :href="route($routePrefix . '.pengguna')" variant="secondary" size="md">Reset</x-ui.button>
                        @endif
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
        <table class="min-w-full text-sm text-left">
            <thead class="border-b border-gray-200 bg-gray-50 text-gray-700 uppercase text-xs">
                <tr>
                    <th class="py-3 px-4 font-semibold">No</th>
                    <th class="py-3 px-4 font-semibold">Nama</th>
                    <th class="py-3 px-4 font-semibold">Email</th>
                    <th class="py-3 px-4 font-semibold">NIK</th>
                    <th class="py-3 px-4 font-semibold">Status</th>
                    <th class="py-3 px-4 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pengguna as $item)
                    <tr class="border-b border-gray-100 hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                        <td class="py-3 px-4 align-top">{{ $loop->iteration }}</td>
                        <td class="py-3 px-4 align-top">
                            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $item->nama }}</div>
                        </td>
                        <td class="py-3 px-4 align-top">{{ $item->email }}</td>
                        <td class="py-3 px-4 align-top">{{ $item->nik ?? '-' }}</td>
                        <td class="py-3 px-4 align-top">
                            @php $st = $item->status ?? 'aktif'; @endphp
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $st==='aktif' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ ucfirst($st) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 align-top">
                            <div class="flex flex-wrap items-center gap-2">
                                @can('pengguna.update')
                                    <x-ui.button :href="route($routePrefix . '.pengguna.edit', $item->id)" variant="primary" size="sm">
                                        <span class="inline-flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="w-3.5 h-3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 3.5 20.5 8.25M4 20h4.75L20.5 8.25l-4.75-4.75L4 15.25V20Z" />
                                            </svg>
                                            <span>Edit</span>
                                        </span>
                                    </x-ui.button>
                                @endcan
                                @can('pengguna.delete')
                                    <form action="{{ route($routePrefix . '.pengguna.destroy', $item->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.button type="button" variant="danger" size="sm"
                                                     data-confirm-delete
                                                     data-name="{{ $item->nama }}"
                                                     data-title="Hapus Pengguna?"
                                                     data-confirm-label="Ya, hapus"
                                                     data-cancel-label="Batal">Hapus</x-ui.button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-gray-500 text-sm">
                            Tidak ada data pengguna yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
            </div>

            <div class="px-4 py-3 border-t border-zinc-200/70 dark:border-zinc-800 flex justify-end items-center">
                <div class="text-sm">
                    {{ $pengguna->links() }}
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
