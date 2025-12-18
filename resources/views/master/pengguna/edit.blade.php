<x-layouts.app :title="__('Edit Pengguna')">
    <div class="max-w-xl mx-auto bg-white p-6 rounded-lg shadow-md mt-6">
        <div class="mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Edit Pengguna</h2>
            <p class="text-sm text-gray-500 mt-1">Perbarui data pengguna dengan informasi yang benar dan terbaru.</p>
        </div>

        {{-- Pesan sukses --}}
        @if (session('success'))
            <div class="mb-4 text-sm text-green-700 bg-green-100 border border-green-200 p-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('master.pengguna.update', $pengguna->id) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Nama --}}
            <div>
                <label for="nama" class="block text-sm font-medium text-gray-700">Nama Lengkap<span class="text-red-500">*</span></label>
                <input
                    type="text"
                    name="nama"
                    id="nama"
                    value="{{ old('nama', $pengguna->nama) }}"
                    placeholder="Masukkan nama lengkap pengguna"
                    class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                    required
                >
                @error('nama')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email<span class="text-red-500">*</span></label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    value="{{ old('email', $pengguna->email) }}"
                    placeholder="nama@sekolah.sch.id"
                    class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                    required
                >
                <p class="text-xs text-gray-400 mt-1">Pastikan email aktif dan dapat diakses oleh pengguna.</p>
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- NIK --}}
            <div>
                <label for="nik" class="block text-sm font-medium text-gray-700">NIK</label>
                <input
                    type="text"
                    name="nik"
                    id="nik"
                    value="{{ old('nik', $pengguna->nik) }}"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="30"
                    class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                    placeholder="Hanya angka, tanpa spasi atau tanda baca"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                >
                <p class="text-xs text-gray-400 mt-1">Isi hanya jika diperlukan. NIK hanya boleh berisi karakter angka.</p>
                @error('nik')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Status --}}
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select
                    name="status"
                    id="status"
                    class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                >
                    @php $st = old('status', $pengguna->status ?? 'aktif'); @endphp
                    <option value="aktif" {{ $st==='aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ $st==='nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                @error('status')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tombol --}}
            <div class="flex justify-between items-center pt-4 border-t border-gray-100 mt-2">
                <p class="text-xs text-gray-400">Kolom bertanda <span class="text-red-500">*</span> wajib diisi.</p>
                <div class="flex gap-2">
                    <a
                        href="{{ route('master.pengguna') }}"
                        class="px-4 py-2 bg-gray-200 text-gray-800 text-sm font-medium rounded-lg hover:bg-gray-300 transition"
                    >
                        Batal
                    </a>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-400 focus:outline-none transition"
                    >
                        Perbarui
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.app>
