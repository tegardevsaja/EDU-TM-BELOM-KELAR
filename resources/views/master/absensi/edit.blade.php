@php
    $routePrefix = 'master';
    if (auth()->check() && method_exists(auth()->user(), 'hasRole')) {
        if (auth()->user()->hasRole('master_admin')) {
            $routePrefix = 'master';
        } elseif (auth()->user()->hasRole('admin')) {
            $routePrefix = 'admin';
        } elseif (auth()->user()->hasRole('guru')) {
            $routePrefix = 'guru';
        }
    }
@endphp

<x-layouts.app :title="__('Kelola Absensi')">
    <div class="p-6">
        <div class="mb-4">
            <a href="{{ route($routePrefix . '.absensi') }}" class="text-sm text-blue-600">&larr; Kembali ke daftar</a>
        </div>

        <div class="mb-4">
            <h1 class="text-lg font-semibold">Absensi {{ $session->kelas->nama_kelas ?? '-' }} - {{ $session->tanggal->format('d M Y') }}</h1>
            <p class="text-sm text-gray-500">{{ $session->keterangan ?? 'Tanpa keterangan' }}</p>
        </div>

        @if($session->locked)
            <div class="mb-4 rounded border border-yellow-300 bg-yellow-50 p-3 text-sm">Sesi ini terkunci. Data tidak dapat diubah.</div>
        @endif

        <form action="{{ route($routePrefix . '.absensi.update', $session->id) }}" method="POST" id="form-absen">
            @csrf
            @method('PUT')
            <input type="hidden" name="bulk_ids" id="bulk_ids" value="" />
            <input type="hidden" name="bulk_status" id="bulk_status" value="" />
            <input type="hidden" name="lock" id="lock_flag" value="" />

            <div class="overflow-hidden rounded-lg border bg-white dark:bg-zinc-800">
                <div class="flex items-center justify-between border-b p-4">
                    <div class="flex items-center gap-2">
                        <button type="button" class="rounded border px-3 py-1.5 text-sm" onclick="setBulk('hadir')">Tandai Hadir</button>
                        <button type="button" class="rounded border px-3 py-1.5 text-sm" onclick="setBulk('alfa')">Tandai Alfa</button>
                        <button type="button" class="rounded border px-3 py-1.5 text-sm" onclick="setBulk('sakit')">Tandai Sakit</button>
                        <button type="button" class="rounded border px-3 py-1.5 text-sm" onclick="setBulk('izin')">Tandai Izin</button>
                    </div>
                    <div>
                        @if(!$session->locked)
                        <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white" onclick="document.getElementById('lock_flag').value='';">Simpan Perubahan</button>
                        <button type="button" class="ml-2 rounded bg-zinc-800 px-4 py-2 text-white" onclick="submitAndLock()">Simpan & Kunci</button>
                        @endif
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-zinc-700">
                            <tr>
                                <th class="px-4 py-3">
                                    <input type="checkbox" id="check_all" onclick="toggleAll(this)" />
                                </th>
                                <th class="px-4 py-3 text-left">Nama</th>
                                <th class="px-4 py-3 text-left">NIS</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($siswas as $s)
                                @php $rec = $recordsBySiswa[$s->id] ?? null; @endphp
                                <tr class="border-t">
                                    <td class="px-4 py-3">
                                        <input type="checkbox" class="row-check" value="{{ $s->id }}" />
                                    </td>
                                    <td class="px-4 py-3">{{ $s->nama }}</td>
                                    <td class="px-4 py-3">{{ $s->nis }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-3">
                                            @foreach(['hadir' => 'Hadir', 'alfa' => 'Alfa', 'sakit' => 'Sakit', 'izin' => 'Izin'] as $key => $label)
                                                <label class="inline-flex items-center gap-1">
                                                    <input type="radio" name="records[{{ $s->id }}][status]" value="{{ $key }}" @checked(($rec->status ?? 'hadir') === $key) @disabled($session->locked)>
                                                    <span>{{ $label }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="text" name="records[{{ $s->id }}][notes]" value="{{ $rec->notes ?? '' }}" class="w-full rounded border px-3 py-1.5" @disabled($session->locked) />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </form>

        <div class="mt-4 flex items-center justify-between">
            <div></div>
            <div class="flex items-center gap-2">
                @if(!$session->locked)
                <form action="{{ route($routePrefix . '.absensi.lock', $session->id) }}" method="POST" onsubmit="return confirm('Kunci sesi ini?')">
                    @csrf
                    <button class="rounded bg-zinc-700 px-4 py-2 text-white">Kunci Sesi</button>
                </form>
                @endif
                <form action="{{ route($routePrefix . '.absensi.destroy', $session->id) }}" method="POST" onsubmit="return confirm('Hapus sesi ini?')">
                    @csrf
                    @method('DELETE')
                    <button class="rounded bg-red-600 px-4 py-2 text-white">Hapus Sesi</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleAll(cb) {
            document.querySelectorAll('.row-check').forEach(el => { el.checked = cb.checked; });
        }
        function collectSelectedIds() {
            const ids = Array.from(document.querySelectorAll('.row-check'))
                .filter(el => el.checked)
                .map(el => el.value);
            return ids.join(',');
        }
        function setBulk(status) {
            const ids = collectSelectedIds();
            if (!ids) {
                alert('Pilih siswa terlebih dahulu.');
                return;
            }
            document.getElementById('bulk_ids').value = ids;
            document.getElementById('bulk_status').value = status;
            document.getElementById('form-absen').submit();
        }
        function submitAndLock() {
            document.getElementById('lock_flag').value = '1';
            document.getElementById('form-absen').submit();
        }
    </script>
</x-layouts.app>
