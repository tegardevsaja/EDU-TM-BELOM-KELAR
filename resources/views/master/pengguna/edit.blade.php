<x-layouts.app :title="__('Edit Pengguna')">
    <div class="max-w-xl mx-auto bg-white p-6 rounded-lg shadow-md mt-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Edit Pengguna</h2>

        {{-- Pesan sukses --}}
        @if (session('success'))
            <div class="mb-4 text-sm text-green-700 bg-green-100 border border-green-200 p-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('master.pengguna.update', $pengguna->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            {{-- Nama --}}
            <div>
                <label for="nama" class="block text-sm font-medium text-gray-700">Nama</label>
                <input type="text" name="nama" id="nama" value="{{ old('nama', $pengguna->nama) }}"
                    class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    required>
                @error('nama')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $pengguna->email) }}"
                    class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    required>
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- NIK --}}
            <div>
                <label for="nik" class="block text-sm font-medium text-gray-700">NIK</label>
                <input type="text" name="nik" id="nik" value="{{ old('nik', $pengguna->nik) }}"
                    class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('nik')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tombol --}}
            <div class="flex justify-end gap-2 pt-4">
                <a href="{{ route('master.pengguna') }}"
                    class="px-4 py-2 bg-gray-200 text-gray-800 text-sm font-medium rounded-lg hover:bg-gray-300 transition">
                    Batal
                </a>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-400 focus:outline-none transition">
                    Perbarui
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
