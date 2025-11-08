<x-layouts.app :title="__('Preview Import Siswa')">
    <div class="max-w-7xl mx-auto px-4 py-8">
        
        <!-- Header -->
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Preview Data Import Siswa</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Periksa data sebelum menyimpan ke database</p>
        </div>

        {{-- Notifikasi Error --}}
        @if(session('error'))
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 p-4 rounded-xl mb-6">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <!-- Total Rows -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl p-5 border border-gray-200 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Total Data</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalRows }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Valid Rows -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl p-5 border border-gray-200 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Data Valid</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $validRows }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Error Rows -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl p-5 border border-gray-200 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Data Error</p>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $errorRows }}</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Import Settings -->
            <div class="bg-gradient-to-br from-purple-50 to-blue-50 dark:from-purple-900/20 dark:to-blue-900/20 rounded-xl p-5 border border-purple-200 dark:border-purple-800">
                <p class="text-sm text-purple-700 dark:text-purple-300 mb-1">Pengaturan Import</p>
                <p class="text-xs text-purple-600 dark:text-purple-400">{{ $kelas->nama_kelas }}</p>
                <p class="text-xs text-purple-600 dark:text-purple-400">{{ $jurusan->nama_jurusan }}</p>
            </div>
        </div>

        <!-- Import Settings Info -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="text-sm">
                    <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">Pengaturan yang Akan Diterapkan</h4>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-blue-700 dark:text-blue-300">
                        <div>
                            <span class="font-medium">Kelas:</span> {{ $kelas->nama_kelas }}
                        </div>
                        <div>
                            <span class="font-medium">Jurusan:</span> {{ $jurusan->nama_jurusan }}
                        </div>
                        <div>
                            <span class="font-medium">Tahun Ajaran:</span> 
                            {{ \Carbon\Carbon::parse($tahunAjaran->tanggal_mulai)->format('Y') }}/{{ \Carbon\Carbon::parse($tahunAjaran->tanggal_selesai)->format('Y') }}
                        </div>
                        <div>
                            <span class="font-medium">Tahun Masuk:</span> {{ $tahunMasuk }}
                        </div>
                        <div>
                            <span class="font-medium">Status:</span> {{ $status }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error List (if any) -->
        @if($errorRows > 0)
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-5 mb-6">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <h3 class="font-semibold text-red-900 dark:text-red-100">Data dengan Error</h3>
                </div>
                <div class="space-y-2 max-h-60 overflow-y-auto">
                    @foreach($errors as $error)
                        <div class="bg-white dark:bg-zinc-800 rounded-lg p-3 text-sm">
                            <div class="flex items-start gap-2">
                                <span class="font-medium text-red-600 dark:text-red-400">Baris {{ $error['row_number'] }}:</span>
                                <div class="flex-1">
                                    <p class="text-gray-900 dark:text-white font-medium">{{ $error['nama'] ?? 'Nama tidak ada' }} (NIS: {{ $error['nis'] ?? '-' }})</p>
                                    <ul class="mt-1 text-red-600 dark:text-red-400 list-disc list-inside">
                                        @foreach($error['errors'] as $err)
                                            <li>{{ $err }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-red-700 dark:text-red-300 mt-3">
                    ⚠️ Data dengan error akan dilewati saat import
                </p>
            </div>
        @endif

        <!-- Data Preview Table -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700">
            <div class="p-6 border-b border-gray-200 dark:border-zinc-700">
                <h3 class="font-semibold text-gray-900 dark:text-white">Preview Data ({{ $totalRows }} baris)</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-zinc-700/50 border-b border-gray-200 dark:border-zinc-700">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">Baris</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">NIS</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">Nama</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">JK</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">Tempat Lahir</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">Tanggal Lahir</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">Agama</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">Orang Tua</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">Asal Sekolah</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-800 divide-y divide-gray-200 dark:divide-zinc-700">
                        @foreach($previewData as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-zinc-700/30 transition-colors {{ isset($row['errors']) ? 'bg-red-50 dark:bg-red-900/10' : '' }}">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if(isset($row['errors']))
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                            Error
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            Valid
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $row['row_number'] }}</td>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100 whitespace-nowrap font-mono">{{ $row['nis'] ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $row['nama'] ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100 whitespace-nowrap">{{ $row['jenis_kelamin'] ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $row['tempat_lahir'] ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100 whitespace-nowrap">{{ $row['tanggal_lahir'] ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $row['agama'] ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $row['nama_orang_tua'] ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $row['asal_sekolah'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Action Buttons -->
            <form action="{{ route('master.siswa.import.store') }}" method="POST" class="p-6 border-t border-gray-200 dark:border-zinc-700">
                @csrf
                <div class="flex items-center justify-between">
                    <a href="{{ url('master/siswa/import') }}" 
                       class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-700 border border-gray-300 dark:border-zinc-600 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-600 transition-colors">
                        Batal
                    </a>
                    <div class="flex gap-3">
                        @if($validRows > 0)
                            <button type="submit" 
                                    class="px-6 py-2.5 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white text-sm font-semibold rounded-lg shadow-lg shadow-green-500/30 hover:shadow-green-500/50 transition-all duration-300 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                </svg>
                                Import {{ $validRows }} Data Valid
                            </button>
                        @else
                            <button type="button" disabled
                                    class="px-6 py-2.5 bg-gray-400 text-white text-sm font-semibold rounded-lg cursor-not-allowed opacity-60">
                                Tidak Ada Data Valid untuk Diimport
                            </button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>