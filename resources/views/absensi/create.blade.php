<x-layouts.app :title="__('Buat Sesi Absensi')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="rounded-2xl border border-zinc-200/70 dark:border-zinc-800/70 bg-white dark:bg-zinc-900 p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-50">Buat Sesi Absensi</h3>
            </div>

            @php
                $storeRoute = request()->routeIs('admin.*') ? 'admin.absensi.store' : (request()->routeIs('guru.*') ? 'guru.absensi.store' : 'master.absensi.store');
            @endphp

            <form method="POST" action="{{ route($storeRoute) }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                <div>
                    <label class="block text-xs text-zinc-500 mb-1">Kelas</label>
                    <select name="kelas_id" class="w-full rounded-md border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-sm" required>
                        <option value="" selected disabled>Pilih Kelas</option>
                        @foreach(($kelas ?? collect()) as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-zinc-500 mb-1">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ $today ?? now()->toDateString() }}" class="w-full rounded-md border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-sm" required />
                </div>
                <div>
                    <label class="block text-xs text-zinc-500 mb-1">Tahun Ajaran</label>
                    <select name="tahun_ajaran_id" class="w-full rounded-md border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-sm">
                        <option value="">- Opsional -</option>
                        @foreach(($tahunAjarans ?? collect()) as $ta)
                            <option value="{{ $ta->id }}">{{ $ta->tahun_ajaran ?? ('TA #'.$ta->id) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-zinc-500 mb-1">Keterangan</label>
                    <input type="text" name="keterangan" class="w-full rounded-md border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-sm" placeholder="Opsional" />
                </div>

                <div class="md:col-span-2 flex items-center gap-3 mt-2">
                    <a href="{{ url()->previous() }}" class="inline-flex items-center rounded-md border border-zinc-200 dark:border-zinc-700 px-3 py-2 text-sm">Batal</a>
                    <button class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">Buat Sesi</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
