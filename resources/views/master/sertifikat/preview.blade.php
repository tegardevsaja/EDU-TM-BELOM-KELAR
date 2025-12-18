<x-layouts.app :title="'Preview Sertifikat'">
    <div class="max-w-7xl mx-auto p-4">
        <h2 class="text-2xl font-bold mb-6 dark:text-white">
            Preview Sertifikat hai hai hai - {{ $template->nama_template }}
        </h2>

        @if (session('success'))
            <div class="mb-4 p-3 rounded bg-emerald-50 border border-emerald-200 text-emerald-800 dark:bg-emerald-900/30 dark:border-emerald-800 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-gray-600 dark:text-gray-400">
                        <strong>Kelas:</strong> 
                        @if($kelas)
                            {{ $kelas->nama_kelas }}
                        @else
                            <span class="text-gray-500">Semua Siswa / Tidak Dipilih</span>
                        @endif
                    </p>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        <strong>Jumlah Siswa:</strong> {{ $siswas->count() }}
                    </p>
                </div>


                {{-- Tombol Kembali ke Customize --}}
                <a href="{{ route('master.sertifikat.generate.customize', $template->id) }}{{ $kelas ? '?kelas_id=' . $kelas->id : '' }}@if(request('grade_template_id')){{ $kelas ? '&' : '?' }}grade_template_id={{ request('grade_template_id') }}@endif" 
                   class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    Edit Template
                </a>
            </div>

            {{-- Filter: Pagination Size (Template Nilai diatur di langkah sebelumnya) --}}
            <form method="GET" action="{{ url()->current() }}" class="grid grid-cols-1 md:grid-cols-6 gap-3 mb-4 items-end">
                <input type="hidden" name="kelas_id" value="{{ request('kelas_id') }}" />
                @if(request('grade_template_id'))
                    <input type="hidden" name="grade_template_id" value="{{ request('grade_template_id') }}" />
                @endif
                @foreach((array)request('siswa_ids', []) as $sid)
                    <input type="hidden" name="siswa_ids[]" value="{{ $sid }}" />
                @endforeach
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Format Lampiran Nilai</label>
                    <select name="grade_sheet_format" class="w-[12rem] border rounded px-3 py-2 dark:bg-gray-700 dark:text-gray-100">
                        <option value="" {{ request('grade_sheet_format')===''? 'selected':'' }}>-- Default (tabel sederhana) --</option>
                        <option value="prakerin" {{ request('grade_sheet_format')==='prakerin'? 'selected':'' }}>Prakerin</option>
                        <option value="tugas_akhir" {{ request('grade_sheet_format')==='tugas_akhir'? 'selected':'' }}>Tugas Akhir</option>
                    </select>
                </div>
               
                
                <div class="flex items-end">
                    <button class="px-4 py-2 ml-18 bg-blue-600 hover:bg-blue-700 text-white rounded">Terapkan</button>
                </div>
            </form>

            {{-- Form Generate & Download/Email/Storage --}}
            <form id="certGenerateForm" action="{{ route('master.sertifikat.generate.process') }}" method="POST">
                @csrf
                <input type="hidden" name="template_id" value="{{ $template->id }}">
                @if($kelas)
                    <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                @endif
                @if(request('grade_sheet_format'))
                    <input type="hidden" name="grade_sheet_format" value="{{ request('grade_sheet_format') }}">
                @endif
                @if(request('grade_template_id'))
                    <input type="hidden" name="grade_template_id" value="{{ request('grade_template_id') }}">
                @endif

                {{-- Tujuan pengiriman --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Tujuan</label>
                        <select name="destination" id="destinationSelect" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:text-gray-100">
                            <option value="download" selected>Download ke perangkat</option>
                            <option value="email">Kirim ke email</option>
                        </select>
                    </div>
                    <div id="emailFieldWrapper" class="md:col-span-2 hidden">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Email tujuan</label>
                        <input type="email" name="email" placeholder="contoh: nama@domain.com" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:text-gray-100">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Jika memilih Kirim ke email, masukkan alamat email penerima.</p>
                    </div>
                </div>

                {{-- Mode Cetak ditentukan otomatis: jika ada template nilai => keduanya, jika tidak => sertifikat saja --}}

                {{-- Opsi Nomor Sertifikat --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Format Nomor Sertifikat</label>
                        <input type="text" name="cert_number_format" value="{{ old('cert_number_format', '000/SMK-TM/XX/XX/XXXX') }}" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:text-gray-100">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Gunakan pola: 000 = nomor berurutan (auto padding), XX = bulan & tanggal, XXXX = tahun. Contoh: 000/SMK-TM/XX/XX/XXXX</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Mulai Nomor</label>
                        <input type="number" min="1" name="cert_start_number" value="{{ (int)old('cert_start_number', 1) }}" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:text-gray-100">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Nomor awal untuk penomoran berurutan</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Tanggal Cetak (Opsional)</label>
                        <input type="date" name="cert_date" value="{{ old('cert_date') }}" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:text-gray-100">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Jika kosong, pakai tanggal hari ini</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <!-- PDF Button -->
                    <button type="submit" name="format" value="pdf" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all inline-flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <div class="text-left">
                            <div class="font-bold">PDF</div>
                            <div class="text-xs opacity-80">Single file</div>
                        </div>
                    </button>
                </div>
                {{-- Pass grade template id if any so download includes nilai --}}
                @if(!empty($gradeTemplateId))
                    <input type="hidden" name="grade_template_id" value="{{ $gradeTemplateId }}">
                @elseif(request('grade_template_id'))
                    <input type="hidden" name="grade_template_id" value="{{ request('grade_template_id') }}">
                @endif
                
                <!-- Info Box -->
                <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900 dark:bg-opacity-30 rounded-lg border border-blue-200 dark:border-blue-700">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-sm text-blue-800 dark:text-blue-300">
                            <p class="font-semibold mb-1">Format Download:</p>
                            <ul class="list-disc list-inside space-y-1 text-xs">
                                <li><strong>PDF saja</strong> melalui UI untuk stabilitas.</li>
                                <li>Export gambar (PNG/JPG/JPEG) tersedia via perintah Artisan.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- Preview Sertifikat dengan Elements (Grid) --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 relative" style="z-index:0;">
            @forelse ($paginatedSiswas as $siswa)
                @php $eligKey = isset($siswa->id) && $siswa->id ? $siswa->id : spl_object_hash($siswa); $ok = $eligibility[$eligKey] ?? true; @endphp
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden {{ $ok ? '' : 'opacity-60' }}">
                    <div class="p-4 bg-gray-100 dark:bg-gray-700 flex items-start justify-between gap-3">
                        <div>
                            <h3 class="font-bold text-lg dark:text-white">{{ $siswa->nama }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">NIS: {{ $siswa->nis }} | Kelas: {{ $siswa->kelas->nama_kelas ?? '-' }}</p>
                            @if($ok && !empty($gradeTemplateId) && !empty($siswa->nilai))
                                <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">Nilai: {{ $siswa->nilai }}</p>
                            @endif
                        </div>
                        <div class="text-right">
                            @if($ok)
                                <span class="inline-flex items-center rounded bg-green-100 text-green-700 px-2 py-0.5 text-xs">Eligible</span>
                            @else
                                <span class="inline-flex items-center rounded bg-red-100 text-red-700 px-2 py-0.5 text-xs">Tidak Eligible</span>
                            @endif
                        </div>
                    </div>
                    @if(!$ok)
                        <div class="px-4 py-2 bg-red-50 dark:bg-red-900/30 border-t border-red-200 dark:border-red-800">
                            <p class="text-xs text-red-700 dark:text-red-300 font-medium">
                                Siswa ini tidak bisa mendapatkan sertifikat berdasarkan aturan kehadiran.
                            </p>
                            @if(isset($siswa->id) && $siswa->id)
                                <form method="POST" action="{{ route('master.sertifikat.eligibility.override') }}" class="mt-2 inline">
                                    @csrf
                                    <input type="hidden" name="siswa_id" value="{{ $siswa->id }}">
                                    <input type="hidden" name="granted" value="1">
                                    <button type="submit"
                                        class="inline-flex items-center px-3 py-1 rounded bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold"
                                        data-confirm-delete
                                        data-title="Buka akses sertifikat?"
                                        data-name="{{ $siswa->nama }}"
                                        data-confirm-label="Ya, buka"
                                        data-cancel-label="Batal">
                                        Buka Akses Sertifikat
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                    <div class="relative w-full bg-gray-200 dark:bg-gray-900 mx-auto overflow-hidden" style="aspect-ratio: 297/210; max-width: 1000px;">
                        @if($template->background_image)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($template->background_image) }}" class="absolute inset-0 w-full h-full object-cover" alt="Background">
                        @endif
                        @php
                            $elementsToRender = [];
                            if ($template->elements && is_string($template->elements)) {
                                $elementsToRender = json_decode($template->elements, true) ?? [];
                            }
                            if (empty($elementsToRender)) {
                                $elementsToRender = [[ 'type'=>'text','content'=>'CERTIFICATE','x'=>50,'y'=>20,'fontSize'=>36,'fontFamily'=>'Arial','color'=>'#000000','bold'=>true,'align'=>'center' ]];
                            }
                            $previewScale = 0.6;
                        @endphp
                        @foreach($elementsToRender as $element)
                            @php
                                $content = '';
                                $isNameVariable = false;
                                if($element['type'] === 'text') {
                                    $content = $element['content'] ?? '';
                                }
                                elseif($element['type'] === 'variable') {
                                    $var = (string)($element['variable'] ?? '');
                                    $content = match($var) {
                                        '$nama_siswa' => $siswa->nama ?? '',
                                        '$Nama' => $siswa->nama ?? '',
                                        '$kelas' => $siswa->kelas->nama_kelas ?? '',
                                        '$nis' => $siswa->nis ?? '',
                                        '$tanggal' => ($siswa->tanggal_custom ?? null) ? \Carbon\Carbon::parse($siswa->tanggal_custom)->locale('id')->isoFormat('D MMMM YYYY') : now()->locale('id')->isoFormat('D MMMM YYYY'),
                                        '$jurusan' => $siswa->kelas->jurusan->nama_jurusan ?? '',
                                        '$nilai' => $siswa->nilai ?? '-',
                                        '$peringkat' => $siswa->peringkat ?? '-',
                                        '$nomor_sertifikat' => $siswa->cert_number ?? '',
                                        '$no_sertifikat' => $siswa->cert_number ?? '',
                                        '$no_sertif' => $siswa->cert_number ?? '',
                                        '$nomor' => $siswa->cert_number ?? '',
                                        '$ttd' => '(Tanda Tangan)',
                                        default => $element['value'] ?? $element['variable'] ?? ''
                                    };
                                    $isNameVariable = in_array($var, ['$', '$nama_siswa', '$Nama'], true) || str_contains(strtolower($var), 'nama');
                                }
                                $fontSize = ($element['fontSize'] ?? 24) * $previewScale;
                                $fontFamily = $element['fontFamily'] ?? 'Arial';
                                $color = $element['color'] ?? '#000000';
                                $bold = $element['bold'] ?? false;
                                // Posisi semua elemen (text & variable) menggunakan titik tengah di X,Y persis sesuai data customize
                                $align = $element['align'] ?? 'center';
                                $x = $element['x'] ?? 50;
                                $y = $element['y'] ?? 50;

                                // Samakan logika centering dengan download_pdf.blade.php
                                if ($isNameVariable) {
                                    $wordCount = is_string($content) ? str_word_count(trim($content)) : 0;

                                    $tx = '-50%';
                                    // Atur margin-left per jumlah kata (ikuti aturan di download_pdf)
                                    if ($wordCount <= 1) {
                                        $extraMarginLeft = '1.2rem';
                                    } elseif ($wordCount === 2) {
                                        $extraMarginLeft = '1rem';
                                    } elseif ($wordCount === 3) {
                                        $extraMarginLeft = '1rem';
                                    } elseif ($wordCount === 4) {
                                        $extraMarginLeft = '1rem';
                                    } elseif ($wordCount <= 6) {
                                        $extraMarginLeft = '5rem';
                                    } else {
                                        $extraMarginLeft = '4rem';
                                    }
                                } else {
                                    $tx = $align === 'left' ? '0' : ($align === 'right' ? '-100%' : '-50%');
                                    $extraMarginLeft = '0';
                                }
                            @endphp
                            <div class="absolute select-none" style="left: {{ $x }}%; top: {{ $y }}%; transform: translate({{ $tx }}, -50%); font-size: {{ $fontSize }}px; font-family: {{ $fontFamily }}, sans-serif; color: {{ $color }}; font-weight: {{ $bold ? 'bold' : 'normal' }}; text-align: {{ $align }}; min-width: 0; pointer-events: none; margin-left: {{ $extraMarginLeft }};">
                                @if($element['type'] === 'text')
                                    <div class="whitespace-pre-wrap">{{ $content }}</div>
                                @elseif($element['type'] === 'variable')
                                    <span class="font-medium whitespace-pre">{{ $content }}</span>
                                @elseif($element['type'] === 'image')
                                    @php $imgW = (isset($element['width']) ? $element['width'] : 100) * $previewScale; $imgH = (isset($element['height']) ? $element['height'] : 100) * $previewScale; @endphp
                                    <img src="{{ $element['src'] ?? '' }}" style="width: {{ $imgW }}px; height: {{ $imgH }}px; object-fit: contain;" alt="Uploaded Image">
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="col-span-2">
                    <div class="bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-400 p-4 rounded">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.062 19c-1.54 0-2.502-1.667-1.732-3L10.268 4c.77-1.333 2.694-1.333 3.464 0L20.66 16c.77 1.333-.192 3-1.732 3H5.062z"/></svg>
                            <p class="text-yellow-800 dark:text-yellow-300 font-medium">Tidak ada siswa yang dipilih. Silakan pilih kelas terlebih dahulu.</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Pagination Sertifikat --}}
        @if($paginatedSiswas->hasPages())
            <div class="mt-4 flex items-center justify-between text-sm text-gray-600 dark:text-gray-300">
                <div>
                    Menampilkan
                    <span class="font-semibold">{{ $paginatedSiswas->firstItem() }}</span>
                    -
                    <span class="font-semibold">{{ $paginatedSiswas->lastItem() }}</span>
                    dari
                    <span class="font-semibold">{{ $paginatedSiswas->total() }}</span>
                    sertifikat
                </div>
                <div class="flex items-center gap-2">
                    @if($paginatedSiswas->onFirstPage())
                        <span class="px-3 py-1 rounded bg-gray-200 dark:bg-gray-700 cursor-not-allowed">Prev</span>
                    @else
                        <a href="{{ $paginatedSiswas->previousPageUrl() }}" class="px-3 py-1 rounded bg-gray-700 text-white hover:bg-gray-800">Prev</a>
                    @endif

                    <span>Halaman {{ $paginatedSiswas->currentPage() }} / {{ $paginatedSiswas->lastPage() }}</span>

                    @if($paginatedSiswas->hasMorePages())
                        <a href="{{ $paginatedSiswas->nextPageUrl() }}" class="px-3 py-1 rounded bg-gray-700 text-white hover:bg-gray-800">Next</a>
                    @else
                        <span class="px-3 py-1 rounded bg-gray-200 dark:bg-gray-700 cursor-not-allowed">Next</span>
                    @endif
                </div>
            </div>
        @endif

        {{-- Slider Lampiran Nilai (jika dipilih) --}}
        @php 
            $selFormat = request('grade_sheet_format');
            $gradeItems = collect();
            if ($selFormat === 'prakerin' || $selFormat === 'tugas_akhir') {
                $gradeItems = $paginatedSiswas->filter(function($s) use ($eligibility) {
                    $key = isset($s->id) && $s->id ? $s->id : spl_object_hash($s);
                    $ok = $eligibility[$key] ?? true;
                    return $ok && isset($s->nilai_detail_map) && is_array($s->nilai_detail_map) && !empty($s->nilai_detail_map);
                });
            }
        @endphp
        @if($gradeItems->count() > 0)
        <div x-data="{ j: 0, total: {{ $gradeItems->count() }} }" class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700">
                <div class="font-semibold dark:text-white">Lampiran Nilai ({{ strtoupper(str_replace('_',' ',$selFormat)) }})</div>
                <div class="flex items-center gap-2">
                    <button type="button" class="px-3 py-1 rounded bg-zinc-700 text-white" @click="j = (j - 1 + total) % total">Prev</button>
                    <span class="text-sm dark:text-gray-300" x-text="(j+1) + ' / ' + total"></span>
                    <button type="button" class="px-3 py-1 rounded bg-zinc-700 text-white" @click="j = (j + 1) % total">Next</button>
                </div>
            </div>
            @foreach($gradeItems as $siswa)
            <div x-show="j === {{ $loop->index }}" class="p-4">
                <div class="grade-frame" style="position:relative; width:100%; max-width:1000px; margin:0 auto; background:#fff; aspect-ratio:297/210; overflow:hidden;">
                    <style>
                        /* Neutralize Tailwind preflight for images inside grade preview so table alignment works */
                        .grade-frame img { display: inline-block !important; max-width: none !important; height: auto; }
                    </style>
                    <div class="grade-scale" style="position:absolute; top:0; left:50%; width:297mm; height:210mm; transform-origin: top center; transform: translateX(-50%) scale(0.65);">
                        @if($selFormat === 'prakerin')
                            @include('master.sertifikat.grades.prakerin', ['siswa' => $siswa, 'signatures' => \App\Models\GradeSignature::first()])
                        @elseif($selFormat === 'tugas_akhir')
                            @include('master.sertifikat.grades.tugas_akhir', ['siswa' => $siswa, 'signatures' => \App\Models\GradeSignature::first()])
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Pagination Controls removed as requested --}}

        {{-- Debug Info testingggg --}}
        <!-- @if(config('app.debug'))
        <div class="mt-6 bg-gray-100 dark:bg-gray-900 border-2 border-gray-300 dark:border-gray-700 rounded-lg p-4">
            <h4 class="font-bold text-gray-800 dark:text-gray-300 mb-2">🔍 Debug Info</h4>
            <div class="text-xs overflow-auto max-h-96 bg-white dark:bg-gray-800 p-3 rounded space-y-2">
                <div><strong>Template ID:</strong> {{ $template->id }}</div>
                <div><strong>Template Name:</strong> {{ $template->nama_template }}</div>
                <div><strong>Elements Count:</strong> {{ is_array($elements ?? null) ? count($elements) : (is_array(json_decode($template->elements ?? '[]', true)) ? count(json_decode($template->elements ?? '[]', true)) : 0) }}</div>
                <div><strong>Students Count:</strong> {{ $siswas->count() }}</div>
                <div><strong>Kelas:</strong> {{ $kelas ? $kelas->nama_kelas : 'All' }}</div>
                
                <div class="mt-3 pt-3 border-t dark:border-gray-700">
                    <strong>Elements Data:</strong>
                    <pre class="mt-2 text-xs">{{ json_encode($elements ?? json_decode($template->elements ?? '[]', true), JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
        </div>
    @endforelse
</div>

{{-- Slider Lampiran Nilai (jika dipilih) --}}
@php 
    $selFormat = request('grade_sheet_format');
    $gradeItems = ($selFormat==='prakerin' || $selFormat==='tugas_akhir') ? $paginatedSiswas->filter(fn($s)=> isset($s->nilai_detail_map) && is_array($s->nilai_detail_map) && !empty($s->nilai_detail_map)) : collect();
@endphp
@if($gradeItems->count() > 0)
<div x-data="{ j: 0, total: {{ $gradeItems->count() }} }" class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700">
        <div class="font-semibold dark:text-white">Lampiran Nilai ({{ strtoupper(str_replace('_',' ',$selFormat)) }})</div>
        <div class="flex items-center gap-2">
            <button type="button" class="px-3 py-1 rounded bg-zinc-700 text-white" @click="j = (j - 1 + total) % total">Prev</button>
            <span class="text-sm dark:text-gray-300" x-text="(j+1) + ' / ' + total"></span>
            <button type="button" class="px-3 py-1 rounded bg-zinc-700 text-white" @click="j = (j + 1) % total">Next</button>
        </div>
    </div>
    @foreach($gradeItems as $siswa)
    <div x-show="j === {{ $loop->index }}" class="p-4">
        <div class="grade-frame" style="position:relative; width:100%; max-width:1000px; margin:0 auto; background:#fff; aspect-ratio:297/210; overflow:hidden;">
            <style>
                /* Neutralize Tailwind preflight for images inside grade preview so table alignment works */
                .grade-frame img { display: inline-block !important; max-width: none !important; height: auto; }
            </style>
            <div class="grade-scale" style="position:absolute; top:0; left:50%; width:297mm; height:210mm; transform-origin: top center; transform: translateX(-50%) scale(0.65);">
                @if($selFormat === 'prakerin')
                    @include('master.sertifikat.grades.prakerin', ['siswa' => $siswa, 'signatures' => \App\Models\GradeSignature::first()])
                @elseif($selFormat === 'tugas_akhir')
                    @include('master.sertifikat.grades.tugas_akhir', ['siswa' => $siswa, 'signatures' => \App\Models\GradeSignature::first()])
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Pagination Controls removed as requested --}}

{{-- Debug Info testingggg --}}
<!-- @if(config('app.debug'))
<div class="mt-6 bg-gray-100 dark:bg-gray-900 border-2 border-gray-300 dark:border-gray-700 rounded-lg p-4">
    <h4 class="font-bold text-gray-800 dark:text-gray-300 mb-2">🔍 Debug Info</h4>
    <div class="text-xs overflow-auto max-h-96 bg-white dark:bg-gray-800 p-3 rounded space-y-2">
        <div><strong>Template ID:</strong> {{ $template->id }}</div>
        <div><strong>Template Name:</strong> {{ $template->nama_template }}</div>
        <div><strong>Elements Count:</strong> {{ is_array($elements ?? null) ? count($elements) : (is_array(json_decode($template->elements ?? '[]', true)) ? count(json_decode($template->elements ?? '[]', true)) : 0) }}</div>
        <div><strong>Students Count:</strong> {{ $siswas->count() }}</div>
        <div><strong>Kelas:</strong> {{ $kelas ? $kelas->nama_kelas : 'All' }}</div>
        
        <div class="mt-3 pt-3 border-t dark:border-gray-700">
            <strong>Elements Data:</strong>
            <pre class="mt-2 text-xs">{{ json_encode($elements ?? json_decode($template->elements ?? '[]', true), JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>
</div>
@endif -->
</div>
<div id="toast" class="hidden fixed bottom-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg bg-emerald-600 text-white text-sm"></div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var select = document.getElementById('destinationSelect');
        var emailWrap = document.getElementById('emailFieldWrapper');
        var form = document.getElementById('certGenerateForm');
        var toast = document.getElementById('toast');
        function toggleEmail() {
            if (!select || !emailWrap) return;
            emailWrap.classList.toggle('hidden', select.value !== 'email');
        }
        if (select) {
            select.addEventListener('change', toggleEmail);
            toggleEmail();
        }

        function showToast(message, durationMs) {
            if (!toast) return;
            toast.textContent = message;
            toast.classList.remove('hidden');
            setTimeout(function(){ toast.classList.add('hidden'); }, durationMs || 2500);
        }

        if (form) {
            form.addEventListener('submit', function(e){
                try {
                    var dest = (document.getElementById('destinationSelect') || {}).value || 'download';
                    var clicked = document.activeElement;
                    var fmt = clicked && clicked.name === 'format' ? clicked.value : null;
                    if (dest === 'download' && (fmt === 'pdf' || fmt === 'png' || fmt === 'jpg' || fmt === 'jpeg')) {
                        showToast('Unduhan sertifikat dimulai...', 2000);
                    }
                } catch (_) {}
            });
        }
    });
</script>
</x-layouts.app>