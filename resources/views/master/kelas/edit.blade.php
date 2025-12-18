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

<x-layouts.app :title="__('Master Admin Dashboard')">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Edit Kelas</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Perbarui informasi kelas dan wali kelas bila diperlukan.
            </p>
        </div>

        <form action="{{ route($routePrefix . '.kelas.update', $kelas->id) }}" method="POST" class="bg-white dark:bg-zinc-800 p-6 rounded-lg shadow-sm border border-zinc-200/70 dark:border-zinc-700 space-y-5">
            @csrf
            @method('PUT')
            
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
                    value="{{ old('nama_kelas', $kelas->nama_kelas) }}"
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
                    @foreach($jurusans as $jurusan)
                        <option value="{{ $jurusan->id }}" {{ old('jurusan_id', $kelas->jurusan_id) == $jurusan->id ? 'selected' : '' }}>
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
                        <option value="{{ $user->id }}" {{ old('wali_kelas_id', $kelas->wali_kelas_id) == $user->id ? 'selected' : '' }}>
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
                <x-ui.button :href="route($routePrefix . '.kelas')" variant="secondary" size="md">Batal</x-ui.button>
                <x-ui.button type="submit" variant="primary" size="md">Update</x-ui.button>
            </div>
        </form>
    </div>
</x-layouts.app>
