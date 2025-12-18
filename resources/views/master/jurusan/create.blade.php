<x-layouts.app :title="__('Tambah Jurusan')">
    <div class="max-w-xl mx-auto mt-6">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Tambah Jurusan</h2>
                <p class="mt-1 text-sm text-gray-500">Isi nama jurusan dengan jelas agar memudahkan pengelolaan data kelas dan siswa.</p>
            </div>

            {{-- Pesan sukses atau error --}}
            @if (session('success'))
                <div class="mb-4 text-sm text-green-700 bg-green-100 border border-green-200 p-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Form tambah pengguna --}}
            <form action="{{ route('master.jurusan.store') }}" method="POST" class="space-y-4">

            @csrf

            {{-- Nama --}}
            <div>
                <label for="nama_jurusan" class="block text-sm font-medium text-gray-700">Nama Jurusan</label>
                <input type="text" name="nama_jurusan" id="nama_jurusan" value="{{ old('nama_jurusan') }}"
                    class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Contoh: Teknik Komputer dan Jaringan" required>
                @error('nama_jurusan')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex justify-end gap-2 pt-2">
                <a href="{{ route('master.jurusan') }}"
                    class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                    Simpan
                </button>
            </div>
            </form>
        </div>
    </div>
</x-layouts.app>
