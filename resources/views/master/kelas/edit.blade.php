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
    <form action="{{ route($routePrefix . '.kelas.update', $kelas->id) }}" method="POST" class="max-w-lg mx-auto bg-white dark:bg-zinc-800 p-6 rounded-lg shadow-md space-y-5">
        @csrf
        @method('PUT')
        
        {{-- Nama Kelas --}}
        <div class="flex flex-col space-y-1">
            <label for="nama_kelas" class="font-medium text-sm">Nama Kelas</label>
            <input 
                id="nama_kelas" 
                type="text" 
                name="nama_kelas" 
                value="{{ old('nama_kelas', $kelas->nama_kelas) }}"
                class="w-full px-3 py-2 rounded border border-gray-300 focus:ring focus:ring-blue-300 dark:bg-zinc-700 dark:text-white dark:border-zinc-600"
                placeholder="Masukkan nama kelas">
        </div>

        {{-- Jurusan --}}
        <div class="flex flex-col space-y-1">
            <label for="jurusan_id" class="font-medium text-sm">Jurusan</label>
            <select 
                id="jurusan_id" 
                name="jurusan_id"
                class="w-full px-3 py-2 rounded border border-gray-300 focus:ring focus:ring-blue-300 dark:bg-zinc-700 dark:text-white dark:border-zinc-600">
                @foreach($jurusans as $jurusan)
                    <option value="{{ $jurusan->id }}" {{ old('jurusan_id', $kelas->jurusan_id) == $jurusan->id ? 'selected' : '' }}>
                        {{ $jurusan->nama_jurusan }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Wali Kelas --}}
        <div class="flex flex-col space-y-1">
            <label for="wali_kelas_id" class="font-medium text-sm">Wali Kelas</label>
            <select 
                id="wali_kelas_id" 
                name="wali_kelas_id"
                class="w-full px-3 py-2 rounded border border-gray-300 focus:ring focus:ring-blue-300 dark:bg-zinc-700 dark:text-white dark:border-zinc-600">
                <option value="">-- Pilih Wali Kelas --</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('wali_kelas_id', $kelas->wali_kelas_id) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Tombol --}}
        <div class="flex justify-end gap-2">
            <x-ui.button :href="route($routePrefix . '.kelas')" variant="secondary" size="md">Batal</x-ui.button>
            <x-ui.button type="submit" variant="primary" size="md">Update</x-ui.button>
        </div>
    </form>
</x-layouts.app>
