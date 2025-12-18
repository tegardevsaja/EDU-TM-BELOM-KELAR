<x-layouts.app :title="__('History Sertifikat')">
    <div class="max-w-6xl mx-auto p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold">History Sertifikat</h1>
                <p class="text-gray-600 text-sm">Rekap semua sertifikat yang pernah dicetak</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('master.sertifikat.generate.history') }}" class="px-3 py-2 rounded border {{ ($range ?? 'all') === 'all' ? 'bg-gray-200 dark:bg-zinc-700' : 'bg-white dark:bg-zinc-800' }}">Semua Waktu</a>
                <a href="{{ route('master.sertifikat.generate.history', ['range' => 'month']) }}" class="px-3 py-2 rounded border {{ ($range ?? 'all') === 'month' ? 'bg-gray-200 dark:bg-zinc-700' : 'bg-white dark:bg-zinc-800' }}">Bulan Ini</a>
            </div>
        </div>

        <form method="GET" action="{{ route('master.sertifikat.generate.history') }}" class="mb-6 bg-white dark:bg-zinc-800 border rounded p-4">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                <div>
                    <label class="text-xs text-gray-500">Template</label>
                    <select name="template_id" class="w-full border rounded px-2 py-1.5">
                        <option value="">Semua</option>
                        @foreach($templateOptions as $opt)
                            <option value="{{ $opt->id }}" {{ (string)($templateId ?? '') === (string)$opt->id ? 'selected' : '' }}>{{ $opt->nama_template }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Kelas</label>
                    <select name="kelas_id" class="w-full border rounded px-2 py-1.5">
                        <option value="">Semua</option>
                        @foreach($kelasOptions as $opt)
                            <option value="{{ $opt->id }}" {{ (string)($kelasId ?? '') === (string)$opt->id ? 'selected' : '' }}>{{ $opt->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Jenis File</label>
                    <select name="jenis_file" class="w-full border rounded px-2 py-1.5">
                        <option value="">Semua</option>
                        @foreach($jenisOptions as $key => $label)
                            <option value="{{ $key }}" {{ ($jenisFile ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Dari</label>
                    <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}" class="w-full border rounded px-2 py-1.5">
                </div>
                <div>
                    <label class="text-xs text-gray-500">Sampai</label>
                    <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" class="w-full border rounded px-2 py-1.5">
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <input type="text" name="q" value="{{ $queryText ?? '' }}" placeholder="Cari template/kelas..." class="flex-1 border rounded px-3 py-2">
                <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white">Terapkan</button>
                <a href="{{ route('master.sertifikat.generate.history') }}" class="px-4 py-2 rounded border">Reset</a>
            </div>
        </form>

        @php
            $hasFilters = ($templateId ?? null) || ($kelasId ?? null) || ($jenisFile ?? null) || ($dateFrom ?? null) || ($dateTo ?? null) || ($queryText ?? '') !== '' || ($range ?? 'all') !== 'all';
        @endphp
        @if($hasFilters)
            <div class="mb-4 text-sm text-gray-600">
                <span class="mr-2">Filter aktif:</span>
                @if(($range ?? 'all') !== 'all')
                    <span class="inline-block bg-gray-200 rounded px-2 py-0.5 mr-2">Range: {{ $range }}</span>
                @endif
                @if($templateId)
                    <span class="inline-block bg-gray-200 rounded px-2 py-0.5 mr-2">Template: {{ optional($templateOptions->firstWhere('id', $templateId))->nama_template }}</span>
                @endif
                @if($kelasId)
                    <span class="inline-block bg-gray-200 rounded px-2 py-0.5 mr-2">Kelas: {{ optional($kelasOptions->firstWhere('id', $kelasId))->nama_kelas }}</span>
                @endif
                @if($jenisFile)
                    <span class="inline-block bg-gray-200 rounded px-2 py-0.5 mr-2">File: {{ strtoupper($jenisFile) }}</span>
                @endif
                @if($dateFrom || $dateTo)
                    <span class="inline-block bg-gray-200 rounded px-2 py-0.5 mr-2">Tanggal: {{ $dateFrom ?? '...' }} s/d {{ $dateTo ?? '...' }}</span>
                @endif
                @if(($queryText ?? '') !== '')
                    <span class="inline-block bg-gray-200 rounded px-2 py-0.5 mr-2">Cari: "{{ $queryText }}"</span>
                @endif
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="p-4 rounded border bg-white dark:bg-zinc-800">
                <div class="text-sm text-gray-600">Sertifikat Tercetak</div>
                <div class="text-2xl font-semibold">{{ $printedAll ?? 0 }}</div>
            </div>
            <div class="p-4 rounded border bg-white dark:bg-zinc-800">
                <div class="text-sm text-gray-600">Bulan Ini</div>
                <div class="text-2xl font-semibold">{{ $printedThisMonth ?? 0 }}</div>
            </div>
            <div class="p-4 rounded border bg-white dark:bg-zinc-800">
                <div class="text-sm text-gray-600">Ditampilkan</div>
                <div class="text-2xl font-semibold">{{ $histories->total() }}</div>
            </div>
        </div>

        @if($histories->count())
        <div class="overflow-x-auto rounded border bg-white dark:bg-zinc-800">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-zinc-700">
                    <tr>
                        <th class="text-left px-4 py-3">Tanggal</th>
                        <th class="text-left px-4 py-3">Template</th>
                        <th class="text-left px-4 py-3">Kelas</th>
                        <th class="text-right px-4 py-3">Jumlah Siswa</th>
                        <th class="text-left px-4 py-3">Format</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($histories as $h)
                    <tr class="border-t">
                        <td class="px-4 py-3">{{ optional($h->created_at)->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3">{{ $h->nama_template ?? ('#'.$h->template_id) }}</td>
                        <td class="px-4 py-3">{{ $h->nama_kelas ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">{{ $h->jumlah_siswa }}</td>
                        <td class="px-4 py-3 uppercase">{{ $h->jenis_file }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $histories->withQueryString()->links() }}</div>
        @else
        <div class="p-10 text-center rounded border bg-gray-50 dark:bg-zinc-800 text-gray-600">
            Belum ada history pencetakan sertifikat.
        </div>
        @endif
    </div>
</x-layouts.app>
