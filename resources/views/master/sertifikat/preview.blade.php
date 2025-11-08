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
                <a href="{{ route('master.sertifikat.generate.customize', $template->id) }}{{ $kelas ? '?kelas_id=' . $kelas->id : '' }}" 
                   class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    Edit Template
                </a>
            </div>

            {{-- Form Generate & Download --}}
            <form action="{{ route('master.sertifikat.generate.process') }}" method="POST">
                @csrf
                <input type="hidden" name="template_id" value="{{ $template->id }}">
                @if($kelas)
                    <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                @endif

                <div class="flex flex-wrap gap-3">
                    <button type="submit" name="format" value="pdf" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all inline-flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Download PDF
                    </button>
                    <button type="submit" name="format" value="png" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all inline-flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Download PNG
                    </button>
                    <button type="submit" name="format" value="jpg" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all inline-flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Download JPG
                    </button>
                </div>
            </form>
        </div>

        {{-- Preview Sertifikat dengan Elements --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @forelse ($siswas->take(6) as $siswa)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                    <div class="p-4 bg-gray-100 dark:bg-gray-700">
                        <h3 class="font-bold text-lg dark:text-white">{{ $siswa->nama }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            NIS: {{ $siswa->nis }} | Kelas: {{ $siswa->kelas->nama_kelas ?? '-' }}
                        </p>
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
                                        '$tanggal' => now()->locale('id')->isoFormat('D MMMM YYYY'),
                                        '$jurusan' => $siswa->kelas->jurusan->nama_jurusan ?? '',
                                        '$nilai' => $siswa->nilai ?? '-',
                                        '$peringkat' => $siswa->peringkat ?? '-',
                                        '$ttd' => '(Tanda Tangan)',
                                        default => $element['value'] ?? $element['variable'] ?? ''
                                    };
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
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
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

        @if($siswas->count() > 6)
            <div class="mt-6 text-center">
                <div class="bg-blue-50 dark:bg-blue-900 border-l-4 border-blue-500 p-4 rounded inline-block">
                    <p class="text-blue-800 dark:text-blue-300 font-medium">
                        📄 Menampilkan 6 dari {{ $siswas->count() }} siswa. 
                        <span class="font-bold">Download untuk melihat semua sertifikat.</span>
                    </p>
                </div>
            </div>
        @endif

        {{-- Debug Info testingggg --}}
        <!-- @if(config('app.debug'))
        <div class="mt-6 bg-gray-100 dark:bg-gray-900 border-2 border-gray-300 dark:border-gray-700 rounded-lg p-4">
            <h4 class="font-bold text-gray-800 dark:text-gray-300 mb-2">🔍 Debug Info</h4>
            <div class="text-xs overflow-auto max-h-96 bg-white dark:bg-gray-800 p-3 rounded space-y-2">
                <div><strong>Template ID:</strong> {{ $template->id }}</div>
                <div><strong>Template Name:</strong> {{ $template->nama_template }}</div>
                <div><strong>Elements Count:</strong> {{ count($elementsToRender ?? []) }}</div>
                <div><strong>Students Count:</strong> {{ $siswas->count() }}</div>
                <div><strong>Kelas:</strong> {{ $kelas ? $kelas->nama_kelas : 'All' }}</div>
                
                <div class="mt-3 pt-3 border-t dark:border-gray-700">
                    <strong>Elements Data:</strong>
                    <pre class="mt-2 text-xs">{{ json_encode($elementsToRender, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
        </div>
        @endif -->
    </div>
</x-layouts.app>