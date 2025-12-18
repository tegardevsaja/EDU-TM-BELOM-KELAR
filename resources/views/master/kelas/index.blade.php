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
    <p class="text-2xl font-bold">Data kelas</p>
  <p class="text-sm text-gray-600 mb-8">daftar data kelas</p>

    {{-- Search Bar --}}
    <form method="GET" action="{{ route($routePrefix . '.kelas') }}" class="mb-3 flex gap-2 items-center">
        <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari kelas, jurusan, atau wali kelas..." class="border rounded-lg px-3 py-2 w-full max-w-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
        <x-ui.button type="submit" variant="primary" size="md">Cari</x-ui.button>
        @if(!empty($q))
            <x-ui.button :href="route($routePrefix . '.kelas')" variant="secondary" size="md">Reset</x-ui.button>
        @endif
    </form>
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold">Data Kelas</h2>
        @can('kelas.create')
        <a href="{{ route($routePrefix . '.kelas.create') }}"
           class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
           Tambah Kelas
        </a>
        @endcan
    </div>

    <table class="min-w-full text-sm text-left border">
        <thead class="border-b bg-gray-50 text-gray-700 uppercase text-xs">
            <tr>
                <th class="py-3 px-4 font-semibold">No</th>
                <th class="py-3 px-4 font-semibold">Kelas</th>
                <th class="py-3 px-4 font-semibold">Jurusan</th>
                <th class="py-3 px-4 font-semibold">Wali Kelas</th>
                <th class="py-3 px-4 font-semibold">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($kelas as $index => $kls)
                <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                    <td class="py-3 px-4">
                        {{ $kelas->firstItem() + $index }}
                    </td>
                    <td class="py-3 px-4">
                        {{ $kls->nama_kelas }}
                    </td>
                    <td class="py-3 px-4">
                        {{ $kls->jurusan->nama_jurusan ?? '-' }}
                    </td>
                    <td class="py-3 px-4">
                        {{ $kls->waliKelas->name ?? '-' }}
                    </td>
                    <td class="py-3 px-4 align-top">
                        <div class="flex flex-wrap items-center gap-2">
                            @can('kelas.update')
                                <a href="{{ route($routePrefix . '.kelas.edit', $kls->id) }}"
                                   class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-2.5 py-1 text-xs font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="w-3.5 h-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 3.5 20.5 8.25M4 20h4.75L20.5 8.25l-4.75-4.75L4 15.25V20Z" />
                                    </svg>
                                    <span>Edit</span>
                                </a>
                            @endcan

                            @can('kelas.delete')
                                <form action="{{ route($routePrefix . '.kelas.destroy', $kls->id) }}"
                                      method="POST"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        class="inline-flex items-center gap-1 rounded-lg bg-red-600 px-2.5 py-1 text-xs font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                                        data-confirm-delete
                                        data-name="{{ $kls->nama_kelas }}"
                                        data-title="Hapus Kelas?"
                                        data-confirm-label="Ya, hapus"
                                        data-cancel-label="Batal">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="w-3.5 h-3.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M10 11v6m4-6v6M9 4h6a1 1 0 0 1 1 1v2H8V5a1 1 0 0 1 1-1Zm-2 3h12l-1 13H8L7 7Z" />
                                        </svg>
                                        <span>Hapus</span>
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="py-3 px-4 text-center text-gray-500">
                        Tidak ada data kelas.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $kelas->links() }}
    </div>
</x-layouts.app>
