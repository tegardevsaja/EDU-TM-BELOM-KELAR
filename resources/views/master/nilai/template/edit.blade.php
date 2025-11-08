<x-layouts.app :title="__('Edit Template Penilaian')">
    <div class="max-w-5xl mx-auto p-6 bg-white dark:bg-zinc-800 shadow rounded">
        <h2 class="text-lg font-semibold mb-4">Edit Template Penilaian</h2>

        <form action="{{ route('master.penilaian.update', $template->id) }}" method="POST">
            @csrf
            @method('PUT') {{-- Penting untuk PUT/UPDATE --}}

            {{-- Nama Template --}}
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Nama Template <span class="text-red-500">*</span></label>
                <input type="text" name="nama_template"
                       class="w-full px-4 py-2 border rounded focus:outline-none focus:ring"
                       value="{{ old('nama_template', $template->nama_template) }}" required>
                @error('nama_template')
                    <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror
            </div>

            {{-- Deskripsi --}}
            <div class="mb-6">
                <label class="block text-sm font-medium mb-1">Deskripsi (opsional)</label>
                <textarea name="deskripsi" rows="3"
                          class="w-full px-4 py-2 border rounded focus:outline-none focus:ring">{{ old('deskripsi', $template->deskripsi) }}</textarea>
                @error('deskripsi')
                    <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror
            </div>

            {{-- Komponen Dinamis --}}
            <div id="komponen-container" class="mb-6">
                <label class="block text-sm font-medium mb-2">Komponen Penilaian</label>

                {{-- Tombol Tambah Komponen --}}
                <button type="button" id="add-komponen"
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    + Tambah Komponen
                </button>

                {{-- Render komponen lama --}}
                @if($template->komponen)
                    @foreach($template->komponen as $i => $komponen)
                        <div class="komponen-item border p-4 mt-4 rounded bg-gray-50 dark:bg-zinc-700">
                            <div class="flex justify-between items-center mb-3">
                                <input type="text" name="komponen[{{ $i }}][kategori]"
                                    placeholder="Nama Komponen"
                                    class="w-full px-3 py-2 border rounded focus:outline-none focus:ring"
                                    value="{{ $komponen['kategori'] ?? '' }}">
                                <button type="button"
                                    class="ml-2 px-3 py-2 bg-red-600 text-white rounded remove-komponen hover:bg-red-700">
                                    Hapus
                                </button>
                            </div>

                            <div class="subkomponen-wrapper">
                                <label class="text-sm text-gray-600 dark:text-gray-300 mb-1 block">Subkomponen</label>

                                @if(!empty($komponen['subkomponen']))
                                    @foreach($komponen['subkomponen'] as $j => $sub)
                                        <div class="subkomponen-item flex gap-2 mb-2">
                                            <input type="text" name="komponen[{{ $i }}][subkomponen][{{ $j }}][uraian]"
                                                placeholder="Uraian Penilaian"
                                                class="w-full px-3 py-2 border rounded focus:outline-none focus:ring"
                                                value="{{ $sub['uraian'] ?? '' }}">
                                            <button type="button"
                                                class="px-3 py-2 bg-red-500 text-white rounded remove-sub hover:bg-red-600">x</button>
                                        </div>
                                    @endforeach
                                @endif

                                <button type="button"
                                    class="add-sub px-3 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 mt-2">
                                    + Tambah Subkomponen
                                </button>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- Tombol --}}
            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('master.penilaian') }}"
                   class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                    Batal
                </a>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Update
                </button>
            </div>
        </form>
    </div>

    {{-- Script Dinamis --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('komponen-container');
            const addKomponenBtn = document.getElementById('add-komponen');

            addKomponenBtn.addEventListener('click', () => {
                const index = container.querySelectorAll('.komponen-item').length;

                const komponenHTML = `
                    <div class="komponen-item border p-4 mt-4 rounded bg-gray-50 dark:bg-zinc-700">
                        <div class="flex justify-between items-center mb-3">
                            <input type="text" name="komponen[${index}][kategori]"
                                placeholder="Nama Komponen"
                                class="w-full px-3 py-2 border rounded focus:outline-none focus:ring">
                            <button type="button"
                                class="ml-2 px-3 py-2 bg-red-600 text-white rounded remove-komponen hover:bg-red-700">
                                Hapus
                            </button>
                        </div>

                        <div class="subkomponen-wrapper">
                            <label class="text-sm text-gray-600 dark:text-gray-300 mb-1 block">Subkomponen</label>
                            <div class="subkomponen-item flex gap-2 mb-2">
                                <input type="text" name="komponen[${index}][subkomponen][0][uraian]"
                                    placeholder="Uraian Penilaian"
                                    class="w-full px-3 py-2 border rounded focus:outline-none focus:ring">
                                <button type="button"
                                    class="px-3 py-2 bg-red-500 text-white rounded remove-sub hover:bg-red-600">x</button>
                            </div>
                            <button type="button"
                                class="add-sub px-3 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 mt-2">
                                + Tambah Subkomponen
                            </button>
                        </div>
                    </div>
                `;

                addKomponenBtn.insertAdjacentHTML('beforebegin', komponenHTML);
            });

            // Delegation event
            container.addEventListener('click', (e) => {
                if (e.target.classList.contains('remove-komponen')) {
                    e.target.closest('.komponen-item').remove();
                }

                if (e.target.classList.contains('add-sub')) {
                    const komponenDiv = e.target.closest('.komponen-item');
                    const komponenIndex = [...container.querySelectorAll('.komponen-item')].indexOf(komponenDiv);
                    const subWrapper = e.target.closest('.subkomponen-wrapper');
                    const subIndex = subWrapper.querySelectorAll('.subkomponen-item').length;

                    const subHTML = `
                        <div class="subkomponen-item flex gap-2 mb-2">
                            <input type="text" name="komponen[${komponenIndex}][subkomponen][${subIndex}][uraian]"
                                placeholder="Uraian Penilaian"
                                class="w-full px-3 py-2 border rounded focus:outline-none focus:ring">
                            <button type="button"
                                class="px-3 py-2 bg-red-500 text-white rounded remove-sub hover:bg-red-600">x</button>
                        </div>
                    `;
                    e.target.insertAdjacentHTML('beforebegin', subHTML);
                }

                if (e.target.classList.contains('remove-sub')) {
                    e.target.closest('.subkomponen-item').remove();
                }
            });
        });
    </script>
</x-layouts.app>
