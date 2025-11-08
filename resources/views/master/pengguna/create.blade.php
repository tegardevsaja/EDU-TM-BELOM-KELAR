<x-layouts.app :title="__('Tambah Pengguna')">
    <div class="max-w-md mx-auto bg-white dark:bg-gray-900 p-8 rounded-[10px] shadow-lg mt-10 border border-gray-100 dark:border-gray-800">
        <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-6 text-center">Tambah Pengguna</h2>

        {{-- Pesan sukses --}}
        @if (session('success'))
            <div class="mb-4 text-sm text-green-700 bg-green-100 border border-green-200 p-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        {{-- Tab Switch: Import / Input Manual --}}
        <div class="flex gap-2 mb-6 p-1 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <button type="button" id="tab-manual" onclick="switchTab('manual')"
                class="flex-1 py-2.5 text-sm font-medium rounded-md transition-all duration-200 tab-active">
                <span class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    Input Manual
                </span>
            </button>
            <button type="button" id="tab-import" onclick="switchTab('import')"
                class="flex-1 py-2.5 text-sm font-medium rounded-md transition-all duration-200 tab-inactive">
                <span class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Import File
                </span>
            </button>
        </div>

        {{-- Form Input Manual --}}
        <form id="form-manual" action="{{ route('master.pengguna.store') }}" method="POST" class="space-y-5">
            @csrf

            {{-- Nama --}}
            <div>
                <label for="nama" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama</label>
                <input type="text" name="nama" id="nama" value="{{ old('nama') }}"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                    placeholder="Masukkan nama pengguna" required>
                @error('nama')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                    placeholder="Masukkan email pengguna" required>
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- NIK --}}
            <div>
                <label for="nik" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">NIK (opsional)</label>
                <input type="number" name="nik" id="nik" value="{{ old('nik') }}"
                    class="no-spinner w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                    placeholder="Masukkan NIK">
                @error('nik')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tombol --}}
            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ route('master.pengguna') }}"
                    class="px-4 py-2 text-sm rounded-lg border border-gray-300 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    Batal
                </a>
                <button type="submit"
                    class="px-4 py-2 text-sm rounded-lg bg-blue-600 hover:bg-blue-700 text-white focus:ring-2 focus:ring-blue-500 transition">
                    Simpan
                </button>
            </div>
        </form>

        {{-- Form Import --}}
        <form id="form-import" action="{{ route('master.pengguna.import') }}" method="POST" enctype="multipart/form-data" class="space-y-5 hidden">
            @csrf

            {{-- Upload Area --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Upload File Excel/CSV</label>
                <div class="relative border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-lg p-8 text-center hover:border-blue-500 dark:hover:border-blue-500 transition-colors cursor-pointer"
                    onclick="document.getElementById('file-upload').click()">
                    <input type="file" id="file-upload" name="file" accept=".xlsx,.xls,.csv" class="hidden" onchange="updateFileName(this)" required>
                    
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    
                    <div class="mt-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400" id="file-name">
                            Klik untuk upload atau drag & drop
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                            Excel atau CSV (max 5MB)
                        </p>
                    </div>
                </div>
                @error('file')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Download Template --}}
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm text-blue-800 dark:text-blue-300">
                            Belum punya template?
                        </p>
                        <a href="{{ route('master.pengguna.template') }}" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline mt-1 inline-block">
                            Download template Excel →
                        </a>
                    </div>
                </div>
            </div>

            {{-- Tombol --}}
            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ route('master.pengguna') }}"
                    class="px-4 py-2 text-sm rounded-lg border border-gray-300 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    Batal
                </a>
                <button type="submit"
                    class="px-4 py-2 text-sm rounded-lg bg-blue-600 hover:bg-blue-700 text-white focus:ring-2 focus:ring-blue-500 transition">
                    Import
                </button>
            </div>
        </form>
    </div>

    <style>
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type=number] {
            -moz-appearance: textfield;
        }

        .tab-active {
            @apply bg-white dark:bg-gray-900 text-blue-600 dark:text-blue-400 shadow-sm;
        }

        .tab-inactive {
            @apply text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200;
        }
    </style>

    <script>
        function switchTab(tab) {
            const manualTab = document.getElementById('tab-manual');
            const importTab = document.getElementById('tab-import');
            const manualForm = document.getElementById('form-manual');
            const importForm = document.getElementById('form-import');

            if (tab === 'manual') {
                manualTab.classList.add('tab-active');
                manualTab.classList.remove('tab-inactive');
                importTab.classList.add('tab-inactive');
                importTab.classList.remove('tab-active');
                manualForm.classList.remove('hidden');
                importForm.classList.add('hidden');
            } else {
                importTab.classList.add('tab-active');
                importTab.classList.remove('tab-inactive');
                manualTab.classList.add('tab-inactive');
                manualTab.classList.remove('tab-active');
                importForm.classList.remove('hidden');
                manualForm.classList.add('hidden');
            }
        }

        function updateFileName(input) {
            const fileName = input.files[0]?.name || 'Klik untuk upload atau drag & drop';
            document.getElementById('file-name').textContent = fileName;
            if (input.files[0]) {
                document.getElementById('file-name').classList.add('text-blue-600', 'dark:text-blue-400', 'font-medium');
            }
        }

        // Drag and drop functionality
        const dropZone = document.querySelector('[onclick*="file-upload"]');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dropZone.classList.add('border-blue-500', 'dark:border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
        }

        function unhighlight(e) {
            dropZone.classList.remove('border-blue-500', 'dark:border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
        }

        dropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            document.getElementById('file-upload').files = files;
            updateFileName(document.getElementById('file-upload'));
        }
    </script>
</x-layouts.app>