<x-layouts.app :title="__('Master Admin Dashboard')">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Tambah Kelas</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Lengkapi informasi kelas dan pilih wali kelas (hanya guru yang tersedia sebagai pilihan).
            </p>
        </div>

        <form action="{{ route('master.kelas.store') }}" method="POST" class="bg-white dark:bg-zinc-800 p-6 rounded-lg shadow-sm border border-zinc-200/70 dark:border-zinc-700 space-y-5">
            @csrf

            {{-- Nama Kelas --}}
            <div class="flex flex-col gap-1">
                <div class="flex items-center justify-between">
                    <label for="nama_kelas" class="font-medium text-sm text-zinc-800 dark:text-zinc-100">Nama Kelas</label>
                    <span class="text-[11px] uppercase tracking-wide text-red-500 font-medium">Wajib</span>
                </div>
                <input 
                    id="nama_kelas" 
                    type="text" 
                    name="nama_kelas" 
                    value="{{ old('nama_kelas') }}"
                    class="w-full px-3 py-2 rounded-md border text-sm border-zinc-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-zinc-700 dark:text-white dark:border-zinc-600"
                    placeholder="Contoh: XII RPL 1">
                @error('nama_kelas')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Jurusan --}}
            <div class="flex flex-col gap-1">
                <div class="flex items-center justify-between">
                    <label for="jurusan_id" class="font-medium text-sm text-zinc-800 dark:text-zinc-100">Jurusan</label>
                    <span class="text-[11px] uppercase tracking-wide text-red-500 font-medium">Wajib</span>
                </div>
                <select 
                    id="jurusan_id" 
                    name="jurusan_id"
                    class="w-full px-3 py-2 rounded-md border text-sm border-zinc-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-zinc-700 dark:text-white dark:border-zinc-600">
                    <option value="" disabled {{ old('jurusan_id') ? '' : 'selected' }}>Pilih jurusan</option>
                    @foreach($jurusans as $jurusan)
                        <option value="{{ $jurusan->id }}" {{ old('jurusan_id') == $jurusan->id ? 'selected' : '' }}>
                            {{ $jurusan->nama_jurusan }}
                        </option>
                    @endforeach
                </select>
                @error('jurusan_id')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Wali Kelas --}}
            <div class="flex flex-col gap-1">
                <div class="flex items-center justify-between">
                    <label for="wali_kelas_id" class="font-medium text-sm text-zinc-800 dark:text-zinc-100">Wali Kelas</label>
                    <span class="text-[11px] uppercase tracking-wide text-red-500 font-medium">Wajib</span>
                </div>
                <select 
                    id="wali_kelas_id" 
                    name="wali_kelas_id"
                    class="w-full px-3 py-2 rounded-md border text-sm border-zinc-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-zinc-700 dark:text-white dark:border-zinc-600">
                    <option value="">-- Pilih Wali Kelas --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('wali_kelas_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
                @error('wali_kelas_id')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tombol --}}
            <div class="flex justify-end gap-2 pt-2">
                <a href="{{ route('master.kelas') }}" class="px-4 py-2 text-sm rounded-md border border-zinc-300 text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-100 dark:hover:bg-zinc-700">Batal</a>
                <button 
                    type="submit" 
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
