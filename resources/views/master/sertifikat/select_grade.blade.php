<x-layouts.app :title="'Pilih Template Nilai'">
    <div class="max-w-4xl mx-auto p-6">
        <h2 class="text-xl font-semibold mb-4 dark:text-white">Pilih Template Nilai (Opsional)</h2>

        <div class="mb-4 text-sm text-gray-600 dark:text-gray-300">
            <p>Langkah: Pilih Template Sertifikat → Pilih Siswa (opsional) → <strong>Pilih Template Nilai</strong> → Customize</p>
        </div>

        <form action="{{ route('master.sertifikat.generate.customize', $template->id) }}" method="GET" class="space-y-5">
            @if(!empty($selectedStudentIds) && count($selectedStudentIds) > 0)
                @foreach($selectedStudentIds as $sid)
                    <input type="hidden" name="siswa_ids[]" value="{{ $sid }}" />
                @endforeach
            @endif
            @if(!empty($kelas_id))
                <input type="hidden" name="kelas_id" value="{{ $kelas_id }}" />
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($gradeTemplates as $gt)
                    <label class="border rounded-lg p-4 cursor-pointer hover:border-purple-500">
                        <input type="radio" name="grade_template_id" value="{{ $gt->id }}" class="mr-2">
                        <span class="font-semibold">{{ $gt->nama_template }}</span>
                        @if($gt->deskripsi)
                            <div class="text-xs text-gray-500 mt-1">{{ $gt->deskripsi }}</div>
                        @endif
                    </label>
                @endforeach
                @if($gradeTemplates->isEmpty())
                    <div class="text-sm text-gray-500">Belum ada template nilai yang tersedia.</div>
                @endif
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ url()->previous() }}" class="rounded border px-4 py-2">Kembali</a>
                <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white">Lanjut ke Customize</button>
            </div>
        </form>
    </div>
</x-layouts.app>
