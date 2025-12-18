<x-layouts.app :title="'Input Nilai (Banyak Siswa)'">
    <div class="p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold">Input Nilai (Banyak Siswa)</h1>
                <p class="text-sm text-zinc-500">Template: <span class="font-medium">{{ $template->nama_template }}</span></p>
            </div>
            <a href="{{ route('master.nilai.choose-template') }}" class="text-sm text-zinc-500 hover:underline">&larr; Ganti Template / Mode</a>
        </div>

        {{-- Filter siswa --}}
        <form method="GET" action="{{ route('master.nilai.create-bulk', $template->id) }}" class="mb-4 border rounded-lg p-3 bg-gray-50 dark:bg-zinc-900/40">
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

        <form action="{{ route('master.penilaian.store-bulk', $template->id) }}" method="POST" class="space-y-4">
            @csrf

            <div class="overflow-x-auto bg-white dark:bg-zinc-900 border rounded-lg">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-3 py-2 text-left">No</th>
                            <th class="px-3 py-2 text-left">Nama</th>
                            <th class="px-3 py-2 text-left">NIS</th>
                            <th class="px-3 py-2 text-left">Kelas</th>
                            <th class="px-3 py-2 text-left">Tahun Ajaran</th>
                            <th class="px-3 py-2 text-left">Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; @endphp
                        @forelse($siswa as $s)
                            <tr class="border-t">
                                <td class="px-3 py-2">{{ $no++ }}</td>
                                <td class="px-3 py-2">{{ $s->nama }}</td>
                                <td class="px-3 py-2">{{ $s->nis ?? '-' }}</td>
                                <td class="px-3 py-2">{{ $s->kelas->nama_kelas ?? '-' }}</td>
                                <td class="px-3 py-2">{{ $s->tahunAjaran->tahun_ajaran ?? '-' }}</td>
                                <td class="px-3 py-2">
                                    <input type="text" name="nilai[{{ $s->id }}]" class="w-24 border rounded px-2 py-1 text-right" placeholder="0 - 100">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-4 text-center text-sm text-zinc-500">Tidak ada siswa dengan filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ url()->previous() }}" class="px-3 py-2 rounded-md border hover:bg-gray-50 dark:hover:bg-zinc-700">Batal</a>
                <button type="submit" class="px-3 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">Simpan Semua Nilai</button>
            </div>
        </form>
    </div>
</x-layouts.app>
