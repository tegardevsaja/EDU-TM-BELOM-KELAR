<x-layouts.app :title="__('Master Admin Dashboard')">
    <div class="flex justify-end mb-4 gap-2">
        <a href="{{ route('master.pengguna.create') }}"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-400 focus:outline-none transition">
            + Tambah
        </a>
        <a href="{{ route('master.pengguna.export') }}"
       class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">
       Export Excel
    </a>
        <a href="{{ route('master.pengguna.template') }}" class="btn btn-secondary">Download Template Excel</a>

    </div>

    <form action="{{ route('master.pengguna.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2 mb-4">
    @csrf
    <input type="file" name="file" accept=".xlsx, .xls" required
        class="border border-gray-300 rounded-lg p-2 text-sm focus:ring-blue-500 focus:border-blue-500">
    <button type="submit"
        class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition">
        Import Excel
    </button>
</form>


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
                            <a href="{{ route('master.pengguna.edit', $item->id) }}"
                                class="text-blue-600 hover:underline">Edit</a>
                            <form action="{{ route('master.pengguna.destroy', $item->id) }}" method="POST"
                                onsubmit="return confirm('Yakin ingin menghapus data ini?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                            </form>
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
