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

<x-layouts.app :title="__('Absensi Kelas')">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-lg font-semibold">Absensi per Kelas</h1>
            @can('absensi.create')
            <a href="{{ route($routePrefix . '.absensi.create') }}" class="inline-flex items-center gap-2 rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Buat Sesi</a>
            @endcan
        </div>

        <form method="GET" class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <label class="block text-sm mb-1">Kelas</label>
                <select name="kelas_id" class="w-full rounded border px-3 py-2">
                    <option value="">Semua</option>
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}" @selected($kelasId == $k->id)>{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm mb-1">Tanggal</label>
                <input type="date" name="tanggal" value="{{ $tanggal }}" class="w-full rounded border px-3 py-2" />
            </div>
            <div class="flex items-end">
                <button class="rounded bg-zinc-800 text-white px-4 py-2">Filter</button>
            </div>
        </form>

        <div class="overflow-hidden rounded-lg border bg-white dark:bg-zinc-800">
            @if ($sessions->isEmpty())
                <div class="p-10 text-center text-gray-500">Belum ada sesi absensi.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-zinc-700">
                            <tr>
                                <th class="px-4 py-3 text-left">Tanggal</th>
                                <th class="px-4 py-3 text-left">Kelas</th>
                                <th class="px-4 py-3 text-left">Keterangan</th>
                                <th class="px-4 py-3 text-left">Dibuat</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sessions as $s)
                                <tr class="border-t">
                                    <td class="px-4 py-3">{{ $s->tanggal->format('d M Y') }}</td>
                                    <td class="px-4 py-3">{{ $s->kelas->nama_kelas ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $s->keterangan ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $s->creator->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="inline-flex gap-2">
                                            @can('absensi.update')
                                            <a href="{{ route($routePrefix . '.absensi.edit', $s->id) }}" class="rounded bg-yellow-500 text-white px-3 py-1.5 text-xs">Kelola</a>
                                            @endcan
                                            @can('absensi.lock')
                                            @if(!$s->locked)
                                            <form action="{{ route($routePrefix . '.absensi.lock', $s->id) }}" method="POST" onsubmit="return confirm('Kunci sesi ini?')">
                                                @csrf
                                                <button class="rounded bg-zinc-700 text-white px-3 py-1.5 text-xs">Kunci</button>
                                            </form>
                                            @else
                                            <span class="rounded bg-zinc-200 px-2 py-1 text-xs">Terkunci</span>
                                            @endif
                                            @endcan
                                            @can('absensi.delete')
                                            <form action="{{ route($routePrefix . '.absensi.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Hapus sesi ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="rounded bg-red-600 text-white px-3 py-1.5 text-xs">Hapus</button>
                                            </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t p-4">{{ $sessions->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts.app>
