<x-layouts.app :title="__('Pilih Template Nilai')">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-lg font-semibold text-zinc-900">Pilih Template Nilai</h2>
            <p class="mt-1 text-sm text-zinc-500">Silakan pilih template penilaian yang sesuai. Kamu bisa input nilai per siswa atau sekaligus per kelas.</p>
        </div>
        <a href="{{ route('master.nilai.index') }}" class="text-sm text-zinc-500 hover:text-zinc-700 hover:underline">&larr; Kembali ke Daftar Nilai</a>
    </div>

    @if($templates->isEmpty())
        <div class="text-center py-10 text-gray-500 border border-dashed rounded-xl bg-gray-50 dark:bg-zinc-800">
            <p class="text-sm">Belum ada template penilaian yang bisa digunakan.</p>
            <p class="mt-1 text-xs text-zinc-400">Buat template baru di menu pengaturan nilai untuk mulai melakukan input penilaian.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($templates as $item)
                <div class="border rounded-xl bg-white dark:bg-zinc-800 p-5 hover:border-indigo-500/70 transition-colors">
                    <div class="flex justify-between items-start gap-3 mb-3">
                        <div>
                            <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">
                                {{ $item->nama_template }}
                            </h3>
                            @if(!empty($item->deskripsi))
                                <p class="mt-1 text-xs text-zinc-500 line-clamp-2">{{ $item->deskripsi }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 flex flex-col gap-2">
                        <a href="{{ route('master.nilai.create', $item->id) }}" class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
                            <span>Input per Siswa (detail)</span>
                        </a>
                        <a href="{{ route('master.nilai.create-bulk', $item->id) }}" class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
                            <span>Input Banyak per Kelas</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.app>
