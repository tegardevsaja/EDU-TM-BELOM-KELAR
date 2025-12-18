<x-layouts.admin :title="__('Admin Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <!-- Banner seperti Master -->
        <div class="relative overflow-hidden rounded-2xl border border-zinc-200/60 dark:border-zinc-800/60 text-white">
            <div class="h-44 sm:h-56 md:h-64 w-full flex items-center bg-cover bg-center" style="background-image: url('{{ asset('img/banner.jpg') }}'); background-position: center center;">
                <div class="w-full h-full bg-gradient-to-r from-black/70 via-black/40 to-transparent flex items-center">
                    <div class="px-6 md:px-10 max-w-2xl">
                        <p class="text-xs sm:text-sm font-medium tracking-wide text-white/80 mb-1">EDU TM — Dashboard Admin</p>
                        <h2 class="text-xl sm:text-2xl md:text-3xl font-bold mb-2">Ringkasan cepat aktivitas akademik</h2>
                        <p class="text-[11px] sm:text-sm text-white/80">Kelola siswa, nilai, dan sertifikat dengan cepat.</p>
                    </div>
                </div>
            </div>
        </div>

        @php
            $sg = request('siswa_gender', 'all');
            if (class_exists(\App\Models\Siswa::class)) {
                if (in_array($sg, ['L','P'])) { $totalSiswa = \App\Models\Siswa::where('jenis_kelamin',$sg)->count(); }
                else { $totalSiswa = \App\Models\Siswa::count(); }
            } else { $totalSiswa = 30; }

            // Total kelas berdasarkan jurusan (filter seperti di dashboard guru)
            $kelasJurusanId = request('kelas_jurusan', 'all');
            $jurusanList = class_exists(\App\Models\Jurusan::class)
                ? \App\Models\Jurusan::orderBy('nama_jurusan')->get(['id','nama_jurusan'])
                : collect();
            if (class_exists(\App\Models\Kelas::class)) {
                $kelasQuery = \App\Models\Kelas::query();
                if ($kelasJurusanId !== 'all' && $kelasJurusanId !== null && $kelasJurusanId !== '') {
                    $kelasQuery->where('jurusan_id', (int)$kelasJurusanId);
                }
                $totalKelas = $kelasQuery->count();
            } else { $totalKelas = 0; }

            // Sertifikat tercetak dengan filter rentang waktu
            $cr = request('cert_range', 'all');
            if (class_exists(\App\Models\CertificateHistory::class)) {
                $q = \App\Models\CertificateHistory::query();
                if ($cr === 'week') $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                elseif ($cr === 'month') $q->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                elseif ($cr === 'year') $q->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]);
                $totalCert = (int) $q->sum('jumlah_siswa');
            } else { $totalCert = 515; }
        @endphp

        <!-- 3 KPI Cards -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="relative rounded-xl bg-white dark:bg-zinc-800 p-5 border border-gray-200 dark:border-zinc-700" x-data="{open:false}" @click.outside="open=false">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total Siswa</p>
                        <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $totalSiswa }}</p>
                    </div>
                    <div class="relative">
                        <button class="text-xl leading-none" @click="open=!open">⋯</button>
                        <div x-show="open" class="absolute mt-2 right-0 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded shadow-md z-40">
                            <a class="block px-3 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700" href="{{ request()->fullUrlWithQuery(['siswa_gender'=>'all']) }}">Semua</a>
                            <a class="block px-3 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700" href="{{ request()->fullUrlWithQuery(['siswa_gender'=>'L']) }}">Laki-laki</a>
                            <a class="block px-3 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700" href="{{ request()->fullUrlWithQuery(['siswa_gender'=>'P']) }}">Perempuan</a>
                        </div>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-1">{{ $sg==='L' ? 'Laki-laki' : ($sg==='P' ? 'Perempuan' : 'Semua') }}</p>
            </div>
            <div class="relative rounded-xl bg-white dark:bg-zinc-800 p-5 border border-gray-200 dark:border-zinc-700" x-data="{open:false}" @click.outside="open=false">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total Kelas</p>
                        <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $totalKelas }}</p>
                    </div>
                    <div class="relative">
                        <button class="text-xl leading-none" @click="open=!open">⋯</button>
                        <div x-cloak x-show="open" class="absolute mt-2 right-0 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded shadow-md z-40 min-w-[180px] max-h-64 overflow-y-auto text-sm">
                            <a href="{{ request()->fullUrlWithQuery(['kelas_jurusan' => 'all']) }}" class="block px-3 py-2 hover:bg-zinc-50 dark:hover:bg-zinc-700 {{ request('kelas_jurusan','all')==='all' ? 'font-semibold' : '' }}">Semua jurusan</a>
                            @foreach($jurusanList as $j)
                                <a href="{{ request()->fullUrlWithQuery(['kelas_jurusan' => $j->id]) }}" class="block px-3 py-1.5 hover:bg-zinc-50 dark:hover:bg-zinc-700 {{ (string)request('kelas_jurusan','all')===(string)$j->id ? 'font-semibold' : '' }}">{{ $j->nama_jurusan }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
                @php
                    $selectedJurusanId = request('kelas_jurusan','all');
                    if ($selectedJurusanId === 'all' || !$jurusanList->count()) {
                        $kelasLabel = 'Semua jurusan';
                    } else {
                        $kelasLabel = optional($jurusanList->firstWhere('id', (int)$selectedJurusanId))->nama_jurusan ?? 'Semua jurusan';
                    }
                @endphp
                <p class="text-xs text-gray-500 mt-1">{{ $kelasLabel }}</p>
            </div>
            <div class="relative rounded-xl bg-white dark:bg-zinc-800 p-5 border border-gray-200 dark:border-zinc-700" x-data="{open:false}" @click.outside="open=false">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Sertifikat Tercetak</p>
                        <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $totalCert }}</p>
                    </div>
                    <div class="relative">
                        <button class="text-xl leading-none" @click="open=!open">⋯</button>
                        @php $cr = request('cert_range','all'); @endphp
                        <div x-cloak x-show="open" class="absolute mt-2 right-0 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-lg z-40 min-w-[180px] text-sm overflow-hidden">
                            <div class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400 border-b border-zinc-100 dark:border-zinc-700">Filter waktu</div>
                            <a href="{{ request()->fullUrlWithQuery(['cert_range'=>'all']) }}" class="flex items-center justify-between px-3 py-2 hover:bg-zinc-50 dark:hover:bg-zinc-700 {{ $cr==='all' ? 'bg-zinc-50 dark:bg-zinc-700 font-semibold' : '' }}">
                                <span>Semua waktu</span>
                                @if($cr==='all')<span class="text-xs">✓</span>@endif
                            </a>
                            <a href="{{ request()->fullUrlWithQuery(['cert_range'=>'week']) }}" class="flex items-center justify-between px-3 py-2 hover:bg-zinc-50 dark:hover:bg-zinc-700 {{ $cr==='week' ? 'bg-zinc-50 dark:bg-zinc-700 font-semibold' : '' }}">
                                <span>Minggu ini</span>
                                @if($cr==='week')<span class="text-xs">✓</span>@endif
                            </a>
                            <a href="{{ request()->fullUrlWithQuery(['cert_range'=>'month']) }}" class="flex items-center justify-between px-3 py-2 hover:bg-zinc-50 dark:hover:bg-zinc-700 {{ $cr==='month' ? 'bg-zinc-50 dark:bg-zinc-700 font-semibold' : '' }}">
                                <span>Bulan ini</span>
                                @if($cr==='month')<span class="text-xs">✓</span>@endif
                            </a>
                            <a href="{{ request()->fullUrlWithQuery(['cert_range'=>'year']) }}" class="flex items-center justify-between px-3 py-2 hover:bg-zinc-50 dark:hover:bg-zinc-700 {{ $cr==='year' ? 'bg-zinc-50 dark:bg-zinc-700 font-semibold' : '' }}">
                                <span>Tahun ini</span>
                                @if($cr==='year')<span class="text-xs">✓</span>@endif
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php
            // Sertifikat tercetak per-bulan (12 bulan terakhir)
            $months = collect(range(0,11))->map(function($i){
                return now()->subMonths(11 - $i)->format('Y-m');
            });
            $certData = \App\Models\CertificateHistory::select(\Illuminate\Support\Facades\DB::raw('DATE_FORMAT(created_at, "%Y-%m") as ym'), \Illuminate\Support\Facades\DB::raw('SUM(jumlah_siswa) as total'))
                ->where('created_at', '>=', now()->subMonths(12)->startOfMonth())
                ->groupBy('ym')
                ->pluck('total','ym');
            $labels = $months->map(fn($ym)=>\Carbon\Carbon::createFromFormat('Y-m',$ym)->translatedFormat('M Y'));
            $values = $months->map(fn($ym)=> (int) ($certData[$ym] ?? 0));
        @endphp

        <div class="rounded-2xl border border-zinc-200/70 dark:border-zinc-800/70 bg-white dark:bg-zinc-900 p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-50">Grafik Sertifikat Tercetak (12 bulan)</h3>
            </div>
            <canvas id="chartCertAdmin" height="110"></canvas>
        </div>

        @php
            // Grafik Total Siswa per Kelas (filter gender sama dengan KPI: $sg)
            $kelasLabels = collect();
            $kelasValues = collect();
            if (class_exists(\App\Models\Kelas::class) && class_exists(\App\Models\Siswa::class)) {
                try {
                    $kelasWithCounts = \App\Models\Kelas::orderBy('nama_kelas')
                        ->get(['id','nama_kelas']);
                    $kelasLabels = $kelasWithCounts->pluck('nama_kelas');
                    $kelasValues = $kelasWithCounts->map(function($k) use ($sg){
                        $q = \App\Models\Siswa::where('kelas_id', $k->id);
                        if (in_array($sg, ['L','P'])) { $q->where('jenis_kelamin', $sg); }
                        return (int) $q->count();
                    });
                } catch (\Throwable $e) {
                    $kelasLabels = collect(['X-A','X-B','XI-A','XI-B']);
                    $kelasValues = collect([30,28,26,24]);
                }
            }
        @endphp

        <div class="rounded-2xl border border-zinc-200/70 dark:border-zinc-800/70 bg-white dark:bg-zinc-900 p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-50">Grafik Total Siswa per Kelas</h3>
                <div class="text-xs text-gray-500">Filter: {{ $sg==='L' ? 'Laki-laki' : ($sg==='P' ? 'Perempuan' : 'Semua') }}</div>
            </div>
            <canvas id="chartSiswaKelas" height="120"></canvas>
        </div>

        @php
            // Kalender bulan berjalan
            $calYm = request('cal', now()->format('Y-m'));
            try { $calStart = \Carbon\Carbon::createFromFormat('Y-m', $calYm)->startOfMonth(); }
            catch (\Throwable $e) { $calStart = now()->startOfMonth(); $calYm = $calStart->format('Y-m'); }
            $startWeek = (clone $calStart)->startOfWeek();
            $endWeek = (clone $calStart)->endOfMonth()->endOfWeek();
            $weeks = [];
            $cursor = $startWeek->copy();
            while ($cursor->lte($endWeek)) {
                $week = [];
                for ($i=0;$i<7;$i++) { $week[] = $cursor->copy(); $cursor->addDay(); }
                $weeks[] = $week;
            }
            $prevYm = (clone $calStart)->subMonth()->format('Y-m');
            $nextYm = (clone $calStart)->addMonth()->format('Y-m');
        @endphp

        <div class="grid gap-4 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-4">
                <div class="rounded-2xl border border-zinc-200/70 dark:border-zinc-800/70 bg-white dark:bg-zinc-900 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-base font-semibold">Siswa Terbaru</h3>
                        <a href="{{ route('admin.siswa.index') }}" class="text-sm text-blue-600">Lihat semua</a>
                    </div>
                    <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @php
                            $latestSiswa = class_exists(\App\Models\Siswa::class)
                                ? \App\Models\Siswa::orderBy('created_at','desc')->limit(5)->get(['nama','nis','jenis_kelamin','created_at'])
                                : collect();
                        @endphp
                        @forelse($latestSiswa as $s)
                            <div class="flex items-center justify-between py-2">
                                <div>
                                    <div class="font-medium">{{ $s->nama }}</div>
                                    <div class="text-xs text-gray-500">NIS: {{ $s->nis }} • {{ $s->jenis_kelamin === 'L' ? 'Laki-laki' : ($s->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}</div>
                                </div>
                                <div class="text-xs text-gray-500">{{ optional($s->created_at)->diffForHumans() }}</div>
                            </div>
                        @empty
                            <div class="py-6 text-center text-gray-500">Belum ada data siswa.</div>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-zinc-200/70 dark:border-zinc-800/70 bg-white dark:bg-zinc-900 p-4">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-base font-semibold">Kalender {{ $calStart->translatedFormat('F Y') }}</h3>
                        <p class="inline-flex items-center mt-0.5 px-2 py-0.5 rounded-full border border-blue-400 bg-blue-50 text-[11px] font-medium text-blue-700 dark:border-blue-500 dark:bg-blue-900/30 dark:text-blue-200">
                            Hari ini: {{ now()->translatedFormat('d M Y') }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <a class="px-2 py-1 rounded border" href="{{ request()->fullUrlWithQuery(['cal'=>$prevYm]) }}">&larr;</a>
                        <a class="px-2 py-1 rounded border" href="{{ request()->fullUrlWithQuery(['cal'=>now()->format('Y-m')]) }}">Today</a>
                        <a class="px-2 py-1 rounded border" href="{{ request()->fullUrlWithQuery(['cal'=>$nextYm]) }}">&rarr;</a>
                    </div>
                </div>
                <div class="grid grid-cols-7 text-center text-xs text-gray-500 mb-2">
                    <div>Sen</div><div>Sel</div><div>Rab</div><div>Kam</div><div>Jum</div><div>Sab</div><div>Min</div>
                </div>
                <div class="grid grid-cols-7 gap-2">
                    @foreach($weeks as $week)
                        @foreach($week as $d)
                            @php
                                $isToday = $d->isToday();
                                // Hari di luar bulan aktif dimute, KECUALI jika itu hari ini (tetap terang)
                                $muted = !$d->isSameMonth($calStart) && !$isToday;
                            @endphp
                            <div class="rounded border text-sm p-2 {{ $muted ? 'opacity-40' : '' }} {{ $isToday ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/40' : 'border-zinc-200 dark:border-zinc-700' }} flex items-start justify-end" style="aspect-ratio:1/1;">
                                <div class="text-right text-xs {{ $isToday ? 'font-semibold text-blue-700 dark:text-blue-300' : '' }}">{{ $d->format('j') }}</div>
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            (function(){
                const ctx = document.getElementById('chartCertAdmin');
                if (!ctx) return;
                const labels = {!! json_encode($labels->toArray()) !!};
                const data = {!! json_encode($values->toArray()) !!};
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Tercetak',
                            data,
                            backgroundColor: 'rgba(59,130,246,0.40)',
                            borderColor: '#3b82f6',
                            borderWidth: 1,
                            maxBarThickness: 24
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0 }
                            }
                        },
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            })();
            (function(){
                const ctx = document.getElementById('chartSiswaKelas');
                if (!ctx) return;
                const labels = {!! json_encode($kelasLabels->toArray()) !!};
                const data = {!! json_encode($kelasValues->toArray()) !!};
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Siswa',
                            data,
                            backgroundColor: 'rgba(16,185,129,0.35)',
                            borderColor: '#10b981',
                            borderWidth: 1,
                            maxBarThickness: 22
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                        plugins: { legend: { display: false } }
                    }
                });
            })();
        </script>
    </div>
</x-layouts.admin>
