<x-layouts.app :title="'Edit Template Sertifikat'">
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
                <h1 class="text-3xl font-bold text-gray-900 mb-1">Edit Template Sertifikat</h1>
                <p class="text-gray-600">Perbarui nama atau background template</p>
            </div>

            <form action="{{ route('master.sertifikat.update', $template->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Informasi Template --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="space-y-5">
                        {{-- Nama Template --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Nama Template <span class="text-red-600">*</span>
                            </label>
                            <input type="text" name="nama_template" 
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent transition-all"
                                   placeholder="Contoh: Sertifikat Kelulusan 2024"
                                   value="{{ old('nama_template', $template->nama_template) }}" required>
                            @error('nama_template')
                                <p class="text-red-600 text-sm mt-1 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- File Upload (opsional) --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                File Background (opsional)
                            </label>
                            <p class="text-xs text-gray-500 mb-3">Format yang didukung: PNG, JPG, JPEG, WEBP (Maks. 5MB)</p>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="relative">
                                    <input type="file" name="file_background" id="file_background" 
                                           class="hidden" accept=".png,.jpg,.jpeg,.webp" onchange="previewImageEdit(event)">
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
                                    <p id="file-name" class="text-sm text-gray-600 mt-2 hidden"></p>
                                </div>

                                <div class="border rounded-lg p-2">
                                    <div class="text-xs text-gray-500 mb-2">Background saat ini:</div>
                                    @if($template->background_image)
                                        <img src="{{ asset('storage/'.$template->background_image) }}" class="w-full h-48 object-contain rounded" alt="Current Background">
                                    @else
                                        <div class="w-full h-48 bg-gray-100 rounded flex items-center justify-center text-gray-400">Tidak ada gambar</div>
                                    @endif
                                </div>
                            </div>
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

                {{-- Actions --}}
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('master.sertifikat.index') }}"
                       class="px-6 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all shadow-sm font-medium">Batal</a>
                    <button type="submit"
                            class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-800 transition-all shadow-sm font-medium flex items-center gap-2 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function previewImageEdit(event) {
            const file = event.target.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar! Maksimal 5MB.');
                    event.target.value = '';
                    return;
                }
                const validTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    alert('Format file tidak valid! Gunakan PNG, JPG, JPEG, atau WEBP.');
                    event.target.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-image').src = e.target.result;
                    document.getElementById('upload-placeholder').classList.add('hidden');
                    document.getElementById('preview-container').classList.remove('hidden');
                    const fileName = document.getElementById('file-name');
                    fileName.textContent = `File: ${file.name} (${(file.size / 1024).toFixed(2)} KB)`;
                    fileName.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        }
    </script>
</x-layouts.app>
