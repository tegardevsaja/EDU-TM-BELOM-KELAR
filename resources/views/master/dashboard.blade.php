<x-layouts.app :title="__('Master Admin Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        
        <!-- Header -->
        <div class="mb-2">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Dashboard Overview</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Statistik dan ringkasan data sistem</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid gap-4 md:grid-cols-3">
            <!-- Total Pengguna -->
            <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pengguna</p>
                        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $totalUsers }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Akun terdaftar</p>
                    </div>
                    <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Siswa -->
            <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Siswa</p>
                        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $totalSiswa }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Siswa terdaftar</p>
                    </div>
                    <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Sertifikat Tercetak -->
            <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Sertifikat Tercetak</p>
                        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $totalSertifikat }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total dicetak</p>
                    </div>
                    <div class="w-16 h-16 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid gap-4 lg:grid-cols-2">
            
            <!-- Gender Distribution Chart -->
            <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Distribusi Siswa Berdasarkan Jenis Kelamin</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Persentase siswa laki-laki dan perempuan</p>
                </div>

                @if($totalSiswa > 0)
                    <div class="flex flex-col items-center justify-center py-8">
                        <!-- Donut Chart Container -->
                        <div class="relative w-64 h-64">
                            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                                <!-- Background Circle -->
                                <circle cx="50" cy="50" r="40" fill="none" stroke="#e5e7eb" stroke-width="20" class="dark:stroke-zinc-700"/>
                                
                                <!-- Male Segment -->
                                <circle cx="50" cy="50" r="40" fill="none" 
                                        stroke="#3b82f6" 
                                        stroke-width="20"
                                        stroke-dasharray="{{ $malePercentage * 2.513 }} 251.3"
                                        stroke-linecap="round"
                                        class="transition-all duration-1000"/>
                                
                                <!-- Female Segment -->
                                <circle cx="50" cy="50" r="40" fill="none" 
                                        stroke="#ec4899" 
                                        stroke-width="20"
                                        stroke-dasharray="{{ $femalePercentage * 2.513 }} 251.3"
                                        stroke-dashoffset="{{ -$malePercentage * 2.513 }}"
                                        stroke-linecap="round"
                                        class="transition-all duration-1000"/>
                            </svg>
                            
                            <!-- Center Text -->
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalSiswa }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Total Siswa</p>
                            </div>
                        </div>

                        <!-- Legend -->
                        <div class="mt-8 flex gap-8">
                            <div class="flex items-center gap-3">
                                <div class="w-4 h-4 rounded-full bg-blue-500"></div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Laki-laki</p>
                                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($malePercentage, 1) }}%</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $totalMale }} siswa</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-4 h-4 rounded-full bg-pink-500"></div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Perempuan</p>
                                    <p class="text-2xl font-bold text-pink-600 dark:text-pink-400">{{ number_format($femalePercentage, 1) }}%</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $totalFemale }} siswa</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                        <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <p class="text-lg font-medium">Belum ada data siswa</p>
                        <p class="text-sm mt-1">Tambahkan siswa untuk melihat statistik</p>
                    </div>
                @endif
            </div>

            <!-- User List -->
            <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Daftar Pengguna Terbaru</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $recentUsers->count() }} pengguna terdaftar terakhir</p>
                    </div>
                    <a href="{{ route('master.users') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                        Lihat Semua →
                    </a>
                </div>

                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($recentUsers as $user)
                        <div class="flex items-center gap-4 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-700/50 transition-colors">
                            <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $user->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $user->email }}</p>
                            </div>
                           <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
    @if($user->role === \App\Enums\UserRole::Master) bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300
    @elseif($user->role === \App\Enums\UserRole::Admin) bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300
    @elseif($user->role === \App\Enums\UserRole::Guru) bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
    @else bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300
    @endif">
    {{ ucfirst(str_replace('_', ' ', $user->role->value)) }}
</span>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <p>Belum ada pengguna</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Activities / Stats -->
        <div class="grid gap-4 md:grid-cols-3">
            <!-- Siswa Aktif -->
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-5">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $siswaAktif }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Siswa Aktif</p>
                    </div>
                </div>
            </div>

            <!-- Siswa Lulus -->
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-5">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M12 14l9-5-9-5-9 5 9 5z"/>
                            <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $siswaLulus }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Siswa Lulus</p>
                    </div>
                </div>
            </div>

            <!-- Siswa Pindah/Keluar -->
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-5">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $siswaPindahKeluar }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Pindah/Keluar</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-layouts.app>