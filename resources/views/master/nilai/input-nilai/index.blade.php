<x-layouts.app :title="__('Daftar Nilai')">
    @if(session('success'))
        <div class="mb-3 px-4 py-2 rounded bg-emerald-100 text-emerald-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-3 px-4 py-2 rounded bg-rose-100 text-rose-800">{{ session('error') }}</div>
    @endif
    @php
        $currentName = \Illuminate\Support\Facades\Route::currentRouteName();
        $group = $currentName ? explode('.', $currentName)[0] : (in_array(request()->segment(1), ['admin','guru','master']) ? request()->segment(1) : 'master');
    @endphp
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">Daftar Nilai</h2>
        <div class="flex gap-2">
            @if(\Illuminate\Support\Facades\Route::has($group.'.nilai.choose-template'))
                <a href="{{ route($group.'.nilai.choose-template') }}" class="px-3 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700">+ Tambah Nilai</a>
            @endif
            @if(\Illuminate\Support\Facades\Route::has($group.'.nilai.import.index'))
                <a href="{{ route($group.'.nilai.import.index') }}" class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Import Nilai</a>
            @endif
            @if(\Illuminate\Support\Facades\Route::has($group.'.penilaian'))
                <a href="{{ route($group.'.penilaian') }}" class="px-3 py-2 bg-gray-200 rounded hover:bg-gray-300">Kelola Template</a>
            @endif
        </div>
    </div>

    <div class="mb-4 inline-flex rounded border overflow-hidden">
        <a href="{{ route($group.'.nilai.index') }}" class="px-4 py-2 text-sm {{ empty($format) ? 'bg-gray-200 dark:bg-zinc-700' : 'bg-white dark:bg-zinc-800' }}">Semua</a>
        <a href="{{ route($group.'.nilai.index', ['format' => 'TA']) }}" class="px-4 py-2 text-sm {{ ($format ?? '') === 'TA' ? 'bg-gray-200 dark:bg-zinc-700' : 'bg-white dark:bg-zinc-800' }}">Nilai TA</a>
        <a href="{{ route($group.'.nilai.index', ['format' => 'Uji DUDI']) }}" class="px-4 py-2 text-sm {{ ($format ?? '') === 'Uji DUDI' ? 'bg-gray-200 dark:bg-zinc-700' : 'bg-white dark:bg-zinc-800' }}">UKK Dudi</a>
    </div>

    @php
        $qs = request()->query();
        $formatParam = array_filter(['format' => $format ?? null], fn($v) => !is_null($v));
        $btn = function($label, $params, $currentSortBy, $currentSortDir) use ($formatParam, $group) {
            $merged = array_merge($formatParam, $params);
            $isActive = (request('sort_by') === ($params['sort_by'] ?? null)) && (strtolower(request('sort_dir')) === strtolower($params['sort_dir'] ?? ''));
            $classes = 'px-3 py-1.5 rounded border text-sm '.($isActive ? 'bg-gray-200 dark:bg-zinc-700' : 'bg-white dark:bg-zinc-800');
            return '<a class="'.$classes.'" href="'.route($group.'.nilai.index', $merged).'">'.$label.'</a>';
        };
        $currentSortBy = request('sort_by', 'tanggal');
        $currentSortDir = strtolower(request('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
    @endphp

    <div class="mb-4 flex flex-wrap items-center gap-2">
        <span class="text-sm text-gray-600">Urutkan:</span>
        {!! $btn('Terbaru', ['sort_by' => 'tanggal', 'sort_dir' => 'desc'], $currentSortBy, $currentSortDir) !!}
        {!! $btn('Terlama', ['sort_by' => 'tanggal', 'sort_dir' => 'asc'], $currentSortBy, $currentSortDir) !!}
        {!! $btn('Nilai Terbaik', ['sort_by' => 'nilai', 'sort_dir' => 'desc'], $currentSortBy, $currentSortDir) !!}
        {!! $btn('Nilai Terendah', ['sort_by' => 'nilai', 'sort_dir' => 'asc'], $currentSortBy, $currentSortDir) !!}
        {!! $btn('Nama Siswa A-Z', ['sort_by' => 'siswa', 'sort_dir' => 'asc'], $currentSortBy, $currentSortDir) !!}
        {!! $btn('Nama Siswa Z-A', ['sort_by' => 'siswa', 'sort_dir' => 'desc'], $currentSortBy, $currentSortDir) !!}
    </div>

    @if(isset($penilaians) && $penilaians->count())
        <div class="overflow-x-auto bg-white dark:bg-zinc-800 rounded border">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-zinc-700">
                    <tr>
                        @php
                            $currentSortBy = request('sort_by', 'tanggal');
                            $currentSortDir = strtolower(request('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
                            $toggleDir = $currentSortDir === 'asc' ? 'desc' : 'asc';
                            $baseParams = array_filter([
                                'format' => $format ?? null,
                            ], fn($v) => !is_null($v));
                            function sort_link($key, $label, $currentSortBy, $currentSortDir, $toggleDir, $baseParams, $group) {
                                $isActive = $currentSortBy === $key;
                                $dir = $isActive ? $toggleDir : 'asc';
                                $params = array_merge($baseParams, ['sort_by' => $key, 'sort_dir' => $dir]);
                                $arrow = '';
                                if ($isActive) {
                                    $arrow = $currentSortDir === 'asc' ? '▲' : '▼';
                                }
                                $url = route($group.'.nilai.index', $params);
                                return '<a href="'.$url.'" class="inline-flex items-center gap-1">'.$label.'<span class="text-xs opacity-70">'.$arrow.'</span></a>';
                            }
                        @endphp
                        <th class="text-left px-4 py-3">{!! sort_link('tanggal', 'Tanggal', $currentSortBy, $currentSortDir, $toggleDir, $baseParams, $group) !!}</th>
                        <th class="text-left px-4 py-3">{!! sort_link('template', 'Template', $currentSortBy, $currentSortDir, $toggleDir, $baseParams, $group) !!}</th>
                        <th class="text-left px-4 py-3">{!! sort_link('siswa', 'Siswa', $currentSortBy, $currentSortDir, $toggleDir, $baseParams, $group) !!}</th>
                        <th class="text-left px-4 py-3">{!! sort_link('jenis', 'Jenis', $currentSortBy, $currentSortDir, $toggleDir, $baseParams, $group) !!}</th>
                        <th class="text-left px-4 py-3">{!! sort_link('nilai', 'Nilai', $currentSortBy, $currentSortDir, $toggleDir, $baseParams, $group) !!}</th>
                        <th class="text-left px-4 py-3">Ringkasan</th>
                        <th class="text-right px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($penilaians as $n)
                        <tr class="border-t">
                            @php
                                $detail = $n->nilai_detail ?? [];
                                $row = $detail['row'] ?? [];
                                $formatDetail = $detail['format'] ?? null;
                            @endphp
                            <td class="px-4 py-3">{{ optional($n->tanggal_input)->format('d M Y') ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $n->template->nama_template ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $n->siswa->nama ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $n->jenis_penilaian ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $nilaiUnified = null;
                                    if (($n->jenis_penilaian ?? '') === 'TA') {
                                        $nilaiUnified = $row['na'] ?? null;
                                    } elseif (($n->jenis_penilaian ?? '') === 'Uji DUDI') {
                                        $nilaiUnified = $row['nilai_akhir'] ?? null;
                                        if ($nilaiUnified === null && strtoupper((string)$formatDetail) === 'PRAKERIN') {
                                            $vals = [];
                                            $stack = [$detail];
                                            while ($stack) {
                                                $item = array_pop($stack);
                                                if (is_array($item)) {
                                                    foreach ($item as $k => $v) {
                                                        if (in_array($k, ['format','judul_laporan','row'], true)) continue;
                                                        if (is_array($v)) {
                                                            $stack[] = $v;
                                                        } elseif (is_numeric($v)) {
                                                            $vals[] = (float)$v;
                                                        }
                                                    }
                                                }
                                            }
                                            if (count($vals)) {
                                                $nilaiUnified = array_sum($vals) / count($vals);
                                            }
                                        }
                                    } elseif ($formatDetail === 'manual_bulk') {
                                        $nilaiUnified = $row['nilai_utama'] ?? null;
                                    }
                                    $nilaiDisplay = '-';
                                    if (is_numeric($nilaiUnified)) {
                                        $nilaiDisplay = rtrim(rtrim(number_format((float)$nilaiUnified, 2, '.', ''), '0'), '.');
                                    }
                                @endphp
                                <span class="font-semibold">{{ $nilaiDisplay }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if(($n->jenis_penilaian ?? '') === 'TA')
                                    <span class="text-xs">NA: <span class="font-semibold">{{ $row['na'] ?? '-' }}</span>, Predikat: <span class="font-semibold">{{ $row['predikat'] ?? '-' }}</span></span>
                                @elseif(($n->jenis_penilaian ?? '') === 'Uji DUDI' && strtoupper((string)$formatDetail) !== 'PRAKERIN')
                                    <span class="text-xs">Nilai Akhir: <span class="font-semibold">{{ $row['nilai_akhir'] ?? '-' }}</span>, Predikat: <span class="font-semibold">{{ $row['predikat'] ?? '-' }}</span></span>
                                @elseif(strtoupper((string)$formatDetail) === 'PRAKERIN')
                                    <span class="text-xs">Rata-rata: <span class="font-semibold">{{ $nilaiDisplay }}</span></span>
                                @elseif($formatDetail === 'manual_bulk')
                                    <span class="text-xs">Nilai: <span class="font-semibold">{{ $row['nilai_utama'] ?? '-' }}</span></span>
                                @else
                                    <span class="text-xs text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex gap-2">
                                    @if(\Illuminate\Support\Facades\Route::has($group.'.nilai.show'))
                                        <a href="{{ route($group.'.nilai.show', $n->id) }}" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded">Lihat</a>
                                    @endif
                                    @if(\Illuminate\Support\Facades\Route::has($group.'.nilai.edit'))
                                        <a href="{{ route($group.'.nilai.edit', $n->id) }}" class="px-3 py-1.5 bg-blue-600 text-white hover:bg-blue-700 rounded">Edit</a>
                                    @endif
                                    @if(\Illuminate\Support\Facades\Route::has($group.'.nilai.destroy'))
                                        <form action="{{ route($group.'.nilai.destroy', $n->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="px-3 py-1.5 bg-rose-600 text-white hover:bg-rose-700 rounded"
                                                    data-confirm-delete
                                                    data-name="{{ $n->siswa->nama ?? 'nilai ini' }}"
                                                    data-title="Hapus Nilai?"
                                                    data-confirm-label="Ya, hapus"
                                                    data-cancel-label="Batal">Hapus</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            @php
                $currentPage = $penilaians->currentPage();
                $lastPage = $penilaians->lastPage();
                $makeUrl = function($page) {
                    return request()->fullUrlWithQuery(['page' => $page]);
                };
                $window = 2; // how many pages around current
                $start = max(1, $currentPage - $window);
                $end = min($lastPage, $currentPage + $window);
            @endphp
            <nav class="flex items-center justify-center gap-1" aria-label="Pagination">
                @if($currentPage > 1)
                    <a href="{{ $makeUrl($currentPage - 1) }}" class="px-3 py-1 rounded border bg-white dark:bg-zinc-800">&laquo;</a>
                @else
                    <span class="px-3 py-1 rounded border bg-gray-100 text-gray-400">&laquo;</span>
                @endif

                @if($start > 1)
                    <a href="{{ $makeUrl(1) }}" class="px-3 py-1 rounded border bg-white dark:bg-zinc-800">1</a>
                    @if($start > 2)
                        <span class="px-2">...</span>
                    @endif
                @endif

                @for($i = $start; $i <= $end; $i++)
                    @if($i == $currentPage)
                        <span class="px-3 py-1 rounded border bg-gray-200 dark:bg-zinc-700 font-semibold">{{ $i }}</span>
                    @else
                        <a href="{{ $makeUrl($i) }}" class="px-3 py-1 rounded border bg-white dark:bg-zinc-800">{{ $i }}</a>
                    @endif
                @endfor

                @if($end < $lastPage)
                    @if($end < $lastPage - 1)
                        <span class="px-2">...</span>
                    @endif
                    <a href="{{ $makeUrl($lastPage) }}" class="px-3 py-1 rounded border bg-white dark:bg-zinc-800">{{ $lastPage }}</a>
                @endif

                @if($currentPage < $lastPage)
                    <a href="{{ $makeUrl($currentPage + 1) }}" class="px-3 py-1 rounded border bg-white dark:bg-zinc-800">&raquo;</a>
                @else
                    <span class="px-3 py-1 rounded border bg-gray-100 text-gray-400">&raquo;</span>
                @endif
            </nav>
        </div>
    @else
        <div class="border bg-gray-50 dark:bg-zinc-800 rounded p-8 text-center text-gray-600">
            Belum ada data nilai.
        </div>
    @endif
</x-layouts.app>
