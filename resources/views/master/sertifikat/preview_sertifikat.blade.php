<x-layouts.app :title="'Preview Sertifikat'">
<div class="max-w-7xl mx-auto p-4">
    
    {{-- Header --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold dark:text-white">Preview Sertifikat</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Template: {{ $template->name }}</p>
            </div>
            <a href="{{ url()->previous() }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition-colors cursor-pointer">
                ← Kembali
            </a>
        </div>
    </div>

    {{-- Certificate Preview --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <div class="bg-gray-100 dark:bg-gray-900 rounded-lg overflow-hidden">
            <div class="relative w-full" style="padding-bottom: 70.7%; /* A4 Landscape ratio */">
                <div class="absolute inset-0">
                    {{-- Background --}}
                    <div class="absolute inset-0 bg-cover bg-center" 
                         style="background-image: url('{{ url('storage/' . $template->background_image) }}')">
                    </div>
                    
                    {{-- Elements --}}
                    @foreach($elements as $element)
                        <div class="absolute" 
                             style="left: {{ $element['x'] }}%; 
                                    top: {{ $element['y'] }}%; 
                                    transform: translate(-50%, -50%);
                                    font-size: {{ $element['fontSize'] }}px;
                                    font-family: {{ $element['fontFamily'] }};
                                    color: {{ $element['color'] }};
                                    font-weight: {{ $element['bold'] ? 'bold' : 'normal' }};
                                    text-align: {{ $element['align'] }};
                                    min-width: 100px;">
                            @if($element['type'] === 'text')
                                <div>{{ $element['content'] }}</div>
                            @elseif($element['type'] === 'variable')
                                <div>{{ $sampleData[$element['variable']] ?? $element['variable'] }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Sample Data Info --}}
        <div class="mt-6 bg-blue-50 dark:bg-blue-900 dark:bg-opacity-20 p-4 rounded-lg">
            <h3 class="font-semibold text-blue-800 dark:text-blue-300 mb-3">📋 Data Sample Preview</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                @foreach($sampleData as $key => $value)
                    <div>
                        <span class="text-gray-600 dark:text-gray-400">{{ str_replace('$', '', $key) }}:</span>
                        <span class="font-semibold dark:text-white ml-1">{{ $value }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="mt-6 flex gap-3 justify-center">
            <a href="{{ route('certificates.generate-bulk', $template->id) }}" 
               class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold transition-colors cursor-pointer inline-flex items-center shadow-lg">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Generate Sertifikat
            </a>
            
            <button onclick="window.print()" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold transition-colors cursor-pointer inline-flex items-center shadow-lg">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print Preview
            </button>
        </div>
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    .bg-gray-100, .bg-gray-100 * {
        visibility: visible;
    }
    .bg-gray-100 {
        position: fixed;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
    }
}
</style>
</x-layouts.app>