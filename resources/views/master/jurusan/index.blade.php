<x-layouts.app :title="__('Master Admin Dashboard')">
   <p>jurusan page</p>
   <div class="flex justify-end">
    <div>
        <a href="{{route('master.jurusan.create')}}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-400 focus:outline-none transition">Tambah Jurusan</a>
    </div>
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
                            <a href="{{ route('master.jurusan.edit', $item->id) }}"
                                class="text-blue-600 hover:underline">Edit</a>
                            <form action="{{ route('master.jurusan.destroy', $item->id) }}" method="POST"
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
        {{ $jurusan->links() }}
    </div>
   </div>
</x-layouts.app>
