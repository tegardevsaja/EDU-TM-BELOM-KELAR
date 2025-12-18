<x-layouts.app :title="__('Kelola Absensi')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="rounded-2xl border border-zinc-200/70 dark:border-zinc-800/70 bg-white dark:bg-zinc-900 p-4">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-50">Kelola Absensi</h3>
                    <p class="text-xs text-zinc-500 mt-1">Tanggal: {{ \Illuminate\Support\Carbon::parse($session->tanggal)->format('d/m/Y') }} · Kelas: {{ $session->kelas->nama_kelas ?? '-' }}</p>
                </div>
                <div class="text-xs {{ $session->locked ? 'text-red-500' : 'text-emerald-600' }}">{{ $session->locked ? 'Terkunci' : 'Terbuka' }}</div>
            </div>

            @php
                $base = request()->routeIs('admin.*') ? 'admin' : (request()->routeIs('guru.*') ? 'guru' : 'master');
            @endphp

            <form method="POST" action="{{ route($base.'.absensi.update', $session->id) }}">
                @csrf
                @method('PUT')

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-zinc-500">
                            <tr>
                                <th class="text-left py-2 pr-3">NIS</th>
                                <th class="text-left py-2 pr-3">Nama</th>
                                <th class="text-left py-2 pr-3">Status</th>
                                <th class="text-left py-2 pr-3">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="text-zinc-800 dark:text-zinc-200">
                        @foreach($siswas as $s)
                            @php $rec = $recordsBySiswa[$s->id] ?? null; @endphp
                            <tr class="border-t border-zinc-200 dark:border-zinc-800">
                                <td class="py-2 pr-3">{{ $s->nis ?? '-' }}</td>
                                <td class="py-2 pr-3">{{ $s->nama }}</td>
                                <td class="py-2 pr-3">
                                    @foreach(['hadir'=>'Hadir','izin'=>'Izin','sakit'=>'Sakit','alfa'=>'Alfa'] as $val=>$label)
                                        <label class="mr-3 inline-flex items-center gap-1">
                                            <input type="radio" name="records[{{ $s->id }}][status]" value="{{ $val }}" class="rounded" @checked(($rec->status ?? 'hadir')===$val) @disabled($session->locked)>
                                            <span>{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </td>
                                <td class="py-2 pr-3">
                                    <input type="text" name="records[{{ $s->id }}][notes]" value="{{ $rec->notes ?? '' }}" class="w-full rounded-md border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-sm" @disabled($session->locked) />
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex items-center gap-3">
                    @if(!$session->locked)
                        <button class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">Simpan</button>
                        <button name="lock" value="1" class="inline-flex items-center rounded-md bg-zinc-900 px-3 py-2 text-sm font-medium text-white">Simpan & Kunci</button>
                    @else
                        <div class="text-xs text-zinc-500">Sesi sudah terkunci.</div>
                    @endif
                    <a href="{{ route($base.'.absensi') }}" class="ml-auto inline-flex items-center rounded-md border border-zinc-200 dark:border-zinc-700 px-3 py-2 text-sm">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
