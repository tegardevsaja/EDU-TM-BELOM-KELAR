<x-layouts.app :title="'Pilih Data Siswa'">
    <div class="max-w-6xl mx-auto p-6">
        <h2 class="text-xl font-semibold mb-4 dark:text-white">Pilih Data Siswa (Opsional)</h2>

        <div class="mb-4 text-sm text-gray-600 dark:text-gray-300">
            <p>Langkah: Pilih Template Sertifikat → <strong>Pilih Siswa (opsional)</strong> → Pilih Template Nilai → Customize</p>
            <p class="mt-1">Jika tidak memilih siswa, data siswa tidak akan ditampilkan pada sertifikat.</p>
        </div>

        <form method="GET" action="{{ route('master.sertifikat.select_grade', $template->id) }}" id="studentsForm" class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-1">
                    <label class="block text-sm font-medium mb-1">Filter Kelas</label>
                    <select name="kelas_id" class="w-full rounded border px-3 py-2" onchange="filterByKelas(this.value)">
                        <option value="">Semua Kelas</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-gray-500">Opsional. Memudahkan seleksi siswa by kelas.</p>
                </div>
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium mb-2">Pilih Siswa (bisa lebih dari satu)</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-2 max-h-[360px] overflow-y-auto border rounded p-3 bg-white dark:bg-zinc-800">
                        @foreach($siswas as $s)
                            <label class="flex items-center gap-2 p-2 rounded hover:bg-zinc-50 dark:hover:bg-zinc-700" data-kelas="{{ $s->kelas->id ?? '' }}">
                                <input type="checkbox" name="siswa_ids[]" value="{{ $s->id }}" class="siswa-checkbox">
                                <span class="text-sm">{{ $s->nama }}
                                    <span class="text-xs text-gray-500">({{ $s->kelas->nama_kelas ?? '-' }})</span>
                                </span>
                            </label>
                        @endforeach
                        @if($siswas->isEmpty())
                            <div class="text-sm text-gray-500">Tidak ada data siswa.</div>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 mt-3 text-sm">
                        <button type="button" class="rounded border px-3 py-1.5" onclick="toggleAll(true)">Pilih Semua</button>
                        <button type="button" class="rounded border px-3 py-1.5" onclick="toggleAll(false)">Kosongkan</button>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('master.sertifikat.select_template') }}" class="rounded border px-4 py-2">Kembali</a>
                <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white">Lanjut pilih Template Nilai</button>
            </div>
        </form>
    </div>

    <script>
        function filterByKelas(kelasId) {
            document.querySelectorAll('[data-kelas]').forEach(el => {
                const kid = el.getAttribute('data-kelas') || '';
                el.style.display = (!kelasId || kid === kelasId) ? '' : 'none';
            });
        }
        function toggleAll(checked) {
            document.querySelectorAll('.siswa-checkbox').forEach(ch => ch.checked = checked);
        }
    </script>
</x-layouts.app>
