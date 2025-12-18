<x-layouts.app :title="'Pengaturan Penguji & Tanda Tangan'">
    <div class="max-w-3xl mx-auto p-6">
        <h2 class="text-xl font-semibold mb-4 dark:text-white">Pengaturan Penguji &amp; Tanda Tangan</h2>

        @if(session('success'))
            <div class="mb-4 px-4 py-3 rounded bg-green-100 text-green-800 border border-green-300 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('master.sertifikat.signatures.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <input type="hidden" name="return" value="{{ request('return') }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-semibold mb-2">Blok Kiri</h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <label class="block mb-1">Label</label>
                            <input type="text" name="left_label" value="{{ old('left_label', $signatures->left_label ?? 'Penguji Internal') }}" class="w-full border rounded px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block mb-1">Nama</label>
                            <input type="text" name="left_name" value="{{ old('left_name', $signatures->left_name ?? '') }}" class="w-full border rounded px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block mb-1">Instansi / Jabatan</label>
                            <input type="text" name="left_org" value="{{ old('left_org', $signatures->left_org ?? 'SMK Tunas Media') }}" class="w-full border rounded px-3 py-2 text-sm">
                        </div>
                        @php
                            $leftNik = '';
                            if (!empty($signatures->left_org) && str_contains($signatures->left_org, 'NIK:')) {
                                $parts = explode('NIK:', $signatures->left_org);
                                $leftNik = trim($parts[1] ?? '');
                            }
                        @endphp
                        <div>
                            <label class="block mb-1">NIK</label>
                            <input type="text" name="left_nik" value="{{ old('left_nik', $leftNik) }}" class="w-full border rounded px-3 py-2 text-sm" placeholder="Misal: 271401002">
                            <p class="text-xs text-gray-500 mt-1">Akan ditampilkan di bawah nama</p>
                        </div>
                        <div>
                            <label class="block mb-2 font-medium text-gray-700 dark:text-gray-300">Tanda Tangan (gambar)</label>
                            <div class="relative">
                                <input 
                                    type="file" 
                                    name="left_signature" 
                                    accept="image/*" 
                                    id="left_signature"
                                    class="hidden"
                                    onchange="previewImage(this, 'left_preview')"
                                >
                                <label for="left_signature" class="flex items-center justify-center w-full px-4 py-6 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:border-blue-500 dark:hover:border-blue-400 transition">
                                    <div class="text-center">
                                        <svg class="mx-auto h-8 w-8 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-8l-3.172-3.172a4 4 0 00-5.656 0L28 20M9 20l3.172-3.172a4 4 0 015.656 0L28 20" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Klik untuk upload atau drag & drop</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">PNG, JPG, GIF (Max 5MB)</p>
                                    </div>
                                </label>
                            </div>
                            <div id="left_preview" class="mt-3"></div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold mb-2">Blok Kanan</h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <label class="block mb-1">Label</label>
                            <input type="text" name="right_label" value="{{ old('right_label', $signatures->right_label ?? 'Penguji Eksternal') }}" class="w-full border rounded px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block mb-1">Nama</label>
                            <input type="text" name="right_name" value="{{ old('right_name', $signatures->right_name ?? '') }}" class="w-full border rounded px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block mb-1">Instansi / Jabatan</label>
                            <input type="text" name="right_org" value="{{ old('right_org', $signatures->right_org ?? '') }}" class="w-full border rounded px-3 py-2 text-sm">
                        </div>
                        @php
                            $rightNik = '';
                            if (!empty($signatures->right_org) && str_contains($signatures->right_org, 'NIK:')) {
                                $parts = explode('NIK:', $signatures->right_org);
                                $rightNik = trim($parts[1] ?? '');
                            }
                        @endphp
                        <div>
                            <label class="block mb-1">NIK</label>
                            <input type="text" name="right_nik" value="{{ old('right_nik', $rightNik) }}" class="w-full border rounded px-3 py-2 text-sm" placeholder="Misal: 271401002">
                            <p class="text-xs text-gray-500 mt-1">Akan ditampilkan di bawah nama</p>
                        </div>
                        <div>
                            <label class="block mb-2 font-medium text-gray-700 dark:text-gray-300">Tanda Tangan (gambar)</label>
                            <div class="relative">
                                <input 
                                    type="file" 
                                    name="right_signature" 
                                    accept="image/*" 
                                    id="right_signature"
                                    class="hidden"
                                    onchange="previewImage(this, 'right_preview')"
                                >
                                <label for="right_signature" class="flex items-center justify-center w-full px-4 py-6 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:border-blue-500 dark:hover:border-blue-400 transition">
                                    <div class="text-center">
                                        <svg class="mx-auto h-8 w-8 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-8l-3.172-3.172a4 4 0 00-5.656 0L28 20M9 20l3.172-3.172a4 4 0 015.656 0L28 20" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Klik untuk upload atau drag & drop</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">PNG, JPG, GIF (Max 5MB)</p>
                                    </div>
                                </label>
                            </div>
                            <div id="right_preview" class="mt-3"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block mb-1 text-sm font-semibold">Kota</label>
                    <input type="text" name="city" value="{{ old('city', $signatures->city ?? 'Kota Depok') }}" class="w-full border rounded px-3 py-2 text-sm">
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <a href="{{ url()->previous() }}" class="rounded border px-4 py-2 text-sm">Kembali</a>
                <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white text-sm">Simpan Pengaturan</button>
            </div>
        </form>
    </div>

    <script>
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            preview.innerHTML = '';

            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    preview.innerHTML = '<p class="text-sm text-red-600 dark:text-red-400">File terlalu besar (Max 5MB)</p>';
                    input.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `
                        <div class="relative inline-block">
                            <img src="${e.target.result}" alt="Preview" class="h-32 w-32 object-cover rounded-lg border border-gray-200 dark:border-gray-700">
                            <button type="button" onclick="clearPreview('${input.id}', '${previewId}')" class="absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full p-1 transition">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">${file.name}</p>
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
            }
        }

        function clearPreview(inputId, previewId) {
            document.getElementById(inputId).value = '';
            document.getElementById(previewId).innerHTML = '';
        }

        // Drag and drop functionality
        ['left_signature', 'right_signature'].forEach(id => {
            const input = document.getElementById(id);
            const label = input.nextElementSibling;

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                label.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                label.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                label.addEventListener(eventName, unhighlight, false);
            });

            function highlight(e) {
                label.classList.add('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
            }

            function unhighlight(e) {
                label.classList.remove('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
            }

            label.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                input.files = files;
                
                // Trigger preview
                const previewId = id === 'left_signature' ? 'left_preview' : 'right_preview';
                previewImage(input, previewId);
            }
        });
    </script>
</x-layouts.app>
