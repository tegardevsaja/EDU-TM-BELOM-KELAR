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
   <form method="GET" action="{{ route($routePrefix . '.jurusan') }}" class="mb-3 flex gap-2 items-center">
        <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari nama jurusan..." class="border rounded-lg px-3 py-2 w-full max-w-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
        <x-ui.button type="submit" variant="primary" size="md">Cari</x-ui.button>
        @if(!empty($q))
            <x-ui.button :href="route($routePrefix . '.jurusan')" variant="secondary" size="md">Reset</x-ui.button>
        @endif
   </form>
   <div class="flex justify-end">
    @can('jurusan.create')
    <div>
        <x-ui.button :href="route($routePrefix . '.jurusan.create')" variant="primary" size="md">Tambah Jurusan</x-ui.button>
    </div>
    @endcan
   </div>
   <div>
    <table class="min-w-full text-sm text-left">
        <thead class="border-b border-gray-200 bg-gray-50 text-gray-700 uppercase text-xs">
            <tr>
                <th class="py-3 px-4 font-semibold">No</th>
                <th class="py-3 px-4 font-semibold">Nama Jurusan</th>
                <th class="py-3 px-4 font-semibold">Aksi</th>
            </tr>
        </thead>
        <tbody>
              @forelse ($jurusan as $item)
            <tr  class="border-b border-gray-100 hover:bg-gray-50 transition">
                <td class="py-3 px-4">{{ $loop->iteration }}</td>
                <td class="py-3 px-4">{{ $item->nama_jurusan}}</td>
                <td class="py-3 px-4 flex items-center gap-3">
                            @can('jurusan.update')
                            <x-ui.button :href="route($routePrefix . '.jurusan.edit', $item->id)" variant="secondary" size="sm">Edit</x-ui.button>
                            @endcan
                            
                            @can('jurusan.delete')
                            <form action="{{ route($routePrefix . '.jurusan.destroy', $item->id) }}" method="POST"
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
        {{ $jurusan->links() }}
    </div>
   </div>
</x-layouts.app>
