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

<x-layouts.app :title="__('Buat Sesi Absensi')">
    <div class="p-6">
        <div class="mx-auto max-w-xl overflow-hidden rounded-lg border bg-white dark:bg-zinc-800">
            <div class="border-b px-6 py-4">
                <h2 class="text-lg font-semibold">Buat Sesi Absensi</h2>
            </div>
            <form action="{{ route($routePrefix . '.absensi.store') }}" method="POST" class="space-y-5 p-6">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium">Kelas</label>
                    <select name="kelas_id" required class="w-full rounded border px-3 py-2">
                        <option value="">Pilih Kelas</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                    @error('kelas_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ $today }}" required class="w-full rounded border px-3 py-2" />
                    @error('tanggal')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Tahun Ajaran</label>
                    <select name="tahun_ajaran_id" class="w-full rounded border px-3 py-2">
                        <option value="">Tidak ditentukan</option>
                        @foreach($tahunAjarans as $ta)
                            <option value="{{ $ta->id }}">{{ $ta->nama ?? ($ta->tahun ?? ('TA ' . $ta->id)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Keterangan</label>
                    <input type="text" name="keterangan" placeholder="Opsional" class="w-full rounded border px-3 py-2" />
                </div>
                <div class="border-t bg-zinc-50 p-4 text-right dark:bg-zinc-900">
                    <a href="{{ route($routePrefix . '.absensi') }}" class="rounded border px-4 py-2">Batal</a>
                    <button class="ml-2 rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Lanjut</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
