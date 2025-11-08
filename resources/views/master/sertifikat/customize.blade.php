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
        <div class="bg-gray-100 dark:bg-gray-900 rounded-lg w-full mx-auto relative overflow-hidden border-2 border-dashed border-gray-300 dark:border-gray-700" 
             style="aspect-ratio: 297/210; max-width: 1000px;"
             x-ref="canvas" 
             @click="selectedElement=null">
            {{-- Background --}}
            <div x-show="backgroundImage" class="absolute inset-0 bg-cover bg-center" :style="`background-image: url(${backgroundImage})`"></div>
            
            {{-- Elements --}}
            <template x-for="(element,index) in elements" :key="element.id">
                <div
                    @mousedown.stop.prevent="startDrag($event,index)"
                    @click.stop="selectedElement=index"
                    :class="selectedElement===index?'ring-4 ring-blue-500 shadow-xl z-50':'ring-1 ring-transparent hover:ring-blue-300'"
                    class="absolute cursor-move px-2 py-1 select-none transition-all duration-200"
                    :style="`left:${element.x}%;top:${element.y}%;transform:translate(-50%,-50%);font-size:${element.fontSize}px;font-family:${element.fontFamily};color:${element.color};font-weight:${element.bold?'bold':'normal'};text-align:${element.align};min-width:100px;`"
                >
                    {{-- Text Elements --}}
                    <template x-if="element.type==='text'">
                        <div x-text="element.content" class="whitespace-pre-wrap pointer-events-none"></div>
                    </template>
                    
                    {{-- Variable Elements --}}
                    <template x-if="element.type==='variable'">
                        <div class="bg-yellow-100 dark:bg-yellow-900 bg-opacity-60 rounded px-2 py-1 pointer-events-none">
                            <span x-text="getVariableSample(element.variable)" class="font-medium"></span>
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

    {{-- Debug Panel --}}
    <div class="mt-4 bg-yellow-50 dark:bg-yellow-900 dark:bg-opacity-20 border-2 border-yellow-300 dark:border-yellow-700 rounded-lg p-4">
        <div class="flex items-center mb-2">
            <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <h4 class="font-bold text-yellow-800 dark:text-yellow-300">Debug Info</h4>
        </div>
        <div class="grid grid-cols-2 gap-4 text-xs bg-white dark:bg-gray-800 p-3 rounded border dark:border-gray-700">
            <div>
                <span class="text-gray-600 dark:text-gray-400">Total Elemen:</span>
                <span class="font-bold text-blue-600 dark:text-blue-400 ml-2" x-text="elements.length"></span>
            </div>
            <div>
                <span class="text-gray-600 dark:text-gray-400">Template ID:</span>
                <span class="font-bold ml-2" x-text="templateId"></span>
            </div>
            <div>
                <span class="text-gray-600 dark:text-gray-400">Element Terpilih:</span>
                <span class="font-bold ml-2" x-text="selectedElement !== null ? '#' + selectedElement : 'Tidak ada'"></span>
            </div>
            <div>
                <span class="text-gray-600 dark:text-gray-400">Tipe Element:</span>
                <span class="font-bold ml-2" x-text="selectedElement !== null ? elements[selectedElement]?.type : '-'"></span>
            </div>
        </div>
        <template x-if="selectedElement !== null && elements[selectedElement]">
            <div class="mt-2 text-xs bg-blue-50 dark:bg-blue-900 dark:bg-opacity-30 p-3 rounded border border-blue-300 dark:border-blue-700">
                <div class="font-semibold text-blue-800 dark:text-blue-300 mb-1">Posisi Element Saat Ini:</div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <span class="text-gray-700 dark:text-gray-300">X:</span>
                        <span class="font-mono font-bold ml-1" x-text="Math.round(elements[selectedElement].x * 100) / 100 + '%'"></span>
                    </div>
                    <div>
                        <span class="text-gray-700 dark:text-gray-300">Y:</span>
                        <span class="font-mono font-bold ml-1" x-text="Math.round(elements[selectedElement].y * 100) / 100 + '%'"></span>
                    </div>
                    <div>
                        <span class="text-gray-700 dark:text-gray-300">Font Size:</span>
                        <span class="font-mono font-bold ml-1" x-text="elements[selectedElement].fontSize + 'px'"></span>
                    </div>
                    <div>
                        <span class="text-gray-700 dark:text-gray-300">Align:</span>
                        <span class="font-mono font-bold ml-1" x-text="elements[selectedElement].align"></span>
                    </div>
                </div>
            </div>
        </template>
    </div>

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

    {{-- Daftar Siswa --}}
    <div class="mt-6 bg-white dark:bg-gray-800 shadow-lg rounded-lg p-5">
        <div class="flex items-center mb-4">
            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <h3 class="font-bold text-lg dark:text-white">
                Daftar Siswa <span class="text-blue-500" x-text="'(' + siswaList.length + ')'"></span>
            </h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 max-h-[300px] overflow-y-auto">
            <template x-for="s in siswaList" :key="s.id">
                <div class="p-3 border-2 dark:border-gray-700 hover:border-blue-400 dark:hover:border-blue-500 rounded-lg text-sm dark:text-white transition-colors duration-200 bg-gray-50 dark:bg-gray-900">
                    <div class="font-semibold text-gray-900 dark:text-white" x-text="s.nama"></div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="'Kelas: ' + (s.kelas ? s.kelas.nama_kelas : '-')"></div>
                </div>
            </template>
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
        backgroundImage: '{{ asset("storage/".$template->background_image) }}',
        elementIdCounter: 1000,

        variables: [
            {name:'$nama_siswa', label:'Nama Siswa', sample:'Tegar Kurniawan'},
            {name:'$kelas', label:'Kelas', sample:'XII RPL'},
            {name:'$nis', label:'NIS', sample:'12345678'},
            {name:'$tanggal', label:'Tanggal', sample:'23 Oktober 2025'},
            {name:'$nilai', label:'Nilai', sample:'95'},
            {name:'$peringkat', label:'Peringkat', sample:'1'},
            {name:'$jurusan', label:'Jurusan', sample:'Rekayasa Perangkat Lunak'},
            {name:'$ttd', label:'Tanda Tangan', sample:'(Signature)'},
        ],

        // 🔥 FIX: Initialize elements from PHP
        init() {
            const elementsFromPhp = @json($elements);
            console.log('='.repeat(60));
            console.log('🚀 CERTIFICATE CUSTOMIZER INITIALIZED');
            console.log('='.repeat(60));
            console.log('📋 Template ID:', this.templateId);
            console.log('👥 Students Count:', this.siswaList.length);
            console.log('📦 Elements from PHP:', elementsFromPhp);
            console.log('🔢 Elements Count:', elementsFromPhp ? elementsFromPhp.length : 0);
            
            if (Array.isArray(elementsFromPhp) && elementsFromPhp.length > 0) {
                this.elements = elementsFromPhp;
                // Set counter to max ID + 1
                const maxId = Math.max(...this.elements.map(el => el.id || 0));
                this.elementIdCounter = maxId + 1;
                console.log('✅ Elements loaded successfully');
                console.log('📊 Elements details:', this.elements.map(el => ({
                    id: el.id,
                    type: el.type,
                    x: el.x,
                    y: el.y,
                    fontSize: el.fontSize
                })));
                console.log('🔄 Next Element ID:', this.elementIdCounter);
            } else {
                console.warn('⚠️ No elements from PHP, starting with empty canvas');
                this.elements = [];
                this.elementIdCounter = 1;
            }
            console.log('='.repeat(60));
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
            console.log('✅ Text element added:', newElement);
            console.log('📊 Total elements:', this.elements.length);
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
                value: selectedVar.sample,
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
            
            console.log('✅ Variable element added:', newElement);
            console.log('📊 Total elements:', this.elements.length);
        },

        deleteElement() {
            if (this.selectedElement === null) return;
            console.log('🗑️ Deleting element:', this.elements[this.selectedElement]);
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
            };

            document.addEventListener('mousemove', move);
            document.addEventListener('mouseup', up);
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

                const elementsToSave = this.elements.map(el => ({
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
                    align: el.align || 'center'
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
            let url = `/master/sertifikat/generate/preview/${this.templateId}`;
            if (kelasId) url += `?kelas_id=${kelasId}`;
            window.open(url, '_blank');
        }
    }
}
</script>

</x-layouts.app>