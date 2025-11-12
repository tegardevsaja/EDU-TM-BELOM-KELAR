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
    <form method="GET" action="{{ route($routePrefix . '.pengguna') }}" class="mb-3 flex gap-2 items-center">
        <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari nama, email, atau NIK..." class="border rounded-lg px-3 py-2 w-full max-w-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
        <x-ui.button type="submit" variant="primary" size="md">Cari</x-ui.button>
        @if(!empty($q))
            <x-ui.button :href="route($routePrefix . '.pengguna')" variant="secondary" size="md">Reset</x-ui.button>
        @endif
    </form>

    <div class="flex justify-end mb-4 gap-2">
        @can('pengguna.create')
        <x-ui.button :href="route($routePrefix . '.pengguna.create')" variant="primary" size="md">+ Tambah</x-ui.button>
        @endcan
        
        @can('pengguna.export')
        <x-ui.button :href="route($routePrefix . '.pengguna.export')" variant="success" size="md">Export Excel</x-ui.button>
        @endcan
        
        @can('pengguna.template')
        <x-ui.button :href="route($routePrefix . '.pengguna.template')" variant="secondary" size="md">Download Template Excel</x-ui.button>
        @endcan
    </div>

    @can('pengguna.import')
    <form action="{{ route($routePrefix . '.pengguna.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2 mb-4">
    @csrf
    <input type="file" name="file" accept=".xlsx, .xls" required class="border border-gray-300 rounded-lg p-2 text-sm focus:ring-blue-500 focus:border-blue-500">
    <x-ui.button type="submit" variant="success" size="md">Import Excel</x-ui.button>
</form>
    @endcan


    <div class="bg-white shadow-sm rounded-xl overflow-hidden">
        <table class="min-w-full text-sm text-left">
            <thead class="border-b border-gray-200 bg-gray-50 text-gray-700 uppercase text-xs">
                <tr>
                    <th class="py-3 px-4 font-semibold">No</th>
                    <th class="py-3 px-4 font-semibold">Nama</th>
                    <th class="py-3 px-4 font-semibold">Email</th>
                    <th class="py-3 px-4 font-semibold">NIK</th>
                    <th class="py-3 px-4 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pengguna as $item)
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                        <td class="py-3 px-4">{{ $loop->iteration }}</td>
                        <td class="py-3 px-4">{{ $item->nama }}</td>
                        <td class="py-3 px-4">{{ $item->email }}</td>
                        <td class="py-3 px-4">{{ $item->nik }}</td>
                        <td class="py-3 px-4 flex items-center gap-3">
                            @can('pengguna.update')
                            <x-ui.button :href="route($routePrefix . '.pengguna.edit', $item->id)" variant="secondary" size="sm">Edit</x-ui.button>
                            @endcan
                            
                            @can('pengguna.delete')
                            <form action="{{ route($routePrefix . '.pengguna.destroy', $item->id) }}" method="POST"
                                onsubmit="return confirm('Yakin ingin menghapus data ini?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <x-ui.button type="submit" variant="danger" size="sm">Hapus</x-ui.button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-gray-500">
                            Tidak ada data pengguna.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
        {{ $pengguna->links() }}
    </div>
    </div>
</x-layouts.app>
