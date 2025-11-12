<x-layouts.app :title="'Pilih Template Sertifikat'">
    <h2 class="text-xl font-semibold mb-6 dark:text-white">Pilih Template Sertifikat</h2>

    <form action="{{ route('master.sertifikat.select_students', 'TEMPLATE_ID') }}" method="GET" id="templateForm">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach ($templates as $template)
                <label class="template-item p-4 bg-white dark:bg-gray-800 rounded shadow cursor-pointer border border-transparent hover:border-purple-400 transition"
                    data-id="{{ $template->id }}">
                    <input type="radio" name="template_id" value="{{ $template->id }}" class="hidden template-radio">

                    @if ($template->background_image)
                        <img src="{{ asset('storage/' . $template->background_image) }}" 
                            alt="{{ $template->nama_template }}" 
                            class="w-full h-40 object-cover rounded mb-3">
                    @else
                        <div class="w-full h-40 bg-gray-300 flex items-center justify-center rounded mb-3">
                            <span class="text-gray-600 text-sm">Tidak ada gambar</span>
                        </div>
                    @endif

                    <h3 class="font-semibold text-lg">{{ $template->nama_template }}</h3>
                    <p class="text-sm text-gray-500">{{ $template->deskripsi ?? '-' }}</p>
                </label>
            @endforeach
        </div>

        {{-- Tombol Next --}}
        <div class="mt-6">
            <button type="submit" id="nextBtn" class="px-4 py-2 bg-purple-600 text-white rounded opacity-50 cursor-not-allowed " disabled>
                Next
            </button>
        </div>
    </form>

    <script>
        const radios = document.querySelectorAll('.template-radio');
        const labels = document.querySelectorAll('.template-item');
        const nextBtn = document.getElementById('nextBtn');
        const form = document.getElementById('templateForm');

        radios.forEach(radio => {
            radio.addEventListener('change', function () {
                labels.forEach(label => {
                    label.classList.remove('border-purple-500', 'ring-2', 'ring-purple-400');
                    label.classList.add('border-transparent');
                });

                const selectedLabel = this.closest('.template-item');
                selectedLabel.classList.remove('border-transparent');
                selectedLabel.classList.add('border-purple-500', 'ring-2', 'ring-purple-400');

                nextBtn.disabled = false;
                nextBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            });
        });

        form.addEventListener('submit', function (e) {
            const selected = document.querySelector('.template-radio:checked');
            if (selected) {
                this.action = this.action.replace('TEMPLATE_ID', selected.value);
            } else {
                e.preventDefault();
            }
        });
    </script>
</x-layouts.app>
