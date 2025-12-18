<x-layouts.app :title="__('Edit Nilai')">
    <div class="max-w-4xl mx-auto">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="text-lg font-semibold">Edit Nilai</h2>
        </div>

        <form method="POST" action="{{ route('master.nilai.update', $nilai->id) }}" class="bg-white dark:bg-zinc-800 border rounded-lg p-5 space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm text-zinc-600">Siswa</label>
                    <div class="font-medium">{{ $nilai->siswa->nama ?? '-' }} ({{ $nilai->siswa->nis ?? '-' }})</div>
                </div>
                <div>
                    <label class="text-sm text-zinc-600">Jenis Penilaian</label>
                    <div class="font-medium">{{ $nilai->jenis_penilaian ?? '-' }}</div>
                </div>
            </div>

            <div class="border-t pt-4">
                <h3 class="font-semibold mb-3">Detail Nilai</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($detail as $k => $v)
                        <div>
                            <label class="text-sm text-zinc-600" for="detail_{{ md5($k) }}">{{ $k }}</label>
                            <input id="detail_{{ md5($k) }}" name="detail[{{ $k }}]" value="{{ is_array($v) ? json_encode($v) : $v }}" class="w-full rounded border px-3 py-2 bg-white dark:bg-zinc-900" />
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('master.nilai.show', $nilai->id) }}" class="px-3 py-2 rounded-md border hover:bg-gray-50 dark:hover:bg-zinc-700">Batal</a>
                <button type="submit" class="px-3 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</x-layouts.app>
