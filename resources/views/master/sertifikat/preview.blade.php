<x-layouts.app :title="'Preview Sertifikat'">
    <div class="max-w-7xl mx-auto p-4">
        <h2 class="text-2xl font-bold mb-6 dark:text-white">
            Preview Sertifikat - {{ $template->nama_template }}
        </h2>

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

            {{-- Filter: Pilih Template Nilai & Pagination Size --}}
            <form method="GET" action="{{ url()->current() }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
                <input type="hidden" name="kelas_id" value="{{ request('kelas_id') }}" />
                @foreach((array)request('siswa_ids', []) as $sid)
                    <input type="hidden" name="siswa_ids[]" value="{{ $sid }}" />
                @endforeach
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Template Nilai (opsional)</label>
                    <select name="grade_template_id" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:text-gray-100">
                        <option value="">-- Tanpa Nilai --</option>
                        @foreach($gradeTemplates as $gt)
                            <option value="{{ $gt->id }}" {{ (string)$gt->id === (string)request('grade_template_id', $gradeTemplateId ?? null) ? 'selected' : '' }}>{{ $gt->nama_template }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Jika dipilih, preview menampilkan hanya siswa yang memiliki nilai pada template ini.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Per halaman</label>
                    <input type="number" min="1" name="per_page" value="{{ (int)request('per_page', 6) }}" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:text-gray-100">
                </div>
                <div class="flex items-end">
                    <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded">Terapkan</button>
                </div>
            </form>

            {{-- Form Generate & Download/Email/Storage --}}
            <form action="{{ route('master.sertifikat.generate.process') }}" method="POST">
                @csrf
                <input type="hidden" name="template_id" value="{{ $template->id }}">
                @if($kelas)
                    <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                @endif

                {{-- Tujuan pengiriman --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Tujuan</label>
                        <select name="destination" id="destinationSelect" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:text-gray-100">
                            <option value="download" selected>Download ke perangkat</option>
                            <option value="email">Kirim ke email</option>
                            <option value="storage">Simpan ke storage</option>
                        </select>
                    </div>
                    <div id="emailFieldWrapper" class="md:col-span-2 hidden">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Email tujuan</label>
                        <input type="email" name="email" placeholder="contoh: nama@domain.com" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:text-gray-100">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Jika memilih Kirim ke email, masukkan alamat email penerima.</p>
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
                    
                    <!-- PNG Button -->
                    <button type="submit" name="format" value="png" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all inline-flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <div class="text-left">
                            <div class="font-bold">PNG</div>
                            <div class="text-xs opacity-80">ZIP file</div>
                        </div>
                    </button>
                    
                    <!-- JPG Button -->
                    <button type="submit" name="format" value="jpg" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all inline-flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <div class="text-left">
                            <div class="font-bold">JPG</div>
                            <div class="text-xs opacity-80">ZIP file</div>
                        </div>
                    </button>
                    
                    <!-- JPEG Button -->
                    <button type="submit" name="format" value="jpeg" class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all inline-flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <div class="text-left">
                            <div class="font-bold">JPEG</div>
                            <div class="text-xs opacity-80">ZIP file</div>
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
                                <li><strong>PDF:</strong> Semua sertifikat dalam 1 file PDF</li>
                                <li><strong>PNG/JPG/JPEG:</strong> Sertifikat per siswa dalam file ZIP</li>
                                <li><strong>Kualitas:</strong> PNG (lossless), JPG/JPEG (compressed)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- Preview Sertifikat dengan Elements --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @forelse ($paginatedSiswas as $siswa)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                    <div class="p-4 bg-gray-100 dark:bg-gray-700 flex items-start justify-between gap-3">
                        <div>
                            <h3 class="font-bold text-lg dark:text-white">{{ $siswa->nama }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                NIS: {{ $siswa->nis }} | Kelas: {{ $siswa->kelas->nama_kelas ?? '-' }}
                            </p>
                        </div>
                        <div class="text-right">
                            @php $eligKey = isset($siswa->id) && $siswa->id ? $siswa->id : spl_object_hash($siswa); $ok = $eligibility[$eligKey] ?? true; @endphp
                            @if($ok)
                                <span class="inline-flex items-center rounded bg-green-100 text-green-700 px-2 py-0.5 text-xs">Eligible</span>
                            @else
                                <span class="inline-flex items-center rounded bg-red-100 text-red-700 px-2 py-0.5 text-xs">Tidak Eligible</span>
                            @endif
                            @if(isset($siswa->id) && $siswa->id)
                            @can('menu.sertifikat')
                                <form action="{{ route('master.sertifikat.eligibility.override') }}" method="POST" class="mt-2 space-y-1">
                                    @csrf
                                    <input type="hidden" name="siswa_id" value="{{ $siswa->id }}" />
                                    <input type="hidden" name="tahun_ajaran_id" value="" />
                                    @if(!$ok)
                                        <input type="hidden" name="granted" value="1" />
                                        <button class="rounded bg-blue-600 text-white px-2 py-1 text-xs hover:bg-blue-700">Override: Izinkan</button>
                                    @else
                                        <input type="hidden" name="granted" value="0" />
                                        <button class="rounded bg-zinc-700 text-white px-2 py-1 text-xs hover:bg-zinc-800">Cabut Override</button>
                                    @endif
                                </form>
                            @endcan
                            @endif
                        </div>
                    </div>
                    
                    {{-- Canvas Preview - UKURAN & STYLING SAMA PERSIS DENGAN CUSTOMIZE --}}
                    <div class="relative w-full bg-gray-200 dark:bg-gray-900 mx-auto overflow-hidden" 
                         style="aspect-ratio: 297/210; max-width: 1000px;">
                        {{-- Background Image --}}
                        @if($template->background_image)
                            <img src="{{ asset('storage/' . $template->background_image) }}" 
                                 class="absolute inset-0 w-full h-full object-cover" 
                                 alt="Background">
                        @endif

                        {{-- Ambil elements dari database --}}
                        @php
                            $elementsToRender = [];
                            
                            if ($template->elements && is_string($template->elements)) {
                                $elementsToRender = json_decode($template->elements, true) ?? [];
                            }
                            
                            // Fallback ke default jika kosong
                            if (empty($elementsToRender)) {
                                $elementsToRender = [
                                    [
                                        'type' => 'text',
                                        'content' => 'CERTIFICATE',
                                        'x' => 50,
                                        'y' => 20,
                                        'fontSize' => 36,
                                        'fontFamily' => 'Arial',
                                        'color' => '#000000',
                                        'bold' => true,
                                        'align' => 'center'
                                    ]
                                ];
                            }
                        @endphp

                        {{-- Render Elements - STYLING PERSIS SEPERTI CUSTOMIZE --}}
                        @foreach($elementsToRender as $index => $element)
                            @php
                                $content = '';
                                
                                // Generate content berdasarkan tipe element
                                if($element['type'] === 'text') {
                                    $content = $element['content'] ?? '';
                                } elseif($element['type'] === 'variable') {
                                    // Gunakan value atau generate dari variable
                                    $content = match($element['variable'] ?? '') {
                                        '$nama_siswa' => $siswa->nama,
                                        '$kelas' => $siswa->kelas->nama_kelas ?? '',
                                        '$nis' => $siswa->nis ?? '',
                                        '$tanggal' => ($siswa->tanggal_custom ?? null) ? \Carbon\Carbon::parse($siswa->tanggal_custom)->locale('id')->isoFormat('D MMMM YYYY') : now()->locale('id')->isoFormat('D MMMM YYYY'),
                                        '$jurusan' => $siswa->kelas->jurusan->nama_jurusan ?? '',
                                        '$nilai' => $siswa->nilai ?? '-',
                                        '$peringkat' => $siswa->peringkat ?? '-',
                                        '$ttd' => '(Tanda Tangan)',
                                        default => $element['value'] ?? $element['variable'] ?? ''
                                    };
                                } elseif($element['type'] === 'image') {
                                    $content = ''; // Image tidak butuh text content
                                }
                                
                                // Gunakan styling yang PERSIS SAMA seperti di customize
                                $fontSize = $element['fontSize'] ?? 24;
                                $fontFamily = $element['fontFamily'] ?? 'Arial';
                                $color = $element['color'] ?? '#000000';
                                $bold = $element['bold'] ?? false;
                                $align = $element['align'] ?? 'center';
                                $x = $element['x'] ?? 50;
                                $y = $element['y'] ?? 50;
                            @endphp

                            {{-- 🔥 OUTER WRAPPER - SAMA DENGAN CUSTOMIZE (px-2 py-1) --}}
                            <div class="absolute cursor-move px-2 py-1 select-none"
                                 style="
                                    left: {{ $x }}%;
                                    top: {{ $y }}%;
                                    transform: translate(-50%, -50%);
                                    font-size: {{ $fontSize }}px;
                                    font-family: {{ $fontFamily }}, sans-serif;
                                    color: {{ $color }};
                                    font-weight: {{ $bold ? 'bold' : 'normal' }};
                                    text-align: {{ $align }};
                                    min-width: 100px;
                                    pointer-events: none;
                                 ">
                                
                                {{-- INNER CONTENT --}}
                                @if($element['type'] === 'text')
                                    {{-- Text Element: Langsung tampil --}}
                                    <div class="whitespace-pre-wrap">{{ $content }}</div>
                                @elseif($element['type'] === 'variable')
                                    {{-- Variable Element: Tanpa background kuning di preview --}}
                                    <div class="px-2 py-1">
                                        <span class="font-medium">{{ $content }}</span>
                                    </div>
                                @elseif($element['type'] === 'image')
                                    {{-- Image Element --}}
                                    <img src="{{ $element['src'] ?? '' }}" 
                                         style="width: {{ $element['width'] ?? 100 }}px; height: {{ $element['height'] ?? 100 }}px; object-fit: contain;"
                                         alt="Uploaded Image">
                                @endif
                            </div>
                        @endforeach
                    </div>
                    @if(!empty($siswa->nilai))
                        <div class="border-t dark:border-gray-700 p-3 bg-gray-50 dark:bg-gray-900">
                            <div class="text-xs text-gray-600 dark:text-gray-300"><strong>Ringkasan Nilai:</strong> {{ $siswa->nilai }}</div>
                        </div>
                    @endif
                </div>
                @if(isset($siswa->nilai_detail_map) && is_array($siswa->nilai_detail_map) && !empty($siswa->nilai_detail_map))
                    <div class="mt-3 bg-white dark:bg-gray-800 border-t dark:border-gray-700">
                        <div class="px-4 py-2 font-semibold text-sm dark:text-white">Lampiran Nilai (Preview)</div>
                        <div class="px-4 pb-4 overflow-x-auto">
                            @php $cmp = $siswa->nilai_computed ?? []; @endphp
                            <table class="min-w-full border text-xs">
                                <thead class="bg-gray-100 dark:bg-gray-900">
                                    <tr>
                                        <th class="border px-2 py-1 w-10">No</th>
                                        <th class="border px-2 py-1">Komponen</th>
                                        <th class="border px-2 py-1">Uraian</th>
                                        <th class="border px-2 py-1 w-24">Nilai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $i=1; $map=$siswa->nilai_detail_map; @endphp
                                    @foreach($map as $komponen=>$subs)
                                        @if(is_array($subs))
                                            @foreach($subs as $uraian=>$nilai)
                                                <tr>
                                                    <td class="border px-2 py-1 text-center">{{ $i++ }}</td>
                                                    <td class="border px-2 py-1">{{ $komponen }}</td>
                                                    <td class="border px-2 py-1">{{ $uraian }}</td>
                                                    <td class="border px-2 py-1 text-center">{{ is_numeric($nilai)? number_format($nilai,2):$nilai }}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td class="border px-2 py-1 text-center">{{ $i++ }}</td>
                                                <td class="border px-2 py-1">-</td>
                                                <td class="border px-2 py-1">{{ $komponen }}</td>
                                                <td class="border px-2 py-1 text-center">{{ is_numeric($subs)? number_format($subs,2):$subs }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="mt-2 text-xs text-gray-600 dark:text-gray-300">
                                @if(isset($cmp['total']))<span class="mr-3">Total: <strong>{{ $cmp['total'] }}</strong></span>@endif
                                @if(isset($cmp['avg']))<span class="mr-3">Rata-rata: <strong>{{ $cmp['avg'] }}</strong></span>@endif
                                @if(isset($cmp['weighted_avg']))<span class="mr-3">Rata-rata Berbobot: <strong>{{ $cmp['weighted_avg'] }}</strong></span>@endif
                                @if(isset($cmp['grade']))<span class="mr-3">Predikat: <strong>{{ $cmp['grade'] }}</strong></span>@endif
                            </div>
                        </div>
                    </div>
                @endif
            @empty
                <div class="col-span-2">
                    <div class="bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-400 p-4 rounded">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <p class="text-yellow-800 dark:text-yellow-300 font-medium">
                                Tidak ada siswa yang dipilih. Silakan pilih kelas terlebih dahulu.
                            </p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Pagination Controls --}}
        <div class="mt-6">
            {{ $paginatedSiswas->withQueryString()->links() }}
        </div>

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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var select = document.getElementById('destinationSelect');
            var emailWrap = document.getElementById('emailFieldWrapper');
            function toggleEmail() {
                if (!select || !emailWrap) return;
                emailWrap.classList.toggle('hidden', select.value !== 'email');
            }
            if (select) {
                select.addEventListener('change', toggleEmail);
                toggleEmail();
            }
        });
    </script>
</x-layouts.app>