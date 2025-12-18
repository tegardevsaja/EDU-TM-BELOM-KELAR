<x-layouts.app :title="'Input Nilai'">
    <div class="p-6 space-y-4">

        <h1 class="text-xl font-bold mb-2">Input Nilai: {{ $template->nama_template }}</h1>

        @if(session('success'))
            <div class="bg-emerald-100 text-emerald-800 px-3 py-2 rounded mb-2">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-rose-100 text-rose-800 px-3 py-2 rounded mb-2">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-rose-100 text-rose-800 px-3 py-2 rounded mb-2">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $currentName = \Illuminate\Support\Facades\Route::currentRouteName();
            $group = $currentName ? explode('.', $currentName)[0] : (in_array(request()->segment(1), ['admin','guru','master']) ? request()->segment(1) : 'master');
        @endphp
        {{-- Filter siswa --}}
        <form method="GET" action="{{ route($group.'.nilai.create', $template->id) }}" class="mb-4 border rounded-lg p-3 bg-gray-50 dark:bg-zinc-900/40">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-sm">
                <div>
                    <label class="block text-xs font-medium text-zinc-600 mb-1">Kelas</label>
                    <select name="kelas_id" class="w-full border rounded px-2 py-1.5 bg-white dark:bg-zinc-900">
                        <option value="">Semua</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-zinc-600 mb-1">Tahun Ajaran</label>
                    <select name="tahun_ajaran_id" class="w-full border rounded px-2 py-1.5 bg-white dark:bg-zinc-900">
                        <option value="">Semua</option>
                        @foreach($tahunAjaran as $ta)
                            <option value="{{ $ta->id }}" {{ request('tahun_ajaran_id') == $ta->id ? 'selected' : '' }}>
                                {{ $ta->tahun_ajaran }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-zinc-600 mb-1">Gender</label>
                    <select name="gender" class="w-full border rounded px-2 py-1.5 bg-white dark:bg-zinc-900">
                        <option value="">Semua</option>
                        <option value="L" {{ request('gender') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ request('gender') === 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-zinc-600 mb-1">Cari Nama / NIS</label>
                    <div class="flex gap-2">
                        <input type="text" name="q" value="{{ request('q') }}" class="flex-1 border rounded px-2 py-1.5 bg-white dark:bg-zinc-900" placeholder="Ketik nama atau NIS">
                        <button type="submit" class="px-3 py-1.5 rounded bg-blue-600 text-white text-xs hover:bg-blue-700">Filter</button>
                    </div>
                </div>
            </div>
        </form>

        <form action="{{ route($group.'.nilai.store', $template->id) }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="redirect" value="{{ url()->current() }}">

            {{-- Pilih siswa --}}
            <div>
                <label class="block text-sm font-semibold mb-1">Pilih Siswa</label>
                <select name="siswa_id" class="border rounded px-3 py-2 w-full bg-white dark:bg-zinc-900" required>
                    @forelse($siswa as $s)
                        <option value="{{ $s->id }}" {{ old('siswa_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->nama }} ({{ $s->nis ?? '-' }})
                            - {{ $s->kelas->nama_kelas ?? '-' }}
                            - {{ $s->jurusan->nama_jurusan ?? '-' }}
                            - {{ $s->tahunAjaran->tahun_ajaran ?? '-' }}
                        </option>
                    @empty
                        <option value="">Tidak ada siswa dengan filter ini</option>
                    @endforelse
                </select>
            </div>

            {{-- Tabel nilai --}}
            <div class="overflow-x-auto">
                <table class="min-w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border py-2 px-3">No</th>
                            <th class="border py-2 px-3">Komponen</th>
                            <th class="border py-2 px-3">Subfield</th>
                            <th class="border py-2 px-3">Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; @endphp
                        @foreach($template->komponen as $group)
                            @php
                                $komponenLabel = $group['kategori'] ?? '-';
                                $subkomponen = $group['subkomponen'] ?? [];
                            @endphp
                            @foreach($subkomponen as $sub)
                                @php $subLabel = is_array($sub) ? ($sub['uraian'] ?? '-') : $sub; @endphp
                                <tr>
                                    <td class="border py-2 px-3 text-center">{{ $no++ }}</td>
                                    <td class="border py-2 px-3">{{ $komponenLabel }}</td>
                                    <td class="border py-2 px-3">{{ $subLabel }}</td>
                                    <td class="border py-2 px-3">
                                        <input type="number" step="0.01" min="0" max="100" name="nilai[{{ $komponenLabel }}][{{ $subLabel }}]"
                                               class="w-full border rounded p-1" placeholder="0 - 100"
                                               value="{{ old('nilai.'.$komponenLabel.'.'.$subLabel) }}">
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Visibilitas -->
            <div class="mt-4">
                <label class="block font-semibold mb-1">Visibilitas</label>
                <select name="visibility" class="border rounded p-2 w-full">
                    <option value="all" selected>Semua (Admin dan Guru)</option>
                    <option value="admin">Admin saja</option>
                </select>
            </div>

            <button type="submit" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded">
                Simpan Nilai
            </button>
        </form>

    </div>
</x-layouts.app>
