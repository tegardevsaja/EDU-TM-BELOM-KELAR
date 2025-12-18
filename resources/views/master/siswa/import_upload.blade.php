<x-layouts.app :title="__('Import Data Siswa')">
    <div class="max-w-4xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Tambah Siswa</h2>
            <p class="text-gray-600 dark:text-gray-400">Import data siswa secara massal menggunakan file Excel (.xlsx, .xls) atau CSV (.csv)</p>
        </div>

        <!-- Toggle Input / Import -->
        <div class="bg-gray-100 dark:bg-zinc-800 p-1 rounded-xl mb-8 inline-flex">
            <a class="px-6 py-2.5 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-zinc-700 transition-all"
               href="{{ route('master.siswa.create') }}" wire:navigate>
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    Input Manual
                </span>
            </a>
            <a class="px-6 py-2.5 rounded-lg text-sm font-medium bg-purple-600 text-white shadow-lg shadow-purple-500/30 transition-all"
               href="{{ url('master/siswa/import') }}" wire:navigate>
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Import File
                </span>
            </a>
        </div>

        <!-- Import Form Card -->
        <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-gray-100 dark:border-zinc-700 overflow-hidden">
            <div class="p-8">
                @if(session('error'))
                    <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <h4 class="font-medium text-red-900 dark:text-red-100">Error</h4>
                            <p class="text-sm text-red-700 dark:text-red-300 mt-1">{{ session('error') }}</p>
                        </div>
                    </div>
                @endif

                <form action="{{ route('master.siswa.import.preview') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- File Upload Section -->
                    <div class="relative mb-6">
                        <input type="file" name="file_excel" id="file_excel"
                               class="hidden" 
                               accept=".csv,.xls,.xlsx"
                               required>

                        <!-- Drop Area -->
                        <div id="drop-area"
                             class="relative border-2 border-dashed border-gray-300 dark:border-zinc-600 rounded-2xl cursor-pointer transition-all duration-300 hover:border-purple-400 dark:hover:border-purple-500 hover:bg-purple-50/50 dark:hover:bg-purple-900/10 group">
                            
                            <!-- Upload Placeholder -->
                            <div id="upload-placeholder" class="flex flex-col items-center justify-center py-12 px-6 text-center">
                                <div class="w-16 h-16 bg-purple-100 dark:bg-purple-900/30 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                    <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                    Upload File Excel atau CSV
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                                    Klik untuk browse atau drag & drop file di sini
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">
                                    Format: .csv, .xls, .xlsx (Max 100 MB)
                                </p>
                            </div>

                            <!-- File Preview -->
                            <div id="file-preview" class="hidden p-6">
                                <div class="flex items-start gap-4">
                                    <!-- File Icon -->
                                    <div class="flex-shrink-0">
                                        <div class="w-14 h-14 bg-gradient-to-br from-green-400 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- File Info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-3 mb-2">
                                            <div class="flex-1 min-w-0">
                                                <h4 id="file-name" class="text-sm font-semibold text-gray-900 dark:text-white truncate mb-1"></h4>
                                                <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                                    <span id="file-size" class="font-medium"></span>
                                                    <span class="w-1 h-1 bg-gray-400 rounded-full"></span>
                                                    <span id="file-type" class="uppercase"></span>
                                                </div>
                                            </div>
                                            <button type="button" id="remove-file" 
                                                    class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>

                                        <!-- Progress Bar -->
                                        <div class="relative w-full h-2 bg-gray-200 dark:bg-zinc-700 rounded-full overflow-hidden">
                                            <div id="file-progress" 
                                                 class="absolute top-0 left-0 h-full bg-gradient-to-r from-purple-500 to-purple-600 rounded-full transition-all duration-500 ease-out"
                                                 style="width: 0%"></div>
                                        </div>

                                        <!-- Success Checkmark -->
                                        <div id="upload-success" class="hidden mt-3 flex items-center gap-2 text-sm text-green-600 dark:text-green-400">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="font-medium">File siap diimport</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @error('file_excel')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Additional Fields Section (Not in Manual Input) -->
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-6 mb-6 border-2 border-blue-200 dark:border-blue-800">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100">
                                Pengaturan Import
                            </h3>
                        </div>
                        <p class="text-sm text-blue-700 dark:text-blue-300 mb-4">
                            Field ini akan diterapkan ke <strong>semua siswa</strong> yang diimport
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {{-- Kelas --}}
                            <div class="flex flex-col">
                                <label class="font-medium text-sm text-gray-700 dark:text-gray-300 mb-2 flex items-center gap-1">
                                    Kelas
                                    <span class="text-red-500">*</span>
                                </label>
                                <select name="kelas_id" required
                                    class="px-3 py-2.5 rounded-lg border-2 border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach ($kelas as $k)
                                        <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                            {{ $k->nama_kelas }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kelas_id') 
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                                @enderror
                            </div>

                            {{-- Jurusan --}}
                            <div class="flex flex-col">
                                <label class="font-medium text-sm text-gray-700 dark:text-gray-300 mb-2 flex items-center gap-1">
                                    Jurusan
                                    <span class="text-red-500">*</span>
                                </label>
                                <select name="jurusan_id" required
                                    class="px-3 py-2.5 rounded-lg border-2 border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                                    <option value="">-- Pilih Jurusan --</option>
                                    @foreach ($jurusan as $j)
                                        <option value="{{ $j->id }}" {{ old('jurusan_id') == $j->id ? 'selected' : '' }}>
                                            {{ $j->nama_jurusan }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('jurusan_id') 
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                                @enderror
                            </div>

                            {{-- Tahun Ajaran --}}
                            <div class="flex flex-col">
                                <label class="font-medium text-sm text-gray-700 dark:text-gray-300 mb-2 flex items-center gap-1">
                                    Tahun Ajaran
                                    <span class="text-red-500">*</span>
                                </label>
                                <select name="tahun_ajaran_id" required
                                    class="px-3 py-2.5 rounded-lg border-2 border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                                    <option value="">-- Pilih Tahun Ajaran --</option>
                                    @foreach ($tahun_ajaran as $t)
                                        <option value="{{ $t->id }}" {{ old('tahun_ajaran_id') == $t->id ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::parse($t->tanggal_mulai)->format('Y') }} /
                                            {{ \Carbon\Carbon::parse($t->tanggal_selesai)->format('Y') }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('tahun_ajaran_id')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            {{-- Tahun Masuk --}}
                            <div class="flex flex-col">
                                <label class="font-medium text-sm text-gray-700 dark:text-gray-300 mb-2 flex items-center gap-1">
                                    Tahun Masuk
                                    <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="tahun_masuk" value="{{ old('tahun_masuk', date('Y')) }}" required
                                    class="px-3 py-2.5 rounded-lg border-2 border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                                    placeholder="Contoh: {{ date('Y') }}" min="2000" max="{{ date('Y') + 1 }}">
                                @error('tahun_masuk') 
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                                @enderror
                            </div>

                            {{-- Status --}}
                            <div class="flex flex-col">
                                <label class="font-medium text-sm text-gray-700 dark:text-gray-300 mb-2 flex items-center gap-1">
                                    Status
                                    <span class="text-red-500">*</span>
                                </label>
                                <select name="status" required
                                    class="px-3 py-2.5 rounded-lg border-2 border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                                    <option value="Aktif" {{ old('status', 'Aktif') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                    <option value="Alumni" {{ old('status') == 'Alumni' ? 'selected' : '' }}>Alumni</option>
                                    <option value="Nonaktif" {{ old('status') == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                    <option value="Dikeluarkan" {{ old('status') == 'Dikeluarkan' ? 'selected' : '' }}>Dikeluarkan</option>
                                </select>
                                @error('status') 
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-between pt-6 border-t border-gray-100 dark:border-zinc-700">
                        <a href="{{ route('master.siswa.index') }}" 
                           class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-700 rounded-xl transition-colors">
                            Batal
                        </a>
                        <button type="submit" id="submit-btn"
                                class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white text-sm font-semibold rounded-xl shadow-lg shadow-purple-500/30 hover:shadow-purple-500/50 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Preview Data
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Info Card -->
        <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="text-sm">
                    <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-1">Tips Import Data</h4>
                    <ul class="text-blue-700 dark:text-blue-300 space-y-1 list-disc list-inside">
                        <li>Pastikan format file sesuai template yang telah ditentukan</li>
                        <li>Maksimal ukuran file adalah 100 MB</li>
                        <li>Kelas, Jurusan, dan Tahun Ajaran akan diterapkan ke semua data yang diimport</li>
                        <li>Data di file Excel hanya perlu: NIS, Nama, Jenis Kelamin, Tempat/Tanggal Lahir, dll</li>
                        <li>Periksa kembali data sebelum melakukan import final</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        const dropArea = document.getElementById('drop-area');
        const fileInput = document.getElementById('file_excel');
        const uploadPlaceholder = document.getElementById('upload-placeholder');
        const filePreview = document.getElementById('file-preview');
        const fileNameEl = document.getElementById('file-name');
        const fileSizeEl = document.getElementById('file-size');
        const fileTypeEl = document.getElementById('file-type');
        const fileProgress = document.getElementById('file-progress');
        const uploadSuccess = document.getElementById('upload-success');
        const removeBtn = document.getElementById('remove-file');
        const submitBtn = document.getElementById('submit-btn');

        const MAX_SIZE = 100 * 1024 * 1024; // 100 MB

        function formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
        }

        function getFileExtension(filename) {
            return filename.slice((filename.lastIndexOf(".") - 1 >>> 0) + 2);
        }

        function updateSubmitState() {
            submitBtn.disabled = !(fileInput.files && fileInput.files.length > 0);
        }

        function showFile(file) {
            uploadPlaceholder.classList.add('hidden');
            filePreview.classList.remove('hidden');
            
            fileNameEl.textContent = file.name;
            fileSizeEl.textContent = formatBytes(file.size);
            fileTypeEl.textContent = getFileExtension(file.name);
            
            // Animate progress bar
            setTimeout(() => {
                fileProgress.style.width = '100%';
            }, 100);

            // Show success message after animation
            setTimeout(() => {
                uploadSuccess.classList.remove('hidden');
            }, 600);

            if (file.size > MAX_SIZE) {
                alert('Ukuran file terlalu besar! Maksimal 100 MB');
                resetFile();
            }
            updateSubmitState();
        }

        function resetFile() {
            fileInput.value = '';
            filePreview.classList.add('hidden');
            uploadPlaceholder.classList.remove('hidden');
            fileProgress.style.width = '0%';
            uploadSuccess.classList.add('hidden');
            updateSubmitState();
        }

        // Click area to trigger input
        dropArea.addEventListener('click', (e) => {
            if (!e.target.closest('#remove-file')) {
                fileInput.click();
            }
        });

        // Drag & drop events
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, e => {
                e.preventDefault();
                e.stopPropagation();
            }, false);
        });

        dropArea.addEventListener('dragover', () => {
            dropArea.classList.add('border-purple-500', 'bg-purple-50', 'dark:bg-purple-900/20');
        });

        dropArea.addEventListener('dragleave', () => {
            dropArea.classList.remove('border-purple-500', 'bg-purple-50', 'dark:bg-purple-900/20');
        });

        dropArea.addEventListener('drop', (e) => {
            dropArea.classList.remove('border-purple-500', 'bg-purple-50', 'dark:bg-purple-900/20');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                showFile(files[0]);
            }
        });

        fileInput.addEventListener('change', () => {
            if (fileInput.files.length > 0) {
                showFile(fileInput.files[0]);
            }
        });

        removeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            resetFile();
        });

        updateSubmitState();
    </script>

</x-layouts.app>