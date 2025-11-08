<x-layouts.app :title="'Pilih Template Sertifikat'">
    <div class="max-w-3xl mx-auto bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6">
        <h2 class="text-2xl font-semibold mb-4 dark:text-white">
            Pilih Kelas
        </h2>

        <p class="text-gray-700 dark:text-gray-300 mb-6">
            Template yang dipilih: 
            <span class="font-bold">{{ $template->nama_template }}</span>
        </p>

        <form action="{{ route('master.sertifikat.generate.customize', $template->id) }}" method="GET">
            <div>
                <label for="kelas" class="block font-medium mb-1 dark:text-gray-200">Pilih Kelas:</label>
                <select 
                    name="kelas_id" 
                    id="kelas" 
                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                >
                    <!-- opsi default -->
                    <option value="">Tidak memilih kelas</option>

                    <!-- daftar kelas -->
                    @foreach ($kelas as $k)
                        <option value="{{ $k->id }}">{{ $k->nama_kelas ?? $k->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end mt-4">
                <button 
                    type="submit" 
                    class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-md shadow-md transition"
                >
                    Lanjut
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
