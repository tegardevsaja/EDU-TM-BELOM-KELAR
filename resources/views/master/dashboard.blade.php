<x-layouts.app :title="__('Master Admin Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <!-- Welcome Banner -->
        <div class="relative overflow-hidden rounded-2xl border border-zinc-200/60 dark:border-zinc-800/60 text-white">
            <div class="h-44 sm:h-56 md:h-64 w-full flex items-center bg-cover bg-center" style="background-image: url('{{ asset('img/banner.jpg') }}'); background-position: center center;">
                <div class="w-full h-full bg-gradient-to-r from-black/70 via-black/40 to-transparent flex items-center">
                    <div class="px-6 md:px-10 max-w-2xl">
                        <p class="text-xs sm:text-sm font-medium tracking-wide text-white/80 mb-1">Smart Academic Management With EDUTM CERTIFY</p>
                        <h2 class="text-xl sm:text-2xl md:text-3xl font-bold mb-2">Kelola akademik sekolah secara modern dan terintegrasi</h2>
                        <p class="text-[11px] sm:text-sm text-white/80 leading-relaxed">
                            EDU TM membantu sekolah mengelola siswa, nilai, dan sertifikat dengan mudah,
                            memberi admin dan guru cara sederhana untuk memantau perkembangan serta menjaga
                            setiap data akademik tetap rapi.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPIs (3 cards) now handled by Livewire (no page refresh) -->
        @livewire('dashboard-kpis')

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Analytics -->
            <div class="lg:col-span-2 space-y-6">
                <div class="rounded-2xl border border-zinc-200/70 dark:border-zinc-800/70 bg-white dark:bg-zinc-900 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-50">Statistik Siswa per Tahun</h3>
                    </div>
                    <canvas id="chartStudents" height="110"></canvas>
                </div>

                <div class="rounded-2xl border border-zinc-200/70 dark:border-zinc-800/70 bg-white dark:bg-zinc-900 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-50">Penilaian Terbaru</h3>
                        <a href="{{ route('master.nilai.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Lihat semua</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-zinc-500 dark:text-zinc-400 text-xs">
                                <tr>
                                    <th class="text-left py-2 pr-4">Siswa</th>
                                    <th class="text-left py-2 pr-4">NIS</th>
                                    <th class="text-left py-2 pr-4">Kelas</th>
                                    <th class="text-left py-2 pr-4">Jurusan</th>
                                    <th class="text-left py-2 pr-4">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                                @forelse($recentPenilaian as $p)
                                    <tr class="hover:bg-zinc-50/60 dark:hover:bg-zinc-800/60">
                                        <td class="py-2 pr-4 text-zinc-900 dark:text-zinc-50">{{ $p->siswa->nama ?? '-' }}</td>
                                        <td class="py-2 pr-4">{{ $p->siswa->nis ?? '-' }}</td>
                                        <td class="py-2 pr-4">{{ optional($p->siswa->kelas)->nama_kelas ?? '-' }}</td>
                                        <td class="py-2 pr-4">{{ optional($p->siswa->jurusan)->nama_jurusan ?? '-' }}</td>
                                        <td class="py-2 pr-4">{{ $p->created_at->format('d M Y') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="py-6 text-center text-zinc-500">Belum ada penilaian</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="rounded-2xl border border-zinc-200/70 dark:border-zinc-800/70 bg-white dark:bg-zinc-900 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-50">Kelas Terbaru</h3>
                        </div>
                        <div class="divide-y divide-zinc-100 dark:divide-zinc-800 text-xs text-zinc-600 dark:text-zinc-300">
                            @forelse($recentKelas as $idx => $k)
                                <div class="flex items-start gap-2 py-1.5">
                                    <div class="mt-0.5 w-5 h-5 rounded-full bg-indigo-50 dark:bg-indigo-900/40 text-[10px] flex items-center justify-center text-indigo-600 dark:text-indigo-300">
                                        {{ $idx + 1 }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-zinc-900 dark:text-zinc-50 truncate">{{ $k->nama_kelas }}</p>
                                        <p class="text-[11px] text-zinc-500">
                                            {{ optional($k->jurusan)->nama_jurusan ?? '-' }} • Dibuat {{ optional($k->created_at)->format('d M Y') }}
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <p class="py-1.5 text-[11px] text-zinc-500">Belum ada data kelas.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-2xl border border-zinc-200/70 dark:border-zinc-800/70 bg-white dark:bg-zinc-900 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-50">Siswa Terbaru</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-xs">
                                <thead class="text-zinc-500 dark:text-zinc-400 text-[11px]">
                                    <tr>
                                        <th class="text-left py-1.5 pr-3">Nama</th>
                                        <th class="text-left py-1.5 pr-3">NIS</th>
                                        <th class="text-left py-1.5 pr-3">Kelas</th>
                                        <th class="text-left py-1.5 pr-3">Terdaftar</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                    @forelse($recentSiswa as $s)
                                        <tr class="hover:bg-zinc-50/60 dark:hover:bg-zinc-800/60">
                                            <td class="py-1.5 pr-3 text-zinc-900 dark:text-zinc-50 truncate">{{ $s->nama }}</td>
                                            <td class="py-1.5 pr-3">{{ $s->nis ?? '-' }}</td>
                                            <td class="py-1.5 pr-3">{{ optional($s->kelas)->nama_kelas ?? '-' }}</td>
                                            <td class="py-1.5 pr-3">{{ optional($s->created_at)->format('d M Y') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="py-3 text-center text-[11px] text-zinc-500">Belum ada data siswa.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Side widgets -->
            <div class="space-y-6">
                <div class="rounded-2xl border border-zinc-200/70 dark:border-zinc-800/70 bg-white dark:bg-zinc-900 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-50">Progress Penilaian</h3>
                    </div>
                    <canvas id="chartProgress" height="160"></canvas>
                </div>

                <div class="rounded-2xl border border-zinc-200/70 dark:border-zinc-800/70 bg-white dark:bg-zinc-900 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-50">Pengguna</h3>
                    </div>
                    <div class="space-y-3">
                        @forelse($recentUsers as $u)
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-600 dark:text-zinc-300 text-xs font-semibold">
                                    {{ strtoupper(substr($u->name,0,1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm text-zinc-900 dark:text-zinc-50 truncate">{{ $u->name }}</p>
                                    <p class="text-xs text-zinc-500 truncate">Bergabung {{ $u->created_at->format('d M Y') }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-zinc-500">Belum ada data pengguna yang tampil.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-2xl border border-zinc-200/70 dark:border-zinc-800/70 bg-white dark:bg-zinc-900 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-50">Ringkasan Sekolah</h3>
                    </div>
                    <dl class="text-xs space-y-1 text-zinc-600 dark:text-zinc-300">
                        <div class="flex justify-between">
                            <dt>Jumlah jurusan</dt>
                            <dd class="font-semibold text-zinc-900 dark:text-zinc-50">{{ $totalJurusan }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>Jumlah kelas</dt>
                            <dd class="font-semibold text-zinc-900 dark:text-zinc-50">{{ $totalKelas }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>Tahun ajaran terdaftar</dt>
                            <dd class="font-semibold text-zinc-900 dark:text-zinc-50">{{ $totalTahunAjaran }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>Template sertifikat</dt>
                            <dd class="font-semibold text-zinc-900 dark:text-zinc-50">{{ $totalTemplateSertifikat }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-2xl border border-zinc-200/70 dark:border-zinc-800/70 bg-white dark:bg-zinc-900 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-50">Kalender</h3>
                        <div class="flex items-center gap-2 text-sm">
                            <button id="calPrev" class="px-2 py-1 border rounded">‹</button>
                            <div id="calTitle" class="min-w-[120px] text-center"></div>
                            <button id="calNext" class="px-2 py-1 border rounded">›</button>
                        </div>
                    </div>
                    <p id="calTodayLabel" class="text-[11px] text-zinc-500 dark:text-zinc-400 mb-2"></p>
                    <div class="grid grid-cols-7 gap-1 text-center text-[11px] mb-1 text-zinc-500 dark:text-zinc-400">
                        <div>Sen</div>
                        <div>Sel</div>
                        <div>Rab</div>
                        <div>Kam</div>
                        <div>Jum</div>
                        <div>Sab</div>
                        <div>Min</div>
                    </div>
                    <div id="calendarGrid" class="grid grid-cols-7 gap-1 text-center text-xs"></div>
                    <div id="calendarLegend" class="mt-2 text-[11px] text-zinc-600 dark:text-zinc-300 space-y-1"></div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const totalPerYear = {!! json_encode(($studentsPerYear ?? collect())->toArray()) !!};
            const malePerYear = {!! json_encode(($studentsPerYearMale ?? collect())->toArray()) !!};
            const femalePerYear = {!! json_encode(($studentsPerYearFemale ?? collect())->toArray()) !!};

            // Label tahun ajaran langsung dari data yang ada, diurutkan dari tahun awal terkecil
            let labels = Object.keys(totalPerYear || {});
            labels = labels.sort((a, b) => {
                const ay = parseInt((a || '').split('/')[0]);
                const by = parseInt((b || '').split('/')[0]);
                if (isNaN(ay) || isNaN(by)) return 0;
                return ay - by;
            });

            const dataTotal = labels.map(y => totalPerYear[y] ?? 0);
            const dataMale = labels.map(y => malePerYear[y] ?? 0);
            const dataFemale = labels.map(y => femalePerYear[y] ?? 0);

            // Hitung skala dinamis untuk sumbu Y berdasarkan nilai maksimum
            const maxVal = Math.max(
                ...(dataTotal.length ? dataTotal : [0]),
                ...(dataMale.length ? dataMale : [0]),
                ...(dataFemale.length ? dataFemale : [0])
            );
            const niceMax = maxVal > 0 ? Math.ceil(maxVal / 10) * 10 : 10;
            const gridLines = 5;
            const stepSize = Math.max(1, Math.round(niceMax / gridLines));
            const ctx1 = document.getElementById('chartStudents');
            if (ctx1) {
                new Chart(ctx1, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [
                            {
                                label: 'Total siswa',
                                data: dataTotal,
                                borderColor: '#8b5cf6',
                                backgroundColor: 'rgba(139,92,246,0.15)',
                                borderWidth: 3,
                                tension: .35,
                                fill: true
                            },
                            {
                                label: 'Laki-laki',
                                data: dataMale,
                                borderColor: '#0ea5e9',
                                backgroundColor: 'rgba(14,165,233,0.10)',
                                borderWidth: 2,
                                borderDash: [6, 4],
                                tension: .35,
                                fill: false
                            },
                            {
                                label: 'Perempuan',
                                data: dataFemale,
                                borderColor: '#ec4899',
                                backgroundColor: 'rgba(236,72,153,0.10)',
                                borderWidth: 2,
                                borderDash: [4, 4],
                                tension: .35,
                                fill: false
                            }
                        ]
                    },
                    options: {
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom',
                                labels: {
                                    boxWidth: 10,
                                    boxHeight: 10,
                                    color: '#6b7280',
                                    font: { size: 11 }
                                }
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                callbacks: {
                                    title: (items) => {
                                        const idx = items[0]?.dataIndex ?? 0;
                                        const raw = labels[idx] ?? '';
                                        return raw;
                                    },
                                    label: (ctx) => `${ctx.dataset.label}: ${ctx.formattedValue} siswa`
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                suggestedMax: niceMax,
                                ticks: {
                                    stepSize: stepSize,
                                    precision: 0,
                                    color: '#9ca3af',
                                    font: { size: 10 }
                                },
                                grid: {
                                    color: 'rgba(148,163,184,0.25)',
                                    drawBorder: false
                                }
                            },
                            x: {
                                ticks: {
                                    color: '#9ca3af',
                                    font: { size: 10 },
                                    callback: (value) => {
                                        // value = index for category scale
                                        const raw = labels[value] ?? '';
                                        return raw;
                                    }
                                },
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }

            // Plugin untuk menampilkan teks di tengah donut
            const centerTextPlugin = {
                id: 'centerText',
                beforeDraw(chart, args, options) {
                    if (!options?.text) return;
                    const {ctx, chartArea: {width, height}} = chart;
                    ctx.save();
                    ctx.font = options.font || '600 14px system-ui';
                    ctx.fillStyle = options.color || '#111827';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(options.text, width / 2, height / 2);
                    ctx.restore();
                }
            };

            Chart.register(centerTextPlugin);

            // Toggle menu filter Sertifikat Tercetak (titik tiga)
            const certToggle = document.getElementById('certFilterToggle');
            const certMenu = document.getElementById('certFilterMenu');
            if (certToggle && certMenu) {
                certToggle.addEventListener('click', (e) => {
                    e.stopPropagation();
                    certMenu.classList.toggle('hidden');
                });

                document.addEventListener('click', (e) => {
                    if (!certMenu.classList.contains('hidden') && !certMenu.contains(e.target)) {
                        certMenu.classList.add('hidden');
                    }
                });
            }

            // Toggle menu filter Total Siswa (gender)
            const siswaToggle = document.getElementById('siswaFilterToggle');
            const siswaMenu = document.getElementById('siswaFilterMenu');
            if (siswaToggle && siswaMenu) {
                siswaToggle.addEventListener('click', (e) => {
                    e.stopPropagation();
                    siswaMenu.classList.toggle('hidden');
                });

                document.addEventListener('click', (e) => {
                    if (!siswaMenu.classList.contains('hidden') && !siswaMenu.contains(e.target) && e.target !== siswaToggle) {
                        siswaMenu.classList.add('hidden');
                    }
                });
            }

            // Toggle menu filter Total Pengguna (role)
            const userToggle = document.getElementById('userFilterToggle');
            const userMenu = document.getElementById('userFilterMenu');
            if (userToggle && userMenu) {
                userToggle.addEventListener('click', (e) => {
                    e.stopPropagation();
                    userMenu.classList.toggle('hidden');
                });

                document.addEventListener('click', (e) => {
                    if (!userMenu.classList.contains('hidden') && !userMenu.contains(e.target) && e.target !== userToggle) {
                        userMenu.classList.add('hidden');
                    }
                });
            }

            const ctx2 = document.getElementById('chartProgress');
            if (ctx2) {
                new Chart(ctx2, {
                    type: 'doughnut',
                    data: {
                        labels: ['Sudah dinilai','Belum'],
                        datasets: [{
                            data: [{{ $percentGraded }}, {{ max(0, 100 - $percentGraded) }}],
                            backgroundColor: ['#22c55e','#e5e7eb']
                        }]
                    },
                    options: {
                        plugins: {
                            legend: { position: 'bottom' },
                            centerText: {
                                text: '{{ $percentGraded }}%',
                                color: '#16a34a',
                                font: '700 18px system-ui'
                            }
                        },
                        cutout: '70%'
                    }
                });
            }

            // Cache libur per tahun (diisi dari API api-harilibur.vercel.app)
            const holidaysByYear = {};

            async function loadHolidays(year) {
                if (holidaysByYear[year]) return holidaysByYear[year];
                try {
                    const resp = await fetch(`https://api-harilibur.vercel.app/api?year=${year}`);
                    if (!resp.ok) throw new Error('Gagal memuat data hari libur');
                    const data = await resp.json();
                    const map = {};
                    const national = new Set();
                    data.forEach(item => {
                        // Normalisasi tanggal ke YYYY-MM-DD (kadang tanpa nol di bulan/tanggal)
                        let iso = String(item.holiday_date || '').trim();
                        if (!iso) return;
                        const parts = iso.split('-');
                        if (parts.length === 3) {
                            const y = parts[0];
                            const m = String(parts[1]).padStart(2,'0');
                            const d = String(parts[2]).padStart(2,'0');
                            iso = `${y}-${m}-${d}`;
                        }
                        map[iso] = item.holiday_name || '';
                        if (item.is_national_holiday) national.add(iso);
                    });
                    holidaysByYear[year] = { map, national };
                    return holidaysByYear[year];
                } catch (e) {
                    console.error('Error memuat hari libur:', e);
                    holidaysByYear[year] = { map: {}, national: new Set() };
                    return holidaysByYear[year];
                }
            }

            let calDate = new Date();
            async function renderCalendar(d){
                const grid = document.getElementById('calendarGrid');
                const title = document.getElementById('calTitle');
                const legend = document.getElementById('calendarLegend');
                const todayLabel = document.getElementById('calTodayLabel');
                if(!grid||!title||!legend) return;
                grid.innerHTML = '';
                legend.innerHTML = '';

                const y = d.getFullYear(), m = d.getMonth();

                // Pastikan data libur tahun ini sudah dimuat
                const { map: indoHolidays, national: nationalHolidayDates } = await loadHolidays(y);
                title.textContent = d.toLocaleString('id-ID', { month: 'long', year: 'numeric' });
                const first = new Date(y,m,1); const last = new Date(y,m+1,0);
                const start = first.getDay() === 0 ? 7 : first.getDay();
                const today = new Date();
                const daysInMonth = last.getDate();

                if (todayLabel) {
                    todayLabel.textContent = 'Hari ini: ' + today.toLocaleDateString('id-ID', {
                        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
                    });
                }

                // Kumpulkan libur pada bulan yang sedang ditampilkan
                const monthStr = String(m+1).padStart(2,'0');
                const holidayEntries = [];

                for (const [iso, name] of Object.entries(indoHolidays)) {
                    if (iso.startsWith(y + '-' + monthStr)) {
                        const day = parseInt(iso.split('-')[2], 10);
                        holidayEntries.push({ day, name, iso });
                    }
                }

                for(let i=1;i<start;i++){
                    const cell=document.createElement('div');
                    cell.className='py-1 text-zinc-400';
                    cell.textContent='';
                    grid.appendChild(cell);
                }

                for(let dnum=1; dnum<=daysInMonth; dnum++){
                    const cell=document.createElement('div');
                    const isToday = dnum===today.getDate() && m===today.getMonth() && y===today.getFullYear();
                    const iso = `${y}-${String(m+1).padStart(2,'0')}-${String(dnum).padStart(2,'0')}`;
                    const holidayName = indoHolidays[iso] ?? null;
                    const isNationalHoliday = holidayName && nationalHolidayDates.has(iso);
                    const dayObj = new Date(y, m, dnum);
                    const dow = dayObj.getDay(); // 0 = Minggu, 6 = Sabtu

                    let baseClass = 'py-1 rounded border ';
                    if (dow === 0) { // Hanya Minggu
                        baseClass += 'border-red-400 ';
                    } else {
                        baseClass += 'border-transparent ';
                    }
                    if (isNationalHoliday) {
                        // Libur nasional: merah jelas
                        baseClass += 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300 font-semibold ';
                    } else if (holidayName) {
                        // Hari penting non-nasional: tandai lebih halus
                        baseClass += 'text-blue-600 dark:text-blue-300 font-semibold ';
                    } else if (isToday) {
                        baseClass += 'bg-indigo-600 text-white ring-2 ring-indigo-400 ';
                    } else {
                        baseClass += 'hover:bg-zinc-100 dark:hover:bg-zinc-800 ';
                    }

                    cell.className = baseClass;
                    cell.textContent = dnum;
                    grid.appendChild(cell);
                }

                if (holidayEntries.length) {
                    const heading = document.createElement('div');
                    heading.textContent = 'Hari libur bulan ini:';
                    heading.className = 'font-semibold';
                    legend.appendChild(heading);

                    holidayEntries.sort((a,b) => a.day - b.day).forEach(h => {
                        const row = document.createElement('div');
                        row.textContent = `${h.day} - ${h.name}`;
                        legend.appendChild(row);
                    });
                } else {
                    const row = document.createElement('div');
                    row.textContent = 'Tidak ada hari libur nasional pada bulan ini (data lokal).';
                    legend.appendChild(row);
                }
            }
            renderCalendar(calDate);
            const prev=document.getElementById('calPrev'); const next=document.getElementById('calNext');
            if(prev){ prev.addEventListener('click', ()=>{ calDate.setMonth(calDate.getMonth()-1); renderCalendar(calDate); }); }
            if(next){ next.addEventListener('click', ()=>{ calDate.setMonth(calDate.getMonth()+1); renderCalendar(calDate); }); }
        </script>
    </div>
</x-layouts.app>
