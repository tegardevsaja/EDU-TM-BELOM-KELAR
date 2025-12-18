<x-layouts.app :title="__('Edit Tahun Ajaran')">
    <div class="p-6 bg-white dark:bg-zinc-800 shadow-md rounded-lg">
        <h2 class="text-lg font-semibold mb-4">Edit Tahun Ajaran</h2>

        @if ($errors->any())
            <div class="mb-4 p-2 bg-red-100 text-red-700 rounded">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('master.tahun-ajaran.update', $tahunAjaran->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="tahun_ajaran" class="block mb-1 font-medium">Tahun Ajaran</label>
                <input type="text" name="tahun_ajaran" id="tahun_ajaran"
                       value="{{ old('tahun_ajaran', $tahunAjaran->tahun_ajaran) }}"
                       class="w-full p-2 border rounded focus:outline-none focus:ring focus:border-blue-500"
                       placeholder="Contoh: 2025/2026" required>
            </div>

            <div class="mb-4">
                <label for="tanggal_mulai" class="block mb-1 font-medium">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" id="tanggal_mulai"
                       value="{{ old('tanggal_mulai', $tahunAjaran->tanggal_mulai) }}"
                       class="w-full p-2 border rounded focus:outline-none focus:ring focus:border-blue-500"
                       required>
            </div>

            <div class="mb-4">
                <label for="tanggal_selesai" class="block mb-1 font-medium">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" id="tanggal_selesai"
                       value="{{ old('tanggal_selesai', $tahunAjaran->tanggal_selesai) }}"
                       class="w-full p-2 border rounded focus:outline-none focus:ring focus:border-blue-500"
                       required>
            </div>

            <div class="mb-4 flex items-center">
                <input type="checkbox" name="aktif" id="aktif" value="1" class="mr-2"
                       {{ old('aktif', $tahunAjaran->aktif) ? 'checked' : '' }}>
                <label for="aktif" class="font-medium">Jadikan Tahun Ajaran Aktif</label>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Update
                </button>
                <a href="{{ route('master.tahunAjaran') }}"
                   class="px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500">
                    Batal
                </a>
            </div>
        </form>
    </div>
</x-layouts.app>
