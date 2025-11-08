<x-layouts.app :title="__('Master Admin Dashboard')">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold">Data Kelas</h2>
        <a href="{{ route('master.kelas.create') }}"
           class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
           Tambah Kelas
        </a>
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
                        <a href="{{ route('master.kelas.edit', $kls->id) }}"
                           class="text-blue-600 hover:underline">Edit</a>
                        
                        <form action="{{ route('master.kelas.destroy', $kls->id) }}"
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
