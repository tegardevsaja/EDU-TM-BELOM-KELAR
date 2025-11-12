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
                    <td class="py-3 px-4 flex items-center gap-3">
                        @can('kelas.update')
                        <a href="{{ route($routePrefix . '.kelas.edit', $kls->id) }}"
                           class="text-blue-600 hover:underline">Edit</a>
                        @endcan
                        
                        @can('kelas.delete')
                        <form action="{{ route($routePrefix . '.kelas.destroy', $kls->id) }}"
                              method="POST"
                              onsubmit="return confirm('Yakin ingin menghapus data ini?')"
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="text-red-600 hover:underline">
                                Hapus
                            </button>
                        </form>
                        @endcan
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
