<x-layouts.app :title="__('Import Nilai')">
    @php
        $currentName = \Illuminate\Support\Facades\Route::currentRouteName();
        $group = $currentName ? explode('.', $currentName)[0] : (in_array(request()->segment(1), ['admin','guru','master']) ? request()->segment(1) : 'master');
    @endphp
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">Import Nilai</h2>
        <a href="{{ route($group.'.nilai.index') }}" class="px-3 py-2 bg-gray-200 rounded hover:bg-gray-300">Kembali ke Daftar Nilai</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="p-5 bg-white dark:bg-zinc-800 rounded border">
            <h3 class="font-semibold mb-2">Format 1: Nilai TA</h3>
            <p class="text-sm text-gray-600 mb-2">Kolom: NO, NAMA, NIS, NISN, JURUSAN, PROJECT, INSTANSI 1, KOTA 1, NP, NS1..NS6, JML NS, NA, HURUF, PREDIKAT</p>
            <p class="text-xs text-blue-600 mb-3">
                <a href="{{ asset('template/nilai_ta_template.csv') }}" class="underline hover:no-underline" download>
                    Download template Nilai TA (CSV)
                </a>
            </p>
            <form action="{{ route($group.'.nilai.import.ta') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <label class="block text-sm font-medium">Upload File Excel/CSV</label>
                <input type="file" name="file_excel" accept=".xlsx,.xls,.csv" class="w-full text-sm border rounded px-3 py-2">
                @error('file_excel')
                    <p class="text-red-600 text-sm">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500">Template akan dibuat otomatis untuk format Nilai TA</p>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Import TA</button>
            </form>
        </div>

        <div class="p-5 bg-white dark:bg-zinc-800 rounded border">
            <h3 class="font-semibold mb-2">Format 2: UKK Dudi</h3>
            <p class="text-sm text-gray-600 mb-2">Kolom: no, No Peserta, nama siswa, NISN, predikat, predikat english, Nilai Akhir</p>
            <p class="text-xs text-blue-600 mb-3">
                <a href="{{ asset('template/nilai_ukk_dudi_template.csv') }}" class="underline hover:no-underline" download>
                    Download template UKK Dudi (CSV)
                </a>
            </p>
            <form action="{{ route($group.'.nilai.import.ukk') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <label class="block text-sm font-medium">Upload File Excel/CSV</label>
                <input type="file" name="file_excel" accept=".xlsx,.xls,.csv" class="w-full text-sm border rounded px-3 py-2">
                @error('file_excel')
                    <p class="text-red-600 text-sm">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500">Template akan dibuat otomatis untuk format UKK DUDI</p>
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700">Import UKK Dudi</button>
            </form>
        </div>

        <div class="p-5 bg-white dark:bg-zinc-800 rounded border">
            <h3 class="font-semibold mb-2">Format 3: Prakerin (DUDI)</h3>
            <p class="text-sm text-gray-600 mb-2">Kolom: nama, nis, kelas, judul_laporan, kedisiplinan_*, kompetensi_*, adaptasi_*, lainnya_*</p>
            <p class="text-xs text-blue-600 mb-3">
                <a href="{{ asset('template/nilai_prakerin_dudi_template.csv') }}" class="underline hover:no-underline" download>
                    Download template Prakerin (DUDI) (CSV)
                </a>
            </p>
            <form action="{{ route($group.'.nilai.import.prakerin') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <label class="block text-sm font-medium">Upload File Excel/CSV</label>
                <input type="file" name="file_excel" accept=".xlsx,.xls,.csv" class="w-full text-sm border rounded px-3 py-2">
                @error('file_excel')
                    <p class="text-red-600 text-sm">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500">Template akan dibuat otomatis untuk format Prakerin/DUDI</p>
                <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700">Import Prakerin</button>
            </form>
        </div>
    </div>
</x-layouts.app>
