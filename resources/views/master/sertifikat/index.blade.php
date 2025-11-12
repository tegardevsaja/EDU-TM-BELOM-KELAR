{{-- resources/views/master/sertifikat/index.blade.php --}}
@php
    $routePrefix = 'master'; // default
    if (auth()->check() && method_exists(auth()->user(), 'hasRole')) {
        if (auth()->user()->hasRole('master_admin')) {
            $routePrefix = 'master';
        } elseif (auth()->user()->hasRole('admin')) {
            $routePrefix = 'admin';
        } elseif (auth()->user()->hasRole('guru')) {
            $routePrefix = 'guru';
        }
    }
@endphp

<x-layouts.app :title="'Template Sertifikat'">
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto p-6">
            {{-- Header Section --}}
            <div class="mb-8">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-1">
                            Manajemen Sertifikat
                        </h1>
                        <p class="text-gray-600">Kelola template dan cetak sertifikat dengan mudah</p>
                    </div>
                    <div class="flex gap-3">
                        @can('sertifikat.view')
                        <a href="{{ route($routePrefix . '.sertifikat.select_template') }}" 
                           class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 hover:border-gray-400 transition-all shadow-sm flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Cetak Sertifikat
                        </a>
                        @endcan
                        
                        @can('sertifikat.create')
                        <a href="{{ route($routePrefix . '.sertifikat.create') }}" 
                           class="px-5 py-2.5 bg-gray-900 text-white rounded-lg font-medium hover:bg-gray-800 transition-all shadow-sm flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Tambah Template
                        </a>
                        @endcan
                    </div>
                </div>
            </div>

            {{-- Alert Success --}}
            @if(session('success'))
            <div class="mb-6 bg-white border border-green-200 p-4 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-800 font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Stats Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 font-medium mb-1">Total Template</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $templates->count() }}</p>
                        </div>
                        <div class="bg-gray-100 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 font-medium mb-1">Sertifikat Tercetak</p>
                            <p class="text-2xl font-bold text-gray-900">0</p>
                        </div>
                        <div class="bg-gray-100 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 font-medium mb-1">Bulan Ini</p>
                            <p class="text-2xl font-bold text-gray-900">0</p>
                        </div>
                        <div class="bg-gray-100 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Templates Grid --}}
            @if($templates->count() > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                    </svg>
                    Daftar Template
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($templates as $item)
                    <div class="group bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 overflow-hidden">
                        {{-- Image Preview --}}
                        <div class="relative h-48 bg-gray-100 overflow-hidden">
                            <img src="{{ asset('storage/'.$item->background_image) }}" 
                                 alt="{{ $item->nama_template }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute top-3 right-3">
                                <span class="bg-white px-2.5 py-1 rounded-md text-xs font-semibold text-gray-700 shadow-sm border border-gray-200">
                                    Template
                                </span>
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="p-5">
                            <h3 class="font-semibold text-base text-gray-900 mb-2 truncate">
                                {{ $item->nama_template }}
                            </h3>
                            
                            <div class="flex items-center text-xs text-gray-500 mb-4">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                </svg>
                                <span>{{ \Carbon\Carbon::parse($item->created_at)->format('d M Y') }}</span>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="flex gap-2">
                                @can('sertifikat.view')
                                <button onclick="previewTemplate('{{ asset('storage/'.$item->background_image) }}', '{{ $item->nama_template }}')" 
                                        class="flex-1 bg-gray-50 text-gray-700 px-3 py-2 rounded-md hover:bg-gray-100 transition-colors font-medium text-sm flex items-center justify-center gap-1 border border-gray-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <span>Preview</span>
                                </button>
                                @endcan
                                
                                @can('sertifikat.update')
                                <a href="{{ route($routePrefix . '.sertifikat.edit', $item->id) }}" 
                                   class="flex-1 bg-gray-50 text-gray-700 px-3 py-2 rounded-md hover:bg-gray-100 transition-colors font-medium text-sm flex items-center justify-center gap-1 border border-gray-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span>Edit</span>
                                </a>
                                @endcan
                                
                                @can('sertifikat.delete')
                                <form action="{{ route($routePrefix . '.sertifikat.destroy', $item->id) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Yakin ingin menghapus template ini?')" 
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="bg-gray-50 text-red-600 px-3 py-2 rounded-md hover:bg-red-50 transition-colors font-medium text-sm flex items-center justify-center border border-gray-200 hover:border-red-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            {{-- Empty State --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-16 text-center">
                <div class="max-w-md mx-auto">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Belum Ada Template</h3>
                    <p class="text-gray-600 mb-8">Mulai dengan menambahkan template sertifikat pertama Anda</p>
                    @can('sertifikat.create')
                    <a href="{{ route($routePrefix . '.sertifikat.create') }}" 
                       class="inline-flex items-center gap-2 px-6 py-3 bg-gray-900 text-white rounded-lg font-medium hover:bg-gray-800 transition-all shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Template Pertama
                    </a>
                    @endcan
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Preview Modal --}}
    <div id="previewModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
            <div class="p-5 border-b border-gray-200 flex items-center justify-between bg-gray-50">
                <h3 id="previewTitle" class="text-lg font-semibold text-gray-900"></h3>
                <button onclick="closePreview()" class="text-gray-400 hover:text-gray-600 transition-colors p-2 hover:bg-gray-100 rounded-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)] bg-white">
                <img id="previewImage" src="" alt="" class="w-full h-auto rounded-lg shadow-sm border border-gray-200">
            </div>
        </div>
    </div>

    <script>
        function previewTemplate(imageSrc, templateName) {
            document.getElementById('previewModal').classList.remove('hidden');
            document.getElementById('previewImage').src = imageSrc;
            document.getElementById('previewTitle').textContent = templateName;
            document.body.style.overflow = 'hidden';
        }

        function closePreview() {
            document.getElementById('previewModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePreview();
            }
        });

        // Close modal on background click
        document.getElementById('previewModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closePreview();
            }
        });
    </script>
</x-layouts.app>