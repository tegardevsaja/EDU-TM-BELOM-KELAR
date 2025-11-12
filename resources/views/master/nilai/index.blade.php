<x-layouts.app :title="__('Template Penilaian')">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold">Template Penilaian</h2>
        <x-ui.button :href="route('master.penilaian.create')" variant="primary" size="md">+ Tambah Template</x-ui.button>
    </div>

    @if($template->isEmpty())
        <div class="text-center py-10 text-gray-500 border rounded-lg bg-gray-50 dark:bg-zinc-800">
            Belum ada template penilaian.
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($template as $index => $item)
                <div class="border rounded-lg shadow-sm hover:shadow-md transition bg-white dark:bg-zinc-800 p-5">
                    {{-- Header --}}
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                                {{ $item->nama_template }}
                            </h3>
                            <p class="text-sm text-gray-500">
                                Dibuat oleh: <span class="font-medium">{{ $item->creator->name ?? '-' }}</span>
                            </p>
                        </div>
                        <span class="text-xs text-gray-400">#{{ $index + 1 }}</span>
                    </div>

                    {{-- Deskripsi --}}
                    @if(!empty($item->deskripsi))
                        <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">
                            {{ $item->deskripsi }}
                        </p>
                    @endif

                    {{-- Komponen --}}
                    <div class="border-t pt-3 mt-3">
                        @if (!empty($item->komponen))
                            <div class="space-y-3">
                                @foreach ($item->komponen as $komponen)
                                    <div>
                                        <p class="font-semibold text-gray-800 dark:text-gray-100">
                                            {{ $komponen['kategori'] ?? '-' }}
                                        </p>
                                        <ul class="list-disc list-inside text-sm text-gray-700 dark:text-gray-300">
                                            @foreach ($komponen['subkomponen'] ?? [] as $sub)
                                                <li>{{ $sub['uraian'] ?? '-' }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-sm">Tidak ada komponen.</p>
                        @endif
                    </div>

                    {{-- Aksi --}}
                    <div class="flex justify-end gap-2 mt-5 pt-3 border-t">
                        <x-ui.button :href="route('master.penilaian.edit', $item->id)" variant="secondary" size="sm">Edit</x-ui.button>
                        <form action="{{ route('master.penilaian.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus template ini?')" class="inline">
                            @csrf
                            @method('DELETE')
                            <x-ui.button type="submit" variant="danger" size="sm">Hapus</x-ui.button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.app>
