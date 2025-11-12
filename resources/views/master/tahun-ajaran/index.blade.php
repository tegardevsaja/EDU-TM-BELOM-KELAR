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

<x-layouts.app :title="__('Tahun Ajaran')">
    <div class="p-6 bg-white dark:bg-zinc-800">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Daftar Tahun Ajaran</h2>
            @can('tahunAjaran.create')
            <a href="{{ route($routePrefix . '.tahun-ajaran.create') }}"
               class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                + Tambah Tahun Ajaran
            </a>
            @endcan
        </div>

        @if(session('success'))
            <div class="mb-4 p-2 bg-green-100 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif
        <table class="min-w-full text-sm text-left border">
            <thead class="border-b bg-gray-50 text-gray-700 uppercase text-xs">
                <tr>
                    <th class="py-3 px-4 font-semibold">No</th>
                    <th class="py-3 px-4 font-semibold">Tahun Ajaran</th>
                    <th class="py-3 px-4 font-semibold">Mulai</th>
                    <th class="py-3 px-4 font-semibold">Selesai</th>
                    <th class="py-3 px-4 font-semibold">Aktif</th>
                    <th class="py-3 px-4 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tahunAjaran as $index => $item)
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                        <td class="py-3 px-4">{{ $index + $tahunAjaran->firstItem() }}</td>
                        <td class="py-3 px-4">{{ $item->tahun_ajaran }}</td>
                        <td class="py-3 px-4">{{ $item->tanggal_mulai }}</td>
                        <td class="py-3 px-4">{{ $item->tanggal_selesai }}</td>
                        <td class="py-3 px-4">
                            @if($item->aktif)
                                <span class="text-green-600 font-semibold">Aktif</span>
                            @else
                                <span class="text-gray-500">Tidak</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 flex items-center gap-3">
                            @can('tahunAjaran.update')
                            <a href="{{ route($routePrefix . '.tahun-ajaran.edit', $item->id) }}"
                               class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                                Edit
                            </a>
                            @endcan
                            
                            @can('tahunAjaran.delete')
                            <form action="{{ route($routePrefix . '.tahun-ajaran.destroy', $item->id) }}" method="POST" class="inline-block"
                                  onsubmit="return confirm('Yakin hapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700">
                                    Hapus
                                </button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-4 text-center text-gray-500">Belum ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">
            {{ $tahunAjaran->links() }}
        </div>
    </div>
</x-layouts.app>
