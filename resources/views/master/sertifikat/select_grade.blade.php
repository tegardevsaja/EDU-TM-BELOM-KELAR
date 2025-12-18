<x-layouts.app :title="'Pilih Template Nilai (Opsional)'">
    <div class="max-w-4xl mx-auto p-6">
        <h2 class="text-xl font-semibold mb-1 dark:text-white">Pilih Template Nilai (Opsional)</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Anda dapat melanjutkan tanpa memilih template nilai jika hanya ingin mencetak sertifikat.</p>

        <div class="mb-4 text-sm text-gray-600 dark:text-gray-300">
            <p>Langkah: Pilih Template Sertifikat → Pilih Siswa <strong>(opsional)</strong> → Pilih Template Nilai <strong>(opsional)</strong> → Customize</p>
        </div>

        @if(session('success'))
            <div class="mb-4 px-4 py-3 rounded bg-green-100 text-green-800 border border-green-300 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 px-4 py-3 rounded bg-red-100 text-red-800 border border-red-300 text-sm">{{ session('error') }}</div>
        @endif

        <div class="mb-6 p-4 border rounded bg-gray-50 dark:bg-gray-800 dark:border-gray-700">
            <div class="font-semibold dark:text-white mb-2">Pilih Berdasarkan Data Nilai yang Sudah Diimport</div>
            <div class="flex items-center gap-3">
                <input id="q" type="text" placeholder="Cari template / kelas / jurusan..." class="w-full md:w-1/2 border rounded px-3 py-2 dark:bg-gray-700 dark:text-gray-100">
                <button type="button" id="btnClear" class="px-3 py-2 rounded border">Bersihkan</button>
            </div>
        </div>

        <form action="{{ route('master.sertifikat.generate.customize', $template->id) }}" method="GET" class="space-y-5">
            @if(!empty($selectedStudentIds) && count($selectedStudentIds) > 0)
                @foreach($selectedStudentIds as $sid)
                    <input type="hidden" name="siswa_ids[]" value="{{ $sid }}" />
                @endforeach
            @endif
            @if(!empty($kelas_id))
                <input type="hidden" name="kelas_id" value="{{ $kelas_id }}" />
            @endif

            @php $prefGt = request()->get('grade_template_id') ?? session('last_grade_template_id'); @endphp
            <div id="gradeList" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($gradeTemplates as $tpl)
                    @php
                        $sum = $nilaiSummaries[$tpl->id] ?? null;
                        $classes = $sum ? (implode(', ', $sum['kelas']->toArray()) ?: '-') : '-';
                        $majors  = $sum ? (implode(', ', $sum['jurusan']->toArray()) ?: '-') : '-';
                        $total   = $sum['total'] ?? ($nilaiCounts[$tpl->id] ?? 0);
                    @endphp
                    <label class="border rounded-lg p-4 cursor-pointer hover:border-purple-500 flex items-start gap-3" data-search="{{ strtolower(($sum['template_name'] ?? $tpl->nama_template) . ' ' . $classes . ' ' . $majors) }}">
                        <input type="radio" name="grade_template_id" value="{{ $tpl->id }}" class="mt-1" {{ ($prefGt == $tpl->id) ? 'checked' : '' }}>
                        <div class="flex-1">
                            <div class="flex items-center justify-between gap-3">
                                <div class="font-semibold">{{ $sum['template_name'] ?? $tpl->nama_template }}</div>
                                <span class="text-xs px-2 py-1 rounded-full bg-gray-100">{{ $total }} nilai</span>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-2 text-xs text-gray-600">
                                <div>
                                    <div class="text-gray-400">Kelas</div>
                                    <div class="font-medium text-gray-700 dark:text-gray-300 truncate">{{ $classes }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-400">Jurusan</div>
                                    <div class="font-medium text-gray-700 dark:text-gray-300 truncate">{{ $majors }}</div>
                                </div>
                            </div>
                        </div>
                    </label>
                @empty
                    <div class="text-sm text-gray-500">Belum ada template nilai yang dapat dipilih.</div>
                @endforelse
            </div>

            <div class="flex items-center justify-between gap-2 mt-4">
                <a href="{{ route('master.sertifikat.signatures.edit', ['return' => request()->fullUrl()]) }}" class="inline-flex items-center px-3 py-2 text-xs font-medium rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                    Pengaturan Penguji &amp; Tanda Tangan
                </a>
                <div class="flex gap-2">
                    <a href="{{ url()->previous() }}" class="rounded border px-4 py-2">Kembali</a>
                    <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white">Lanjut ke Customize</button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.app>

<script>
    (function(){
        const input = document.getElementById('q');
        const btnClear = document.getElementById('btnClear');
        const container = document.getElementById('gradeList');
        if (!input || !container) return;

        function applyFilter() {
            const term = (input.value || '').toLowerCase().trim();
            const items = container.querySelectorAll('label[data-search]');
            let any = false;
            items.forEach(el => {
                const hay = (el.getAttribute('data-search') || '').toLowerCase();
                const ok = !term || hay.includes(term);
                el.style.display = ok ? '' : 'none';
                if (ok) any = true;
            });
        }

        input.addEventListener('input', applyFilter);
        btnClear?.addEventListener('click', function(){ input.value=''; applyFilter(); });
        // initial filter on load (preserve query if any)
        applyFilter();
    })();
</script>
