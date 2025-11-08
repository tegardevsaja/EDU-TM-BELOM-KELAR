<x-layouts.app :title="__('Tambah Jurusan')">
    <div class="max-w-xl mx-auto bg-white p-6 rounded-lg shadow-md mt-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Tambah Pengguna</h2>

        {{-- Pesan sukses atau error --}}
        @if (session('success'))
            <div class="mb-4 text-sm text-green-700 bg-green-100 border border-green-200 p-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        {{-- Form tambah pengguna --}}
<form action="{{ route('master.jurusan.store') }}" method="POST">

            @csrf

            {{-- Nama --}}
            <div>
                <label for="nama" class="block text-sm font-medium text-gray-700">Nama Jurusan</label>
                <input type="text" name="nama_jurusan" id="nama_jurusan" value="{{ old('nama_jurusan') }}"
                    class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Masukkan nama jurusan" required>
                @error('nama_jurusan')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>       

            {{-- Tombol Aksi --}}
            <div class="flex justify-end gap-2 pt-4">
                <a href="{{ route('master.jurusan') }}"
                    class="px-4 py-2 bg-gray-200 text-gray-800 text-sm font-medium rounded-lg hover:bg-gray-300 transition">
                    Batal
                </a>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-400 focus:outline-none transition">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
