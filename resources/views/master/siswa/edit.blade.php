<x-layouts.app :title="__('Edit Data Siswa')">
    <h2 class="text-xl font-semibold mb-6 dark:text-white">Edit Siswa</h2>
    <div class="h-full overflow-hidden shadow-md rounded p-2">
        
        <div class="max-w-4xl mx-auto p-6 bg-white dark:bg-zinc-800 rounded-lg">    
            <form action="{{ route('master.siswa.update', $siswa->id) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')
    
                {{-- NIS --}}
                <div class="flex flex-col">
                    <label class="font-medium text-sm">NIS</label>
                    <input type="number" name="nis" value="{{ old('nis', $siswa->nis) }}"
                        class="px-3 py-2 rounded border dark:bg-zinc-700 dark:text-white"
                        placeholder="Masukkan NIS" minlength="5" required> 
                    @error('nis') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
    
                {{-- Nama --}}
                <div class="flex flex-col">
                    <label class="font-medium text-sm">Nama Lengkap</label>
                    <input type="text" name="nama" value="{{ old('nama', $siswa->nama) }}"
                        class="px-3 py-2 rounded border dark:bg-zinc-700 dark:text-white"
                        placeholder="Masukkan Nama Lengkap">
                    @error('nama') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
    
                {{-- Jenis Kelamin --}}
                <div class="flex flex-col">
                    <label class="font-medium text-sm">Jenis Kelamin</label>
                    <select name="jenis_kelamin"
                        class="px-3 py-2 rounded border dark:bg-zinc-700 dark:text-white">
                        <option value="L" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('jenis_kelamin') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
    
                {{-- Tempat & Tanggal Lahir --}}
                <div class="flex gap-4">
                    <div class="flex flex-col w-1/2">
                        <label class="font-medium text-sm">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $siswa->tempat_lahir) }}"
                            class="px-3 py-2 rounded border dark:bg-zinc-700 dark:text-white"
                            placeholder="Kota lahir">
                        @error('tempat_lahir') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex flex-col w-1/2">
                        <label class="font-medium text-sm">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $siswa->tanggal_lahir) }}"
                            class="px-3 py-2 rounded border dark:bg-zinc-700 dark:text-white">
                        @error('tanggal_lahir') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
    
                {{-- Agama --}}
                <div class="flex flex-col">
                    <label class="font-medium text-sm">Agama</label>
                    <input type="text" name="agama" value="{{ old('agama', $siswa->agama) }}"
                        class="px-3 py-2 rounded border dark:bg-zinc-700 dark:text-white"
                        placeholder="Agama siswa">
                    @error('agama') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
    
                {{-- Orang Tua --}}
                <div class="flex flex-col">
                    <label class="font-medium text-sm">Nama Orang Tua</label>
                    <input type="text" name="nama_orang_tua" value="{{ old('nama_orang_tua', $siswa->nama_orang_tua) }}"
                        class="px-3 py-2 rounded border dark:bg-zinc-700 dark:text-white"
                        placeholder="Masukkan nama orang tua">
                    @error('nama_orang_tua') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
    
                <div class="flex flex-col">
                    <label class="font-medium text-sm">Alamat Orang Tua</label>
                    <textarea name="alamat_orang_tua"
                        class="px-3 py-2 rounded border dark:bg-zinc-700 dark:text-white"
                        placeholder="Alamat lengkap">{{ old('alamat_orang_tua', $siswa->alamat_orang_tua) }}</textarea>
                    @error('alamat_orang_tua') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
    
                <div class="flex flex-col">
                    <label class="font-medium text-sm">No HP Orang Tua</label>
                    <input type="text" name="no_hp_orang_tua" value="{{ old('no_hp_orang_tua', $siswa->no_hp_orang_tua) }}"
                        class="px-3 py-2 rounded border dark:bg-zinc-700 dark:text-white"
                        placeholder="Nomor HP">
                    @error('no_hp_orang_tua') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
    
                {{-- Asal Sekolah --}}
                <div class="flex flex-col">
                    <label class="font-medium text-sm">Asal Sekolah</label>
                    <input type="text" name="asal_sekolah" value="{{ old('asal_sekolah', $siswa->asal_sekolah) }}"
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
                                <option value="{{ $k->id }}" {{ old('kelas_id', $siswa->kelas_id) == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                        @error('kelas_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
    
                    <div class="flex flex-col">
                        <label class="font-medium text-sm">Jurusan</label>
                        <select name="jurusan_id"
                            class="px-3 py-2 rounded border dark:bg-zinc-700 dark:text-white">
                            @foreach ($jurusan as $j)
                                <option value="{{ $j->id }}" {{ old('jurusan_id', $siswa->jurusan_id) == $j->id ? 'selected' : '' }}>
                                    {{ $j->nama_jurusan }}
                                </option>
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
                                    {{ old('tahun_ajaran_id', $siswa->tahun_ajaran_id) == $t->id ? 'selected' : '' }}>
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
                        <input type="number" name="tahun_masuk" value="{{ old('tahun_masuk', $siswa->tahun_masuk) }}"
                            class="px-3 py-2 rounded border dark:bg-zinc-700 dark:text-white"
                            placeholder="Contoh: 2023">
                        @error('tahun_masuk') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
    
                    <div class="flex flex-col">
                        <label class="font-medium text-sm">Status</label>
                        <select name="status"
                            class="px-3 py-2 rounded border dark:bg-zinc-700 dark:text-white">
                            <option value="Aktif" {{ old('status', $siswa->status) == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="Alumni" {{ old('status', $siswa->status) == 'Alumni' ? 'selected' : '' }}>Alumni</option>
                            <option value="Nonaktif" {{ old('status', $siswa->status) == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                            <option value="Dikeluarkan" {{ old('status', $siswa->status) == 'Dikeluarkan' ? 'selected' : '' }}>Dikeluarkan</option>
                        </select>
                        @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
    
                {{-- Tombol Aksi --}}
                <div class="flex justify-between items-center pt-4 border-t border-gray-200 dark:border-zinc-700">
                    <a href="{{ route('master.siswa.index') }}" 
                       class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-700 border border-gray-300 dark:border-zinc-600 rounded hover:bg-gray-50 dark:hover:bg-zinc-600 transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Update Data
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-layouts.app>