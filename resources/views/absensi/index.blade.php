<x-layouts.app :title="__('Absensi')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="rounded-2xl border border-zinc-200/70 dark:border-zinc-800/70 bg-white dark:bg-zinc-900 p-4">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-50">Absensi per Kelas</h3>
                </div>
                @php
                    $base = request()->routeIs('admin.*') ? 'admin' : (request()->routeIs('guru.*') ? 'guru' : 'master');
                @endphp
                <div class="flex items-center gap-2">
                    <a href="{{ route($base.'.absensi.create') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">Buat Sesi</a>
                </div>
            </div>

            <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-3 mb-4">
                <div>
                    <label class="block text-xs text-zinc-500 mb-1">Kelas</label>
                    <select name="kelas_id" class="w-full rounded-md border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-sm">
                        <option value="all">Semua</option>
                        @foreach(($kelas ?? ($kelasList ?? collect())) as $k)
                            <option value="{{ $k->id }}" @selected(request('kelas_id')==$k->id)>{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-zinc-500 mb-1">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="w-full rounded-md border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-sm" />
                </div>
                <div>
                    <label class="block text-xs text-zinc-500 mb-1">Bulan Rekap</label>
                    <input type="month" name="month" value="{{ request('month') ?? now()->format('Y-m') }}" class="w-full rounded-md border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-sm" />
                </div>
                <div>
                    <label class="block text-xs text-zinc-500 mb-1">Tahun Rekap</label>
                    <input type="number" name="year" min="2000" max="2100" value="{{ request('year') ?? now()->year }}" class="w-full rounded-md border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-sm" />
                </div>
                <div>
                    <label class="block text-xs text-zinc-500 mb-1">Mingguan: Mulai</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full rounded-md border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-sm" />
                </div>
                <div>
                    <label class="block text-xs text-zinc-500 mb-1">Mingguan: Akhir</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full rounded-md border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-sm" />
                </div>
                <div class="flex items-end gap-2 flex-wrap">
                    <button class="inline-flex items-center rounded-md bg-zinc-100 dark:bg-zinc-800 px-3 py-2 text-sm font-medium text-zinc-900 dark:text-zinc-100 border border-zinc-200 dark:border-zinc-700">Filter</button>
                    @if(($kelasId ?? request('kelas_id')) && (request('month') || request('tanggal')))
                        @php
                            $monthVal = request('month') ?? (request('tanggal') ? \Illuminate\Support\Carbon::parse(request('tanggal'))->format('Y-m') : now()->format('Y-m'));
                        @endphp
                        <a href="{{ route($base.'.absensi.export-monthly', ['kelas_id' => $kelasId ?? request('kelas_id'), 'month' => $monthVal, 'format' => request('format','xlsx'), 'detail' => request('detail')]) }}" class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700">Export Bulanan</a>
                    @endif
                    @if(($kelasId ?? request('kelas_id')) && request('start_date') && request('end_date'))
                        <a href="{{ route($base.'.absensi.export-weekly', ['kelas_id' => $kelasId ?? request('kelas_id'), 'start_date' => request('start_date'), 'end_date' => request('end_date'), 'format' => request('format','xlsx'), 'detail' => request('detail')]) }}" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">Export Mingguan</a>
                    @endif
                    @if(($kelasId ?? request('kelas_id')) && request('year'))
                        <a href="{{ route($base.'.absensi.export-yearly', ['kelas_id' => $kelasId ?? request('kelas_id'), 'year' => request('year'), 'format' => request('format','xlsx'), 'detail' => request('detail')]) }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">Export Tahunan</a>
                    @endif
                    @if(request()->routeIs('master.*') || request()->routeIs('admin.*'))
                        @php
                            $periodType = request('start_date') && request('end_date') ? 'range' : (request('month') ? 'month' : (request('year') ? 'year' : null));
                        @endphp
                        @if($periodType)
                            <a href="{{ route($base.'.absensi.export-school', [
                                'period_type' => $periodType,
                                'period_value' => $periodType==='month' ? ($monthVal ?? now()->format('Y-m')) : ($periodType==='year' ? request('year') : null),
                                'start_date' => $periodType!=='month' && $periodType!=='year' ? request('start_date') : null,
                                'end_date' => $periodType!=='month' && $periodType!=='year' ? request('end_date') : null,
                                'format' => request('format','xlsx'),
                                'detail' => request('detail')
                            ]) }}" class="inline-flex items-center rounded-md bg-teal-600 px-3 py-2 text-sm font-medium text-white hover:bg-teal-700">Export Sekolah</a>
                        @endif
                    @endif
                    <label class="ml-2 inline-flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-200">
                        <input type="checkbox" name="detail" value="1" @checked(request('detail')) class="rounded border-zinc-300 dark:border-zinc-700"> Detail
                    </label>
                    <select name="format" class="ml-2 rounded-md border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-sm">
                        <option value="xlsx" @selected(request('format','xlsx')==='xlsx')>XLSX</option>
                        <option value="csv" @selected(request('format')==='csv')>CSV</option>
                    </select>
                </div>
            </form>

            @if(isset($sessions) && method_exists($sessions, 'count') && $sessions->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-zinc-500">
                            <tr>
                                <th class="text-left py-2 pr-3">Tanggal</th>
                                <th class="text-left py-2 pr-3">Kelas</th>
                                <th class="text-left py-2 pr-3">Keterangan</th>
                                <th class="text-left py-2 pr-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-zinc-800 dark:text-zinc-200">
                            @foreach($sessions as $s)
                            <tr class="border-t border-zinc-200 dark:border-zinc-800">
                                <td class="py-2 pr-3">{{ \Illuminate\Support\Carbon::parse($s->tanggal)->format('d/m/Y') }}</td>
                                <td class="py-2 pr-3">{{ $s->kelas->nama_kelas ?? '-' }}</td>
                                <td class="py-2 pr-3">{{ $s->keterangan ?? '-' }}</td>
                                <td class="py-2 pr-3">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route($base.'.absensi.edit', $s->id) }}" class="inline-flex items-center rounded-md bg-yellow-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-400">
                                            Kelola
                                        </a>
                                        <form action="{{ route($base.'.absensi.destroy', $s->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="inline-flex items-center rounded-md bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-400"
                                                    data-confirm-delete
                                                    data-name="{{ $s->kelas->nama_kelas ?? 'sesi ini' }} ({{ \Illuminate\Support\Carbon::parse($s->tanggal)->format('d/m/Y') }})"
                                                    data-title="Hapus Sesi Absensi?"
                                                    data-confirm-label="Ya, hapus"
                                                    data-cancel-label="Batal">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(method_exists($sessions, 'links'))
                    <div class="mt-3">{{ $sessions->links() }}</div>
                @endif
            @else
                <div class="rounded-md border border-zinc-200 dark:border-zinc-800 p-6 text-sm text-zinc-500">Belum ada sesi absensi.</div>
            @endif
        </div>
    </div>
</x-layouts.app>
