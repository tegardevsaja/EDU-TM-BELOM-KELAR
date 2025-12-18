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
    <p class="text-2xl font-bold">Data Jurusan</p>
  <p class="text-sm text-gray-600 mb-8">daftar data jurusan</p>

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
                <td class="py-3 px-4 align-top">
                    <div class="flex flex-wrap items-center gap-2">
                        @can('jurusan.update')
                            <a href="{{ route($routePrefix . '.jurusan.edit', $item->id) }}"
                               class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-2.5 py-1 text-xs font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="w-3.5 h-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 3.5 20.5 8.25M4 20h4.75L20.5 8.25l-4.75-4.75L4 15.25V20Z" />
                                </svg>
                                <span>Edit</span>
                            </a>
                        @endcan

                        @can('jurusan.delete')
                            <form action="{{ route($routePrefix . '.jurusan.destroy', $item->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="button"
                                    class="inline-flex items-center gap-1 rounded-lg bg-red-600 px-2.5 py-1 text-xs font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                                    data-confirm-delete
                                    data-name="{{ $item->nama_jurusan }}"
                                    data-title="Hapus Jurusan?"
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
