<x-layouts.app :title="__('Guru Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <!-- Banner seperti Master -->
        <div class="relative overflow-hidden rounded-2xl border border-zinc-200/60 dark:border-zinc-800/60 text-white">
            <div class="h-44 sm:h-56 md:h-64 w-full flex items-center bg-cover bg-center" style="background-image: url('{{ asset('img/banner.jpg') }}'); background-position: center center;">
                <div class="w-full h-full bg-gradient-to-r from-black/70 via-black/40 to-transparent flex items-center">
                    <div class="px-6 md:px-10 max-w-2xl">
                        <p class="text-xs sm:text-sm font-medium tracking-wide text-white/80 mb-1">EDU TM — Dashboard Guru</p>
                        <h2 class="text-xl sm:text-2xl md:text-3xl font-bold mb-2">Ringkasan cepat aktivitas akademik</h2>
                        <p class="text-[11px] sm:text-sm text-white/80">Akses data siswa, nilai dan sertifikat dengan cepat.</p>
                    </div>
                </div>
            </div>
        </div>

        @php
            $totalSiswa = class_exists(\App\Models\Siswa::class) ? \App\Models\Siswa::count() : 30;

            // Total kelas berdasarkan jurusan (RPL, TKJ, dst.)
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
            } else {
                $totalKelas = 0;
            }

            // Filter sertifikat tercetak berdasarkan rentang waktu
            $certRange = request('cert_range', 'all');
            if (class_exists(\App\Models\CertificateHistory::class)) {
                $certQuery = \App\Models\CertificateHistory::query();
                if ($certRange === 'week') {
                    $certQuery->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                } elseif ($certRange === 'month') {
                    $certQuery->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month);
                } elseif ($certRange === 'year') {
                    $certQuery->whereYear('created_at', now()->year);
                }
                $totalCert = (int) $certQuery->sum('jumlah_siswa');
            } else {
                $totalCert = 515;
            }
        @endphp

        @php
            $sg = request('siswa_gender', 'all');
            if (class_exists(\App\Models\Siswa::class)) {
                if (in_array($sg, ['L','P'])) { $totalSiswa = \App\Models\Siswa::where('jenis_kelamin',$sg)->count(); }
                else { $totalSiswa = \App\Models\Siswa::count(); }
            }
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
                    $kelasJurusanId = request('kelas_jurusan','all');
                    if ($kelasJurusanId === 'all' || !$jurusanList->count()) {
                        $kelasLabel = 'Semua jurusan';
                    } else {
                        $kelasLabel = optional($jurusanList->firstWhere('id', (int)$kelasJurusanId))->nama_jurusan ?? 'Semua jurusan';
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
                        <div x-cloak x-show="open" class="absolute mt-2 right-0 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded shadow-md z-40 min-w-[160px]">
                            <a href="{{ request()->fullUrlWithQuery(['cert_range' => 'week']) }}" class="block px-3 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700 {{ request('cert_range','all')==='week' ? 'font-semibold' : '' }}">Minggu ini</a>
                            <a href="{{ request()->fullUrlWithQuery(['cert_range' => 'month']) }}" class="block px-3 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700 {{ request('cert_range','all')==='month' ? 'font-semibold' : '' }}">Bulan ini</a>
                            <a href="{{ request()->fullUrlWithQuery(['cert_range' => 'year']) }}" class="block px-3 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700 {{ request('cert_range','all')==='year' ? 'font-semibold' : '' }}">Tahun ini</a>
                            <a href="{{ request()->fullUrlWithQuery(['cert_range' => 'all']) }}" class="block px-3 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700 {{ request('cert_range','all')==='all' ? 'font-semibold' : '' }}">Semua waktu</a>
                        </div>
                    </div>
                </div>
                @php
                    $certLabel = match(request('cert_range','all')) {
                        'week' => 'Minggu ini',
                        'month' => 'Bulan ini',
                        'year' => 'Tahun ini',
                        default => 'Semua waktu',
                    };
                @endphp
                <p class="text-xs text-gray-500 mt-1">{{ $certLabel }}</p>
            </div>
        </div>

        @php
            $myClasses = [];
            $myStudents = collect();
            if (auth()->check()) {
                $myClasses = \App\Models\Kelas::with('siswas')
                    ->where('wali_kelas_id', auth()->id())
                    ->get();
                $classIds = $myClasses->pluck('id');
                if ($classIds->isNotEmpty()) {
                    $myStudents = \App\Models\Siswa::with('kelas')
                        ->whereIn('kelas_id', $classIds)
                        ->orderBy('nama')
                        ->limit(10)
                        ->get();
                }
            }
        @endphp

        <div class="rounded-2xl border border-zinc-200/70 dark:border-zinc-800/70 bg-white dark:bg-zinc-900 p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-50">Siswa Kelas Saya</h3>
            </div>
            @if($myStudents->isEmpty())
                <p class="text-sm text-zinc-500">Belum ada kelas yang diampu atau tidak ada data siswa.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-zinc-500 dark:text-zinc-400 text-xs">
                            <tr>
                                <th class="text-left py-2 pr-4">Nama</th>
                                <th class="text-left py-2 pr-4">NIS</th>
                                <th class="text-left py-2 pr-4">Kelas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @foreach($myStudents as $s)
                                <tr class="hover:bg-zinc-50/60 dark:hover:bg-zinc-800/60">
                                    <td class="py-2 pr-4 text-zinc-900 dark:text-zinc-50">{{ $s->nama }}</td>
                                    <td class="py-2 pr-4">{{ $s->nis ?? '-' }}</td>
                                    <td class="py-2 pr-4">{{ optional($s->kelas)->nama_kelas ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
