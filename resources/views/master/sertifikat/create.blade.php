<x-layouts.app :title="'Tambah Template Sertifikat'">
    <div class="min-h-screen bg-gray-50 py-8 px-4">
        <div class="max-w-3xl mx-auto">
            {{-- Header --}}
            <div class="mb-6">
                <a href="{{ route('master.sertifikat.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 mb-4 transition-colors font-medium">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali
                </a>
                <h1 class="text-3xl font-bold text-gray-900 mb-1">Upload Template Sertifikat</h1>
                <p class="text-gray-600">Tambahkan template baru untuk sertifikat</p>
            </div>

            <form action="{{ route('master.sertifikat.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                {{-- Informasi Template --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center mb-6">
                       
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Informasi Template</h2>
                            <p class="text-sm text-gray-600">Masukkan detail template sertifikat</p>
                        </div>
                    </div>

                    <div class="space-y-5">
                        {{-- Nama Template --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Nama Template <span class="text-red-600">*</span>
                            </label>
                            <input type="text" name="nama_template" 
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent transition-all"
                                   placeholder="Contoh: Sertifikat Kelulusan 2024"
                                   value="{{ old('nama_template') }}" required>
                            @error('nama_template')
                                <p class="text-red-600 text-sm mt-1 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- File Upload --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                File Background <span class="text-red-600">*</span>
                            </label>
                            <p class="text-xs text-gray-500 mb-3">Format yang didukung: PNG, JPG, JPEG (Maks. 5MB)</p>
                            
                            <div class="relative">
                                <input type="file" name="file_background" id="file_background" 
                                       class="hidden" 
                                       accept=".png,.jpg,.jpeg"
                                       onchange="previewImage(event)" required>
                                
                                <label for="file_background" 
                                       class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed border-purple-300 rounded-lg cursor-pointer hover:border-gray-400 hover:bg-gray-50 transition-all">
                                    <div id="upload-placeholder" class="flex flex-col items-center justify-center text-center">
                                        <svg class="w-12 h-12 text-purple-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                        <p class="text-sm font-medium text-purple-700 mb-1">Klik untuk upload file</p>
                                        <p class="text-xs text-purple-500">atau drag & drop file di sini</p>
                                    </div>
                                    <div id="preview-container" class="hidden w-full h-full">
                                        <img id="preview-image" src="" alt="Preview" class="w-full h-full object-contain rounded-lg">
                                    </div>
                                </label>
                                
                                <button type="button" id="remove-image" 
                                        class="hidden absolute top-2 right-2 p-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-all shadow-lg"
                                        onclick="removeImage()">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            
                            <p id="file-name" class="text-sm text-gray-600 mt-2 hidden"></p>
                            
                            @error('file_background')
                                <p class="text-red-600 text-sm mt-1 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Preview Info --}}
                <div class="bg-purple-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-semibold text-purple-800">Tips Upload Template</h3>
                            <div class="mt-2 text-sm text-purple-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Gunakan gambar dengan resolusi tinggi untuk hasil cetak terbaik</li>
                                    <li>Pastikan desain sudah final sebelum diupload</li>
                                    <li>Ukuran file maksimal 5MB</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('master.sertifikat.index') }}"
                       class="px-6 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all shadow-sm font-medium">
                        Batal
                    </a>
                    <button type="submit"
                            class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-800 transition-all shadow-sm font-medium flex items-center gap-2 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Simpan Template
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar! Maksimal 5MB.');
                    event.target.value = '';
                    return;
                }

                // Validate file type
                const validTypes = ['image/png', 'image/jpeg', 'image/jpg'];
                if (!validTypes.includes(file.type)) {
                    alert('Format file tidak valid! Gunakan PNG, JPG, atau JPEG.');
                    event.target.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-image').src = e.target.result;
                    document.getElementById('upload-placeholder').classList.add('hidden');
                    document.getElementById('preview-container').classList.remove('hidden');
                    document.getElementById('remove-image').classList.remove('hidden');
                    
                    // Show file name
                    const fileName = document.getElementById('file-name');
                    fileName.textContent = `File: ${file.name} (${(file.size / 1024).toFixed(2)} KB)`;
                    fileName.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        }

        function removeImage() {
            document.getElementById('file_background').value = '';
            document.getElementById('preview-image').src = '';
            document.getElementById('upload-placeholder').classList.remove('hidden');
            document.getElementById('preview-container').classList.add('hidden');
            document.getElementById('remove-image').classList.add('hidden');
            document.getElementById('file-name').classList.add('hidden');
        }

        // Drag and drop functionality
        const dropZone = document.querySelector('label[for="file_background"]');
        
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('border-gray-900', 'bg-gray-100');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('border-gray-900', 'bg-gray-100');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-gray-900', 'bg-gray-100');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                document.getElementById('file_background').files = files;
                previewImage({ target: { files: files } });
            }
        });
    </script>
</x-layouts.app>