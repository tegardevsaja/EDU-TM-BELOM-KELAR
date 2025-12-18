<x-layouts.app :title="'Customisasi Sertifikat'">
<div class="max-w-7xl mx-auto p-4" x-data="certificateCustomizer()" x-init="init()">

    {{-- Canvas dengan aspect ratio dan ukuran yang SAMA dengan preview --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <div class="mb-3 flex items-center justify-between">
            <div class="text-sm text-gray-600 dark:text-gray-400">
                <span class="font-semibold">Canvas Sertifikat</span> - Aspect Ratio: A4 Landscape (297:210)
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-500">
                💡 Klik elemen untuk edit, drag untuk pindah posisi
            </div>
        </div>
        <div class="bg-gray-100 dark:bg-gray-900 rounded-lg w-full mx-auto relative overflow-hidden border-2 border-dashed border-gray-300 dark:border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" 
             style="aspect-ratio: 297/210; max-width: 1122px;"
             x-ref="canvas" 
             tabindex="0"
             @click="selectedElement=null; isEditingText=false; $refs.canvas.focus()"
             @keydown="handleKeydown($event)">
            {{-- Background --}}
            <div x-show="backgroundImage" class="absolute inset-0 bg-cover bg-center" :style="`background-image: url(${backgroundImage})`"></div>
            
            {{-- Elements --}}
            <template x-for="(element,index) in elements" :key="element.id">
                <div
                    @mousedown.stop.prevent="startDrag($event,index)"
                    @click.stop="selectedElement=index; $refs.canvas.focus()"
                    @dblclick.stop="startInlineEdit(index)"
                    :class="selectedElement===index?'ring-4 ring-blue-500 shadow-xl z-50':'ring-1 ring-transparent hover:ring-blue-300'"
                    class="absolute cursor-move select-none transition-all duration-200"
                    :style="`left:${element.x}%;top:${element.y}%;transform:translate(${element.align==='left' ? '0' : (element.align==='right' ? '-100%' : '-50%')},-50%);font-size:${element.fontSize}px;font-family:${element.fontFamily};color:${element.color};font-weight:${element.bold?'bold':'normal'};text-align:${element.align};min-width:0;`"
                >
                    {{-- Text Elements --}}
                    <template x-if="element.type==='text'">
                        <div
                            :contenteditable="isEditingText && selectedElement===index"
                            @blur="finishInlineEdit($event)"
                            @keydown.enter.prevent="finishInlineEdit($event)"
                            class="whitespace-pre-wrap pointer-events-auto bg-white/70 dark:bg-black/20 rounded px-1"
                            x-text="!isEditingText || selectedElement!==index ? element.content : ''"
                            x-show="!(isEditingText && selectedElement===index && false)"></div>
                    </template>
                    
                    {{-- Variable Elements --}}
                    <template x-if="element.type==='variable'">
                        <div class="bg-yellow-100 dark:bg-yellow-900 bg-opacity-60 rounded px-2 py-1 pointer-events-auto">
                            <span x-text="getVariableLabel(element.variable)" class="font-medium"></span>
                        </div>
                    </template>
                    
                    {{-- Image Elements --}}
                    <template x-if="element.type==='image'">
                        <div class="rounded border-2 border-dashed border-gray-400 dark:border-gray-500 relative overflow-hidden bg-transparent"
                             :style="`width: ${element.width || 100}px; height: ${element.height || 100}px;`">
                            <img :src="element.src" class="w-full h-full object-contain" x-show="element.src">
                            <div x-show="!element.src" class="absolute inset-0 flex items-center justify-center text-xs text-gray-500">
                                IMG
                            </div>
                            {{-- Resize handles --}}
                            <div x-show="selectedElement===index" class="absolute bottom-0 right-0 w-3 h-3 bg-blue-500 cursor-se-resize"
                                 @mousedown.stop.prevent="startImageResize($event, index)"></div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Info jika tidak ada elemen --}}
            <div x-show="elements.length === 0" class="absolute inset-0 flex items-center justify-center text-gray-400 dark:text-gray-500 pointer-events-none">
                <div class="text-center">
                    <svg class="w-20 h-20 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-xl font-semibold">Canvas kosong</p>
                    <p class="text-sm mt-2">Tambahkan elemen menggunakan tools di bawah</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Input Nilai (tampil jika template nilai dipilih dan bukan template auto import) --}}
    @if(!empty($gradeTemplateId) && $gradeTemplate)
    @php
        $isAutoGradeTemplate = \Illuminate\Support\Str::contains($gradeTemplate->nama_template ?? '', '(Auto)');
    @endphp
    @unless($isAutoGradeTemplate)
    <div class="mt-6 bg-white dark:bg-gray-800 shadow-lg rounded-lg p-5">
        <div class="flex items-center mb-4">
            <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <h3 class="font-bold text-lg dark:text-white">Input Nilai - {{ $gradeTemplate->nama_template }}</h3>
        </div>

        <form action="{{ route('master.sertifikat.grade.store', $gradeTemplateId) }}" method="POST" class="space-y-5" id="form-nilai">
            @csrf
            <input type="hidden" name="redirect" value="{{ request()->fullUrl() }}">
            <input type="hidden" name="computed[total]" id="hf-total">
            <input type="hidden" name="computed[avg]" id="hf-avg">
            <input type="hidden" name="computed[weighted_avg]" id="hf-weighted-avg">
            <input type="hidden" name="computed[grade]" id="hf-grade">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-1">
                    <label class="block text-sm font-medium mb-1">Pilih Siswa</label>
                    @php $existingIds = array_keys($existingPenilaianBySiswa ?? []); @endphp
                    <select name="siswa_id" class="w-full rounded border px-3 py-2" required>
                        <option value="">-- Pilih Siswa --</option>
                        @foreach($siswaList as $s)
                            @if(!in_array($s->id, $existingIds))
                                <option value="{{ $s->id }}">{{ $s->nama }} ({{ $s->kelas->nama_kelas ?? '-' }})</option>
                            @endif
                        @endforeach
                    </select>
                    @if(count($siswaList) === count($existingIds))
                        <p class="mt-2 text-xs text-zinc-500">Semua siswa pada daftar ini sudah memiliki penilaian.</p>
                    @endif
                </div>
                <div class="md:col-span-2">
                    @php 
                        $komponen = is_array($gradeTemplate->komponen) ? $gradeTemplate->komponen : []; 
                        // Normalize: support either
                        // 1) mapped groups: ['Nilai Akademik' => ['kehadiran','kedisiplinan']]
                        // 2) array of objects: [{kategori:'Nilai Akademik', subkomponen:[{uraian:'kehadiran'}, ...]}]
                        $normalized = [];
                        if (!empty($komponen)) {
                            $first = reset($komponen);
                            if (is_array($first) && array_key_exists('kategori', $first)) {
                                foreach ($komponen as $row) {
                                    $g = (string)($row['kategori'] ?? 'Komponen');
                                    $subs = [];
                                    foreach (($row['subkomponen'] ?? []) as $subRow) {
                                        if (is_array($subRow) && isset($subRow['uraian'])) {
                                            $subs[] = (string)$subRow['uraian'];
                                        } elseif (is_string($subRow)) {
                                            $subs[] = $subRow;
                                        }
                                    }
                                    $normalized[$g] = $subs;
                                }
                            } else {
                                // already mapped or flat list
                                $normalized = $komponen;
                            }
                        }
                    @endphp
                    <div class="overflow-x-auto">
                        <table class="min-w-full border text-sm">
                            <thead class="bg-gray-100 dark:bg-gray-900">
                                <tr>
                                    <th class="border px-3 py-2 text-left w-12">No</th>
                                    <th class="border px-3 py-2 text-left">Komponen</th>
                                    <th class="border px-3 py-2 text-right w-48">Nilai (0-100)</th>
                                    <th class="border px-3 py-2 text-right w-36">Bobot (opsional)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $rowNo = 0; @endphp
                                @if(is_array($normalized))
                                    @foreach($normalized as $group => $items)
                                        <tr>
                                            <td colspan="4" class="border px-3 py-2 font-semibold bg-zinc-100 dark:bg-gray-900">{{ $group }}</td>
                                        </tr>
                                        @foreach((array)$items as $sub)
                                            @php 
                                                $rowNo++;
                                                $labelText = trim($group.' - '.$sub);
                                            @endphp
                                            <tr class="odd:bg-white even:bg-zinc-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                                <td class="border px-3 py-2 align-middle">{{ $rowNo }}</td>
                                                <td class="border px-3 py-2 align-middle">{{ $sub }}</td>
                                                <td class="border px-3 py-2 align-middle">
                                                    <div class="flex items-center gap-2">
                                                        <input type="number" step="0.01" min="0" max="100" name="nilai[{{ $group }}][{{ $sub }}]" data-label="{{ $labelText }}" class="nilai-field w-full rounded border px-3 py-2" placeholder="0-100" required>
                                                        <span class="text-xs text-zinc-500">/100</span>
                                                    </div>
                                                </td>
                                                <td class="border px-3 py-2 align-middle">
                                                    <div class="flex items-center justify-end gap-2 text-xs">
                                                        <label class="text-zinc-500">Bobot</label>
                                                        <input type="number" step="0.01" min="0" value="1" class="bobot-field w-20 rounded border px-2 py-1" />
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                    {{-- Ringkasan & Pengaturan Hitung --}}
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="rounded border p-3">
                            <div class="text-xs text-zinc-500">Total</div>
                            <div id="sum-nilai" class="text-lg font-semibold">0</div>
                        </div>
                        <div class="rounded border p-3">
                            <div class="text-xs text-zinc-500">Rata-rata</div>
                            <div id="avg-nilai" class="text-lg font-semibold">0</div>
                        </div>
                        <div class="rounded border p-3">
                            <div class="text-xs text-zinc-500 flex items-center gap-2">
                                Rata-rata Berbobot
                                <label class="inline-flex items-center gap-1 text-[11px]">
                                    <input id="use-weight" type="checkbox" class="rounded border" />
                                    Gunakan bobot
                                </label>
                            </div>
                            <div id="weighted-avg-nilai" class="text-lg font-semibold">0</div>
                            <div class="mt-1 text-[11px] text-zinc-500">Total Bobot: <span id="total-bobot">0</span></div>
                        </div>
                    </div>

                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-4 gap-3">
                        <div class="rounded border p-3">
                            <div class="text-xs text-zinc-500">Ambang A (>=)</div>
                            <input type="number" id="thr-a" class="w-full rounded border px-2 py-1 text-sm" value="90">
                        </div>
                        <div class="rounded border p-3">
                            <div class="text-xs text-zinc-500">Ambang B (>=)</div>
                            <input type="number" id="thr-b" class="w-full rounded border px-2 py-1 text-sm" value="80">
                        </div>
                        <div class="rounded border p-3">
                            <div class="text-xs text-zinc-500">Ambang C (>=)</div>
                            <input type="number" id="thr-c" class="w-full rounded border px-2 py-1 text-sm" value="70">
                        </div>
                        <div class="rounded border p-3">
                            <div class="text-xs text-zinc-500">Predikat</div>
                            <div id="grade-nilai" class="text-lg font-semibold">-</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-right">
                <button type="submit" class="rounded bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">Simpan Nilai</button>
            </div>
        </form>
    </div>
    @endunless
    @endif

    

    {{-- Tools Customization --}}
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-4">
        
        {{-- Tambah Elemen --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-5">
            <div class="flex items-center mb-4">
                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <h3 class="font-bold text-lg dark:text-white">Tambah Elemen</h3>
            </div>
            
            {{-- Tambah Teks --}}
            <button 
                @click="addTextElement()" 
                class="w-full bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white px-4 py-3 rounded-lg mb-3 text-sm font-medium transition-colors duration-200 flex items-center justify-center cursor-pointer shadow hover:shadow-md"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Teks
            </button>

            {{-- Upload Gambar/TTD --}}
            <div class="mb-3">
                <input type="file" id="imageUpload" accept="image/*" class="hidden" @change="handleImageUpload($event)">
                <button 
                    @click="$refs.imageUpload.click()" 
                    class="w-full bg-purple-500 hover:bg-purple-600 active:bg-purple-700 text-white px-4 py-3 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center justify-center cursor-pointer shadow hover:shadow-md"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Upload Gambar/TTD
                </button>
                <input type="file" x-ref="imageUpload" accept="image/*" class="hidden" @change="handleImageUpload($event)">
            </div>
            
            {{-- Tambah Variabel --}}
            <div class="space-y-2">
                <label class="block text-sm font-semibold mb-2 dark:text-white">Tambah Data Siswa:</label>
                <select 
                    x-model="selectedVariable" 
                    class="w-full border-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent cursor-pointer transition-all"
                >
                    <option value="">-- Pilih Data --</option>
                    <template x-for="v in variables" :key="v.name">
                        <option :value="v.name" x-text="v.label"></option>
                    </template>
                </select>
                <button 
                    @click="addVariableElement()" 
                    :disabled="!selectedVariable" 
                    :class="selectedVariable ? 'bg-green-500 hover:bg-green-600 active:bg-green-700 cursor-pointer shadow hover:shadow-md' : 'bg-gray-400 cursor-not-allowed opacity-60'"
                    class="w-full text-white px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 flex items-center justify-center"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah ke Canvas
                </button>
            </div>
        </div>

        {{-- Pengaturan Elemen --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-5">
            <div class="flex items-center mb-4">
                <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                </svg>
                <h3 class="font-bold text-lg dark:text-white">Pengaturan Elemen</h3>
            </div>
            
            <div x-show="selectedElement === null" class="text-center py-8 text-gray-400 dark:text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
                </svg>
                <p class="text-sm">Pilih elemen untuk mengatur</p>
            </div>

            <template x-if="selectedElement !== null && elements[selectedElement]">
                <div class="space-y-4">
                    {{-- Font Size --}}
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 dark:text-white">
                            Ukuran Font: 
                            <span class="text-blue-500" x-text="elements[selectedElement].fontSize + 'px'"></span>
                            <span class="text-xs text-gray-500 ml-2" x-text="getFontSizeLabel(elements[selectedElement].fontSize)"></span>
                        </label>
                        <input 
                            type="range" 
                            x-model.number="elements[selectedElement].fontSize" 
                            class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700"
                            min="8" 
                            max="120"
                        >
                        <div class="flex justify-between text-xs text-gray-500 mt-1">
                            <span>Kecil (8px)</span>
                            <span>Sedang (40px)</span>
                            <span>Besar (120px)</span>
                        </div>
                    </div>

                    {{-- Font Family --}}
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 dark:text-white">Font:</label>
                        <select 
                            x-model="elements[selectedElement].fontFamily" 
                            class="w-full border-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 cursor-pointer transition-all"
                        >
                            <option value="Arial">Arial</option>
                            <option value="Times New Roman">Times New Roman</option>
                            <option value="Georgia">Georgia</option>
                            <option value="Courier New">Courier New</option>
                            <option value="Verdana">Verdana</option>
                        </select>
                    </div>

                    {{-- Color --}}
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 dark:text-white">Warna:</label>
                        <input 
                            type="color" 
                            x-model="elements[selectedElement].color" 
                            class="w-full h-12 border-2 border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer"
                        >
                    </div>

                    {{-- Bold & Align --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex items-center bg-gray-50 dark:bg-gray-900 p-2 rounded-lg">
                            <input 
                                type="checkbox" 
                                x-model="elements[selectedElement].bold" 
                                id="bold" 
                                class="mr-2 w-4 h-4 cursor-pointer"
                            >
                            <label for="bold" class="text-sm font-medium dark:text-white cursor-pointer">Tebal</label>
                        </div>
                        
                        <select 
                            x-model="elements[selectedElement].align" 
                            class="border-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-2 py-1 text-sm cursor-pointer"
                        >
                            <option value="left">← Kiri</option>
                            <option value="center">↔ Tengah</option>
                            <option value="right">→ Kanan</option>
                        </select>
                    </div>

                    {{-- Edit Content (untuk text) --}}
                    <template x-if="elements[selectedElement].type === 'text'">
                        <div>
                            <label class="block text-sm font-semibold mb-1.5 dark:text-white">Teks:</label>
                            <textarea 
                                x-model="elements[selectedElement].content" 
                                class="w-full border-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 transition-all" 
                                rows="3"
                            ></textarea>
                        </div>
                    </template>

                    {{-- Edit Image Size (untuk image) --}}
                    <template x-if="elements[selectedElement].type === 'image'">
                        <div class="space-y-3">
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-sm font-semibold mb-1 dark:text-white">Lebar:</label>
                                    <input type="number" x-model.number="elements[selectedElement].width" 
                                           class="w-full border-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded px-2 py-1 text-sm" 
                                           min="20" max="500">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold mb-1 dark:text-white">Tinggi:</label>
                                    <input type="number" x-model.number="elements[selectedElement].height" 
                                           class="w-full border-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded px-2 py-1 text-sm" 
                                           min="20" max="500">
                                </div>
                            </div>
                            <div class="text-xs text-gray-500">
                                <strong>File:</strong> <span x-text="elements[selectedElement].filename || 'Unknown'"></span>
                            </div>
                        </div>
                    </template>

                    {{-- Variable Info --}}
                    <template x-if="elements[selectedElement].type === 'variable'">
                        <div class="bg-yellow-50 dark:bg-yellow-900 dark:bg-opacity-30 p-3 rounded-lg border-2 border-yellow-300 dark:border-yellow-700">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                <div class="text-sm dark:text-white">
                                    <strong>Variabel:</strong> <span x-text="getVariableLabel(elements[selectedElement].variable)"></span>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- Hapus Elemen --}}
                    <button 
                        @click="deleteElement()" 
                        class="w-full bg-red-500 hover:bg-red-600 active:bg-red-700 text-white px-4 py-3 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center justify-center cursor-pointer shadow hover:shadow-md"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Hapus Elemen
                    </button>
                </div>
            </template>
        </div>

        {{-- Actions --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-5">
            <div class="flex items-center mb-4">
                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <h3 class="font-bold text-lg dark:text-white">Aksi</h3>
            </div>
            
            <div class="space-y-3">
                <button 
                    @click="saveTemplate()" 
                    class="w-full bg-green-600 hover:bg-green-700 active:bg-green-800 text-white px-6 py-4 rounded-lg font-semibold transition-colors duration-200 flex items-center justify-center cursor-pointer shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                    Simpan Template
                </button>
                
                <button 
                    @click="previewCertificate()" 
                    class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white px-6 py-4 rounded-lg font-semibold transition-colors duration-200 flex items-center justify-center cursor-pointer shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Preview Sertifikat
                </button>

                <div class="pt-4 border-t dark:border-gray-700">
                    <div class="bg-blue-50 dark:bg-blue-900 dark:bg-opacity-30 p-3 rounded-lg">
                        <p class="text-xs text-blue-800 dark:text-blue-300 font-medium">
                            💡 <strong>Tips:</strong> Klik dan drag elemen untuk memindahkan posisi
                        </p>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-900 p-3 rounded-lg">
                    <div class="text-xs dark:text-gray-400 space-y-1">
                        <div class="flex justify-between">
                            <span>Total Elemen:</span>
                            <span class="font-bold text-blue-600 dark:text-blue-400" x-text="elements.length"></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Template ID:</span>
                            <span class="font-bold" x-text="templateId"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

   

</div>
<script>
function certificateCustomizer() {
    return {
        templateId: {{ $template->id }},
        siswaList: @json($siswaList),
        elements: [], // Will be initialized in init()
        selectedElement: null,
        selectedVariable: '',
        backgroundImage: '{{ url("storage/".$template->background_image) }}',
        elementIdCounter: 1000,
        isEditingText: false,
        history: [],
        future: [],

        variables: [
            {name:'$nama_siswa', label:'$Nama', sample:'Tegar Kurniawan'},
            {name:'$kelas', label:'$Kelas', sample:'XII RPL'},
            {name:'$nis', label:'$NIS', sample:'22100001'},
            {name:'$tanggal', label:'$Tanggal Cetak', sample:'17 November 2025'},
            {name:'$jurusan', label:'$Jurusan', sample:'Rekayasa Perangkat Lunak'},
            {name:'$nomor_sertifikat', label:'$No Sertifikat', sample:'001/SMK-TM/11/17/2025'},
        ],

        // Initialize elements from PHP
        init() {
            const elementsFromPhp = @json($elements);
            if (Array.isArray(elementsFromPhp) && elementsFromPhp.length > 0) {
                this.elements = elementsFromPhp;
                // Set counter to max ID + 1
                const maxId = Math.max(...this.elements.map(el => el.id || 0));
                this.elementIdCounter = maxId + 1;
            } else {
                this.elements = [];
                this.elementIdCounter = 1;
            }

            // Global keydown fallback if canvas had recent interaction
            const keyHandler = (e) => this.handleKeydown(e);
            document.addEventListener('keydown', keyHandler);
            this.$watch('selectedElement', () => { /* keep handler alive */ });
        },

        addTextElement() {
            const newElement = {
                id: this.elementIdCounter++,
                type: 'text',
                content: 'Teks Baru',
                x: 50,
                y: 50,
                fontSize: 24,
                fontFamily: 'Arial',
                color: '#000000',
                bold: false,
                align: 'center'
            };
            this.elements.push(newElement);
            this.selectedElement = this.elements.length - 1;
        },

        addVariableElement() {
            if (!this.selectedVariable) {
                alert('⚠️ Pilih data siswa terlebih dahulu!');
                return;
            }
            
            const selectedVar = this.variables.find(v => v.name === this.selectedVariable);
            
            if (!selectedVar) {
                console.error('❌ Variable not found:', this.selectedVariable);
                return;
            }

            const newElement = {
                id: this.elementIdCounter++,
                type: 'variable',
                variable: this.selectedVariable,
                value: selectedVar.label,
                x: 50,
                y: 50,
                fontSize: 20,
                fontFamily: 'Arial',
                color: '#000000',
                bold: true,
                align: 'center'
            };

            this.elements.push(newElement);
            this.selectedElement = this.elements.length - 1;
            this.selectedVariable = '';
        },

        deleteElement() {
            if (this.selectedElement === null) return;
            this.elements.splice(this.selectedElement, 1);
            this.selectedElement = null;
        },

        startDrag(e, idx) {
            this.selectedElement = idx;
            const canvas = this.$refs.canvas;
            const rect = canvas.getBoundingClientRect();
            
            const move = (ev) => {
                if (!this.elements[idx]) return;
                const x = ((ev.clientX - rect.left) / rect.width) * 100;
                const y = ((ev.clientY - rect.top) / rect.height) * 100;
                this.elements[idx].x = Math.max(0, Math.min(100, x));
                this.elements[idx].y = Math.max(0, Math.min(100, y));
            };

            const up = () => {
                document.removeEventListener('mousemove', move);
                document.removeEventListener('mouseup', up);
                console.log('📍 Element position updated:', {
                    id: this.elements[idx].id,
                    x: this.elements[idx].x,
                    y: this.elements[idx].y
                });
                this.pushHistory();
            };

            document.addEventListener('mousemove', move);
            document.addEventListener('mouseup', up);
        },

        startInlineEdit(index) {
            if (!this.elements[index]) return;
            if (this.elements[index].type !== 'text') return;
            this.selectedElement = index;
            this.isEditingText = true;
            // Focus canvas to receive keyboard but let contenteditable handle text
            setTimeout(() => {
                const nodes = this.$refs.canvas.querySelectorAll('[contenteditable]');
                if (nodes && nodes.length) {
                    const el = nodes[nodes.length-1];
                    // Put current text inside the element for editing
                    el.innerText = this.elements[index].content || '';
                    el.focus();
                    // Move caret to end
                    const range = document.createRange();
                    range.selectNodeContents(el);
                    range.collapse(false);
                    const sel = window.getSelection();
                    sel.removeAllRanges();
                    sel.addRange(range);
                }
            }, 10);
        },

        finishInlineEdit(ev) {
            if (this.selectedElement===null) return;
            if (this.elements[this.selectedElement]?.type !== 'text') { this.isEditingText=false; return; }
            const newText = (ev?.target?.innerText ?? '').trim();
            this.elements[this.selectedElement].content = newText;
            this.isEditingText = false;
            this.pushHistory();
        },

        handleKeydown(e) {
            // If currently editing text or an input is focused, don't handle global keys
            const ae = document.activeElement;
            const isInputFocus = ae && (ae.tagName==='INPUT' || ae.tagName==='TEXTAREA' || ae.isContentEditable);
            if (this.isEditingText || isInputFocus) return;
            if (this.selectedElement===null) {
                // Undo/Redo should still work without selection
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase()==='z') { this.undo(); e.preventDefault(); return; }
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase()==='y') { this.redo(); e.preventDefault(); return; }
                return;
            }
            const el = this.elements[this.selectedElement];
            if (!el) return;
            const step = e.shiftKey ? 1 : 0.5; // percent
            let consumed = true;
            if (e.key === 'ArrowUp') {
                el.y = Math.max(0, el.y - step);
            } else if (e.key === 'ArrowDown') {
                el.y = Math.min(100, el.y + step);
            } else if (e.key === 'ArrowLeft') {
                el.x = Math.max(0, el.x - step);
            } else if (e.key === 'ArrowRight') {
                el.x = Math.min(100, el.x + step);
            } else if (e.key === 'PageUp') {
                el.y = Math.max(0, el.y - 5);
            } else if (e.key === 'PageDown') {
                el.y = Math.min(100, el.y + 5);
            } else if (e.key === 'Home') {
                el.x = Math.max(0, el.x - 5);
            } else if (e.key === 'End') {
                el.x = Math.min(100, el.x + 5);
            } else if (e.key === 'Delete' || e.key === 'Backspace') {
                this.deleteElement();
            } else if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase()==='z') {
                this.undo();
            } else if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase()==='y') {
                this.redo();
            } else {
                consumed = false;
            }
            if (consumed) e.preventDefault();
            if (consumed && !['Delete','Backspace'].includes(e.key)) this.pushHistory();
        },

        // History helpers
        snapshot() {
            return JSON.parse(JSON.stringify({ elements: this.elements, selected: this.selectedElement }));
        },
        pushHistory() {
            // Limit history size
            this.history.push(this.snapshot());
            if (this.history.length > 50) this.history.shift();
            // Clear redo stack when new action occurs
            this.future = [];
        },
        undo() {
            if (this.history.length === 0) return;
            const current = this.snapshot();
            const prev = this.history.pop();
            this.future.push(current);
            this.elements = prev.elements;
            this.selectedElement = prev.selected;
        },
        redo() {
            if (this.future.length === 0) return;
            const current = this.snapshot();
            const next = this.future.pop();
            this.history.push(current);
            this.elements = next.elements;
            this.selectedElement = next.selected;
        },

        // Image upload handler
        handleImageUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            const reader = new FileReader();
            reader.onload = (e) => {
                const newElement = {
                    id: this.elementIdCounter++,
                    type: 'image',
                    src: e.target.result,
                    x: 50,
                    y: 50,
                    width: 100,
                    height: 100,
                    filename: file.name
                };
                this.elements.push(newElement);
                this.selectedElement = this.elements.length - 1;
                this.pushHistory();
            };
            reader.readAsDataURL(file);
            // Clear input
            event.target.value = '';
        },

        // Image resize handler
        startImageResize(e, idx) {
            if (!this.elements[idx] || this.elements[idx].type !== 'image') return;
            this.selectedElement = idx;
            const canvas = this.$refs.canvas;
            const rect = canvas.getBoundingClientRect();
            const startX = e.clientX;
            const startY = e.clientY;
            const startWidth = this.elements[idx].width || 100;
            const startHeight = this.elements[idx].height || 100;
            
            const resize = (ev) => {
                const deltaX = ev.clientX - startX;
                const deltaY = ev.clientY - startY;
                this.elements[idx].width = Math.max(20, startWidth + deltaX);
                this.elements[idx].height = Math.max(20, startHeight + deltaY);
            };

            const stopResize = () => {
                document.removeEventListener('mousemove', resize);
                document.removeEventListener('mouseup', stopResize);
                this.pushHistory();
            };

            document.addEventListener('mousemove', resize);
            document.addEventListener('mouseup', stopResize);
        },

        getVariableSample(name) {
            const v = this.variables.find(x => x.name === name);
            return v ? v.sample : name;
        },

        getVariableLabel(name) {
            const v = this.variables.find(x => x.name === name);
            return v ? v.label : name;
        },

        getFontSizeLabel(size) {
            if (size <= 16) return '(Sangat Kecil)';
            if (size <= 24) return '(Kecil)';
            if (size <= 36) return '(Sedang)';
            if (size <= 48) return '(Besar)';
            return '(Sangat Besar)';
        },

        async saveTemplate() {
            try {
                if (this.elements.length === 0) {
                    alert('⚠️ Tidak ada elemen untuk disimpan. Tambahkan minimal 1 elemen terlebih dahulu.');
                    return;
                }

                console.log('💾 Starting save process...');
                console.log('📦 Elements to save:', this.elements);

                for (let i = 0; i < this.elements.length; i++) {
                    const el = this.elements[i];
                    if (!el.type || !el.id) {
                        alert(`⚠️ Element index ${i} tidak valid: type dan id wajib ada`);
                        console.error('❌ Invalid element:', el);
                        return;
                    }
                }

                const loadingAlert = this.showNotification('Menyimpan template...', 'info');

                const elementsToSave = this.elements
                    .filter(el => el && el.id !== undefined && el.type)
                    .map(el => ({
                    id: el.id,
                    type: el.type,
                    content: el.content || '',
                    variable: el.variable || '',
                    value: el.value || '',
                    x: parseFloat(el.x) || 50,
                    y: parseFloat(el.y) || 50,
                    fontSize: parseInt(el.fontSize) || 24,
                    fontFamily: el.fontFamily || 'Arial',
                    color: el.color || '#000000',
                    bold: !!el.bold,
                    align: el.align || 'center',
                    // Image specific fields
                    src: el.src || '',
                    width: parseInt(el.width) || 100,
                    height: parseInt(el.height) || 100,
                    filename: el.filename || ''
                }));

                console.log('📤 Payload to send:', {
                    template_id: this.templateId,
                    elements_count: elementsToSave.length,
                    elements: elementsToSave
                });

                const payload = {
                    template_id: this.templateId,
                    elements: JSON.stringify(elementsToSave)
                };

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                if (!csrfToken) {
                    throw new Error('CSRF token tidak ditemukan');
                }

                const response = await fetch(`/master/sertifikat/generate/customize/${this.templateId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();
                console.log('📥 Server response:', result);

                if (loadingAlert && loadingAlert.parentElement) {
                    loadingAlert.remove();
                }

                if (response.ok && result.success) {
                    this.showNotification('✅ Template berhasil disimpan! Anda bisa preview sekarang.', 'success');
                    console.log('✅ Template saved successfully');
                } else {
                    throw new Error(result.message || 'Gagal menyimpan template');
                }

            } catch (error) {
                console.error('❌ Error saving template:', error);
                this.showNotification('❌ Gagal menyimpan: ' + error.message, 'error');
            }
        },

        showNotification(message, type = 'info') {
            const existing = document.getElementById('custom-notification');
            if (existing) existing.remove();

            const notification = document.createElement('div');
            notification.id = 'custom-notification';
            notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-2xl transform transition-all duration-300 max-w-md`;
            
            const colors = {
                success: 'bg-green-500 text-white',
                error: 'bg-red-500 text-white',
                info: 'bg-blue-500 text-white'
            };
            
            notification.className += ' ' + (colors[type] || colors.info);
            
            notification.innerHTML = `
                <div class="flex items-center">
                    <span class="text-base font-semibold">${message}</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 10);
            
            if (type !== 'info') {
                setTimeout(() => {
                    notification.style.opacity = '0';
                    notification.style.transform = 'translateX(100%)';
                    setTimeout(() => notification.remove(), 300);
                }, 3000);
            }
            
            return notification;
        },

        previewCertificate() {
            const urlParams = new URLSearchParams(window.location.search);
            const kelasId = urlParams.get('kelas_id') || '';
            const gradeTplId = urlParams.get('grade_template_id') || '';
            // If custom recipients exist, submit a GET form with arrays
            const list = document.getElementById('recipients-list');
            const hasCustom = list && list.children.length > 0;
            if (hasCustom) {
                const form = document.createElement('form');
                form.method = 'GET';
                form.target = '_blank';
                form.action = `/master/sertifikat/generate/preview/${this.templateId}`;
                if (kelasId) {
                    const hid = document.createElement('input');
                    hid.type = 'hidden'; hid.name = 'kelas_id'; hid.value = kelasId; form.appendChild(hid);
                }
                if (gradeTplId) {
                    const h2 = document.createElement('input');
                    h2.type = 'hidden'; h2.name = 'grade_template_id'; h2.value = gradeTplId; form.appendChild(h2);
                }
                // collect recipients
                list.querySelectorAll('.recipient-row').forEach(row => {
                    const fields = ['name','nis','kelas','jurusan','peringkat','tanggal'];
                    fields.forEach(f => {
                        const inp = row.querySelector(`[data-field="${f}"]`);
                        if (!inp) return;
                        const h = document.createElement('input');
                        h.type = 'hidden';
                        h.name = `recipients[${f}][]`;
                        h.value = inp.value || '';
                        form.appendChild(h);
                    });
                });
                document.body.appendChild(form);
                form.submit();
                form.remove();
                return;
            }
            // Jika tidak memilih siswa, tidak memilih kelas, dan belum menambah penerima manual,
            // kirim 1 penerima kosong agar tetap muncul 1 sertifikat
            if (!kelasId && (!this.siswaList || this.siswaList.length === 0)) {
                const form = document.createElement('form');
                form.method = 'GET';
                form.target = '_blank';
                form.action = `/master/sertifikat/generate/preview/${this.templateId}`;
                const fields = ['name','nis','kelas','jurusan','peringkat','tanggal'];
                fields.forEach(f => {
                    const h = document.createElement('input');
                    h.type = 'hidden';
                    h.name = `recipients[${f}][]`;
                    h.value = '';
                    form.appendChild(h);
                });
                document.body.appendChild(form);
                form.submit();
                form.remove();
                return;
            }
            // fallback to class/siswa-based preview
            let url = `/master/sertifikat/generate/preview/${this.templateId}`;
            const qp = new URLSearchParams();
            if (kelasId) qp.set('kelas_id', kelasId);
            if (gradeTplId) qp.set('grade_template_id', gradeTplId);
            const qs = qp.toString();
            if (qs) url += `?${qs}`;
            window.open(url, '_blank');
        }
    }
}
</script>

@if($siswaList->isEmpty())
<script>
    // Repeater for custom recipients
    function addRecipientRow() {
        const c = document.getElementById('recipients-list');
        const wrap = document.createElement('div');
        wrap.className = 'recipient-row grid grid-cols-1 md:grid-cols-6 gap-2 p-3 border rounded';
        wrap.innerHTML = `
            <input data-field="name" class="rounded border px-2 py-1" placeholder="Nama">
            <input data-field="nis" class="rounded border px-2 py-1" placeholder="NIS/NISN">
            <input data-field="kelas" class="rounded border px-2 py-1" placeholder="Kelas">
            <input data-field="jurusan" class="rounded border px-2 py-1" placeholder="Jurusan">
            <input data-field="peringkat" class="rounded border px-2 py-1" placeholder="Peringkat">
            <div class="flex items-center gap-2">
                <input data-field="tanggal" type="date" class="rounded border px-2 py-1 w-full">
                <button type="button" class="px-2 py-1 text-xs rounded bg-red-600 text-white" onclick="this.closest('.recipient-row').remove()">Hapus</button>
            </div>
        `;
        c.appendChild(wrap);
    }
    // Auto tambahkan 1 baris default saat panel pertama kali dibuka dan belum ada baris
    document.addEventListener('DOMContentLoaded', () => {
        const list = document.getElementById('recipients-list');
        if (list && list.children.length === 0) {
            addRecipientRow();
        }
    });
</script>
@endif

<script>
    // Auto-calc Total, Rata-rata, Grade
    (function() {
        const inputs = () => Array.from(document.querySelectorAll('#form-nilai .nilai-field'));
        const bobots = () => Array.from(document.querySelectorAll('#form-nilai .bobot-field'));
        const sumEl = document.getElementById('sum-nilai');
        const avgEl = document.getElementById('avg-nilai');
        const wavgEl = document.getElementById('weighted-avg-nilai');
        const gradeEl = document.getElementById('grade-nilai');
        const tbobotEl = document.getElementById('total-bobot');
        const useWeight = document.getElementById('use-weight');
        const hfTotal = document.getElementById('hf-total');
        const hfAvg = document.getElementById('hf-avg');
        const hfWAvg = document.getElementById('hf-weighted-avg');
        const hfGrade = document.getElementById('hf-grade');
        const thrA = document.getElementById('thr-a');
        const thrB = document.getElementById('thr-b');
        const thrC = document.getElementById('thr-c');

        function calc() {
            const vals = inputs()
                .map(i => parseFloat(i.value))
                .filter(v => !isNaN(v));
            const count = vals.length;
            const sum = vals.reduce((a,b)=>a+b,0);
            const avg = count ? (sum / count) : 0;
            sumEl && (sumEl.textContent = sum.toFixed(2));
            avgEl && (avgEl.textContent = avg.toFixed(2));
            let weighted = 0; let totalBobot = 0;
            if (useWeight && useWeight.checked) {
                const valNodes = inputs();
                const bobotNodes = bobots();
                for (let i=0; i<valNodes.length; i++) {
                    const v = parseFloat(valNodes[i].value);
                    const w = parseFloat(bobotNodes[i]?.value || '0');
                    if (!isNaN(v) && !isNaN(w)) { weighted += v * w; totalBobot += w; }
                }
            }
            const wavg = (useWeight && useWeight.checked && totalBobot>0) ? (weighted/totalBobot) : avg;
            wavgEl && (wavgEl.textContent = wavg.toFixed(2));
            tbobotEl && (tbobotEl.textContent = totalBobot.toFixed(2));
            const letter = toLetter(wavg);
            gradeEl && (gradeEl.textContent = letter);
            // set hidden fields
            if (hfTotal) hfTotal.value = sum.toFixed(2);
            if (hfAvg) hfAvg.value = avg.toFixed(2);
            if (hfWAvg) hfWAvg.value = wavg.toFixed(2);
            if (hfGrade) hfGrade.value = letter;
        }
        function toLetter(score) {
            const a = parseFloat(thrA?.value || '90');
            const b = parseFloat(thrB?.value || '80');
            const c = parseFloat(thrC?.value || '70');
            if (score >= a) return 'A';
            if (score >= b) return 'B';
            if (score >= c) return 'C';
            if (score >= 60) return 'D';
            return 'E';
        }
        document.addEventListener('input', (e) => {
            if (!e.target) return;
            if (e.target.classList.contains('nilai-field') || e.target.classList.contains('bobot-field')) calc();
            if (['thr-a','thr-b','thr-c','use-weight'].includes(e.target.id)) calc();
        });
        document.addEventListener('DOMContentLoaded', calc);
        calc();
    })();
</script>

@if(!empty($gradeTemplateId) && $gradeTemplate)
    {{-- Data Nilai Saat Ini --}}
    <div class="mt-6 bg-white dark:bg-gray-800 shadow-lg rounded-lg p-5">
        <div class="flex items-center mb-4">
            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c.638 0 1.246.12 1.803.338l2.574-2.574A9 9 0 103 12h3a6 6 0 116 6v3a9 9 0 000-18z" />
            </svg>
            <h3 class="font-bold text-lg dark:text-white">Data Nilai Saat Ini</h3>
        </div>
        @php $map = $existingPenilaianBySiswa ?? []; @endphp
        @if(!empty($map))
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($siswaList as $s)
                    @php $d = $map[$s->id] ?? null; @endphp
                    <div class="p-3 rounded border bg-zinc-50 dark:bg-zinc-900">
                        <div class="font-semibold text-sm mb-1">{{ $s->nama }}</div>
                        @if($d)
                        @php 
                            // Normalisasi struktur nilai_detail: bisa berupa
                            // 1) ['nilai' => [...], 'computed' => [...]] (input manual)
                            // 2) ['row' => [...]] (TA/UKK)
                            // 3) Struktur grup (PRAKERIN) tanpa 'nilai'/'row', nilai ada di bawah key-key kategori
                            $raw = is_array($d) ? $d : (json_decode($d, true) ?: []);
                            if (array_key_exists('nilai', $raw)) {
                                $nilai = $raw['nilai'];
                            } elseif (array_key_exists('row', $raw)) {
                                $nilai = $raw['row'];
                            } else {
                                // buang meta yang bukan nilai
                                $nilai = $raw;
                                unset($nilai['format'], $nilai['judul_laporan']);
                            }
                            $cmp   = $raw['computed'] ?? [];

                            // Fallback: jika total/avg belum ada di computed, hitung dari semua nilai numerik
                            $fallbackTotal = null;
                            $fallbackAvg   = null;
                            if (empty($cmp['total']) || empty($cmp['avg'])) {
                                $nums = [];
                                $stack = [$nilai];
                                while ($stack) {
                                    $item = array_pop($stack);
                                    if (is_array($item)) {
                                        foreach ($item as $v) {
                                            if (is_array($v)) {
                                                $stack[] = $v;
                                            } elseif (is_numeric($v)) {
                                                $nums[] = (float)$v;
                                            }
                                        }
                                    } elseif (is_numeric($item)) {
                                        $nums[] = (float)$item;
                                    }
                                }
                                if (count($nums) > 0) {
                                    $fallbackTotal = array_sum($nums);
                                    $fallbackAvg   = round($fallbackTotal / count($nums), 2);
                                }
                            }
                        @endphp
                        <div class="mt-1 text-[11px] text-zinc-600">
                            <div>Total: <span class="font-semibold">{{ $cmp['total'] ?? $fallbackTotal ?? '-' }}</span></div>
                            <div>Rata-rata: <span class="font-semibold">{{ $cmp['avg'] ?? $fallbackAvg ?? '-' }}</span></div>
                        </div>
                        @else
                            <div class="text-xs text-zinc-500">Belum ada nilai.</div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-sm text-zinc-500">Belum ada penilaian yang tersimpan untuk template ini.</div>
        @endif
    </div>

    <script>
        // Prefill nilai saat pilih siswa, dari existingPenilaianBySiswa
        (function() {
            const existing = @json($existingPenilaianBySiswa ?? []);
            const siswaSelect = document.querySelector('#form-nilai select[name="siswa_id"]');
            if (!siswaSelect) return;

            function prefill() {
                const id = siswaSelect.value;
                if (!id || !existing[id]) return;
                let data = existing[id];
                if (typeof data === 'string') {
                    try { data = JSON.parse(data); } catch(e) { data = {}; }
                }
                const nilai = data?.nilai || {};
                for (const [k,v] of Object.entries(nilai)) {
                    const input = document.querySelector(`#form-nilai input[name="nilai[${k}]"]`);
                    if (input) input.value = v;
                }
                // computed prefill to hidden and summary UI
                const cmp = data?.computed || {};
                if (document.getElementById('hf-total')) document.getElementById('hf-total').value = cmp.total ?? '';
                if (document.getElementById('hf-avg')) document.getElementById('hf-avg').value = cmp.avg ?? '';
                if (document.getElementById('hf-weighted-avg')) document.getElementById('hf-weighted-avg').value = cmp.weighted_avg ?? '';
                if (document.getElementById('hf-grade')) document.getElementById('hf-grade').value = cmp.grade ?? '';
                if (document.getElementById('sum-nilai')) document.getElementById('sum-nilai').textContent = cmp.total ?? '0';
                if (document.getElementById('avg-nilai')) document.getElementById('avg-nilai').textContent = cmp.avg ?? '0';
                if (document.getElementById('weighted-avg-nilai')) document.getElementById('weighted-avg-nilai').textContent = cmp.weighted_avg ?? '0';
                if (document.getElementById('grade-nilai')) document.getElementById('grade-nilai').textContent = cmp.grade ?? '-';
            }

            siswaSelect.addEventListener('change', prefill);
            // prefill on load if a value preselected
            if (siswaSelect.value) prefill();
        })();
    </script>
@endif

</x-layouts.app>