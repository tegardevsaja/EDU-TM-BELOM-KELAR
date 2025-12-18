<x-layouts.app :title="__('Tambah Data Siswa')">
    {{-- Notifikasi (jika ada) --}}
    @if(session('success') || session('error'))
        <div class="max-w-4xl mx-auto mt-4 mb-2">
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition
                     class="flex items-start gap-2 rounded-md border border-emerald-300 bg-emerald-50 px-3 py-2 text-sm text-emerald-800 dark:bg-emerald-900/40 dark:border-emerald-700 dark:text-emerald-100">
                    <div class="mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium">Berhasil</p>
                        <p class="text-xs mt-0.5">{{ session('success') }}</p>
                    </div>
                    <button type="button" class="text-emerald-700 hover:text-emerald-900 dark:text-emerald-200" @click="show = false">
                        ✕
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition
                     class="mt-2 flex items-start gap-2 rounded-md border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-800 dark:bg-red-900/40 dark:border-red-700 dark:text-red-100">
                    <div class="mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/></svg>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium">Terjadi kesalahan</p>
                        <p class="text-xs mt-0.5">{{ session('error') }}</p>
                    </div>
                    <button type="button" class="text-red-700 hover:text-red-900 dark:text-red-200" @click="show = false">
                        ✕
                    </button>
                </div>
            @endif
        </div>
    @endif

    <div class="max-w-4xl mx-auto p-6 bg-white dark:bg-zinc-800 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-6 dark:text-white">Tambah Siswa</h2>

        <form action="{{ route('master.siswa.store') }}" method="POST" class="space-y-5">
            @csrf

            {{-- NIS --}}
            <div class="flex flex-col">
                <label class="font-medium text-sm">NIS</label>
                <input type="text" name="nis" value="{{ old('nis') }}"
                    class="px-3 py-2 rounded border dark:bg-zinc-700 dark:text-white"
                    placeholder="Masukkan NIS" inputmode="numeric" pattern="[0-9]*" maxlength="24"
                    oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                @error('nis') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- Nama --}}
            <div class="flex flex-col">
                <label class="font-medium text-sm">Nama Lengkap</label>
                <input type="text" name="nama" value="{{ old('nama') }}"
                    class="px-3 py-2 rounded border dark:bg-zinc-700 dark:text-white"
                    placeholder="Masukkan Nama Lengkap">
                @error('nama') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- Jenis Kelamin --}}
            <div class="flex flex-col">
                <label class="font-medium text-sm">Jenis Kelamin</label>
                <select name="jenis_kelamin"
                    class="px-3 py-2 rounded border dark:bg-zinc-700 dark:text-white">
                    <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                </select>
                @error('jenis_kelamin') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- Tempat & Tanggal Lahir --}}
            <div class="flex gap-4">
                <div class="flex flex-col w-1/2">
                    <label class="font-medium text-sm">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}"
                        class="px-3 py-2 rounded border dark:bg-zinc-700 dark:text-white"
                        placeholder="Kota lahir">
                    @error('tempat_lahir') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="flex flex-col w-1/2">
                    <label class="font-medium text-sm">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}"
                        class="px-3 py-2 rounded border dark:bg-zinc-700 dark:text-white">
                    @error('tanggal_lahir') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Agama --}}
            <div class="flex flex-col">
                <label class="font-medium text-sm">Agama</label>
                <input type="text" name="agama" value="{{ old('agama') }}"
                    class="px-3 py-2 rounded border dark:bg-zinc-700 dark:text-white"
                    placeholder="Agama siswa">
                @error('agama') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- Orang Tua --}}
            <div class="flex flex-col">
                <label class="font-medium text-sm">Nama Orang Tua</label>
                <input type="text" name="nama_orang_tua" value="{{ old('nama_orang_tua') }}"
                    class="px-3 py-2 rounded border dark:bg-zinc-700 dark:text-white"
                    placeholder="Masukkan nama orang tua">
                @error('nama_orang_tua') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex flex-col">
                <label class="font-medium text-sm">Alamat Orang Tua</label>
                <textarea name="alamat_orang_tua"
                    class="px-3 py-2 rounded border dark:bg-zinc-700 dark:text-white"
                    placeholder="Alamat lengkap">{{ old('alamat_orang_tua') }}</textarea>
                @error('alamat_orang_tua') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex flex-col">
                <label class="font-medium text-sm">No HP Orang Tua</label>
                <input type="text" name="no_hp_orang_tua" value="{{ old('no_hp_orang_tua') }}"
                    class="px-3 py-2 rounded border dark:bg-zinc-700 dark:text-white"
                    placeholder="Nomor HP" inputmode="tel" pattern="[0-9]*" maxlength="18"
                    oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                @error('no_hp_orang_tua') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- Asal Sekolah --}}
            <div class="flex flex-col">
                <label class="font-medium text-sm">Asal Sekolah</label>
                <input type="text" name="asal_sekolah" value="{{ old('asal_sekolah') }}"
                    class="px-3 py-2 rounded border dark:bg-zinc-700 dark:text-white"
                    placeholder="Asal sekolah">
                @error('asal_sekolah') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- Kelas, Jurusan, Tahun Ajaran --}}
            <div class="grid grid-cols-3 gap-4">

                <div class="flex flex-col">
                    <label class="font-medium text-sm">Kelas</label>
                    <select name="kelas_id"
                        class="px-3 py-2 rounded border dark:bg-zinc-700 dark:text-white">
                        @foreach ($kelas as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                    @error('kelas_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="flex flex-col">
                    <label class="font-medium text-sm">Jurusan</label>
                    <select name="jurusan_id"
                        class="px-3 py-2 rounded border dark:bg-zinc-700 dark:text-white">
                        @foreach ($jurusan as $j)
                            <option value="{{ $j->id }}">{{ $j->nama_jurusan }}</option>
                        @endforeach
                    </select>
                    @error('jurusan_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="flex flex-col">
                    <label class="font-medium text-sm">Tahun Ajaran</label>
                    <select name="tahun_ajaran_id"
                        class="px-3 py-2 rounded border dark:bg-zinc-700 dark:text-white">
                        <option value="">-- Pilih Tahun Ajaran --</option>
                        @foreach ($tahun_ajaran as $t)
                            <option value="{{ $t->id }}" 
                                {{ old('tahun_ajaran_id') == $t->id ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::parse($t->tanggal_mulai)->format('Y') }} /
                                {{ \Carbon\Carbon::parse($t->tanggal_selesai)->format('Y') }}
                            </option>
                        @endforeach
                    </select>
                    @error('tahun_ajaran_id')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

            </div>

            {{-- Tahun Masuk & Status --}}
            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col">
                    <label class="font-medium text-sm">Tahun Masuk</label>
                    <input type="number" name="tahun_masuk" value="{{ old('tahun_masuk') }}"
                        class="px-3 py-2 rounded border dark:bg-zinc-700 dark:text-white"
                        placeholder="Contoh: 2023">
                    @error('tahun_masuk') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="flex flex-col">
                    <label class="font-medium text-sm">Status</label>
                    <select name="status"
                        class="px-3 py-2 rounded border dark:bg-zinc-700 dark:text-white">
                        <option value="Aktif">Aktif</option>
                        <option value="Alumni">Alumni</option>
                        <option value="Nonaktif">Nonaktif</option>
                        <option value="Dikeluarkan">Dikeluarkan</option>
                    </select>
                    @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="text-right">
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
