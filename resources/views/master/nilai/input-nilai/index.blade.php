<x-layouts.app :title="__('Daftar Nilai')">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">Daftar Nilai</h2>
        <div class="flex gap-2">
            <a href="{{ route('master.penilaian') }}" class="px-3 py-2 bg-gray-200 rounded hover:bg-gray-300">Kelola Template</a>
        </div>
    </div>

    @if(isset($penilaians) && $penilaians->count())
        <div class="overflow-x-auto bg-white dark:bg-zinc-800 rounded border">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-zinc-700">
                    <tr>
                        <th class="text-left px-4 py-3">Tanggal</th>
                        <th class="text-left px-4 py-3">Template</th>
                        <th class="text-left px-4 py-3">Siswa</th>
                        <th class="text-left px-4 py-3">Guru</th>
                        <th class="text-left px-4 py-3">Jenis</th>
                        <th class="text-left px-4 py-3">Visibilitas</th>
                        <th class="text-right px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($penilaians as $n)
                        <tr class="border-t">
                            <td class="px-4 py-3">{{ optional($n->tanggal_input)->format('d M Y') ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $n->template->nama_template ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $n->siswa->nama ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $n->guru->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $n->jenis_penilaian ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @if($n->visibility === 'admin')
                                    <span class="inline-flex items-center px-2 py-1 text-xs rounded bg-rose-100 text-rose-700">Admin</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 text-xs rounded bg-emerald-100 text-emerald-700">Semua</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex gap-2">
                                    <a href="#" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded">Lihat</a>
                                    <a href="#" class="px-3 py-1.5 bg-blue-600 text-white hover:bg-blue-700 rounded">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $penilaians->links() }}</div>
    @else
        <div class="border bg-gray-50 dark:bg-zinc-800 rounded p-8 text-center text-gray-600">
            Belum ada data nilai.
        </div>
    @endif
</x-layouts.app>
