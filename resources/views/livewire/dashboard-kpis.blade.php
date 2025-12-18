<div>
    <div class="grid gap-4 grid-cols-1 sm:grid-cols-3">
        {{-- Total Siswa dengan filter gender --}}
        <div class="group rounded-xl border border-zinc-200/70 dark:border-zinc-800/70 bg-white dark:bg-zinc-900 p-5 relative ring-1 ring-indigo-500/20">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 to-indigo-500/0"></div>
            <div class="relative z-10 flex items-start justify-between gap-2">
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Total Siswa</p>
                    <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-zinc-50">{{ $totalSiswa }}</p>
                    <p class="mt-1 text-[11px] text-zinc-500 dark:text-zinc-400">
                        @switch($siswaGenderFilter)
                            @case('L') Laki-laki @break
                            @case('P') Perempuan @break
                            @default Semua
                        @endswitch
                    </p>
                </div>
                <div class="relative" x-data="{ open: false }">
                    <button @click.stop="open = !open" type="button" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-xl leading-none px-1">⋯</button>
                    <div x-cloak x-show="open" @click.outside="open = false" class="absolute top-full right-0 mt-1 z-20 w-32 rounded-md border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-md text-[11px] text-zinc-600 dark:text-zinc-300">
                        <button type="button" wire:click="setSiswaGenderFilter('all')" class="w-full text-left px-3 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ $siswaGenderFilter === 'all' ? 'font-semibold text-indigo-600' : '' }}">Semua</button>
                        <button type="button" wire:click="setSiswaGenderFilter('L')" class="w-full text-left px-3 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ $siswaGenderFilter === 'L' ? 'font-semibold text-indigo-600' : '' }}">Laki-laki</button>
                        <button type="button" wire:click="setSiswaGenderFilter('P')" class="w-full text-left px-3 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ $siswaGenderFilter === 'P' ? 'font-semibold text-indigo-600' : '' }}">Perempuan</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Pengguna dengan filter role --}}
        <div class="group rounded-xl border border-zinc-200/70 dark:border-zinc-800/70 bg-white dark:bg-zinc-900 p-5 relative ring-1 ring-violet-500/20">
            <div class="absolute inset-0 bg-gradient-to-br from-violet-500/10 to-violet-500/0"></div>
            <div class="relative z-10 flex items-start justify-between gap-2">
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Total Pengguna</p>
                    <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-zinc-50">{{ $totalUsers }}</p>
                    <p class="mt-1 text-[11px] text-zinc-500 dark:text-zinc-400">
                        @switch($userRoleFilter)
                            @case('master_admin') Master Admin @break
                            @case('admin') Admin @break
                            @case('guru') Guru @break
                            @default Semua role
                        @endswitch
                    </p>
                </div>
                <div class="relative" x-data="{ open: false }">
                    <button @click.stop="open = !open" type="button" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-xl leading-none px-1">⋯</button>
                    <div x-cloak x-show="open" @click.outside="open = false" class="absolute top-full right-0 mt-1 z-20 w-36 rounded-md border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-md text-[11px] text-zinc-600 dark:text-zinc-300">
                        <button type="button" wire:click="setUserRoleFilter('all')" class="w-full text-left px-3 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ $userRoleFilter === 'all' ? 'font-semibold text-violet-600' : '' }}">Semua role</button>
                        <button type="button" wire:click="setUserRoleFilter('master_admin')" class="w-full text-left px-3 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ $userRoleFilter === 'master_admin' ? 'font-semibold text-violet-600' : '' }}">Master Admin</button>
                        <button type="button" wire:click="setUserRoleFilter('admin')" class="w-full text-left px-3 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ $userRoleFilter === 'admin' ? 'font-semibold text-violet-600' : '' }}">Admin</button>
                        <button type="button" wire:click="setUserRoleFilter('guru')" class="w-full text-left px-3 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ $userRoleFilter === 'guru' ? 'font-semibold text-violet-600' : '' }}">Guru</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sertifikat Tercetak dengan filter range waktu --}}
        <div class="group rounded-xl border border-zinc-200/70 dark:border-zinc-800/70 bg-white dark:bg-zinc-900 p-5 relative ring-1 ring-emerald-500/20">
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-emerald-500/0"></div>
            <div class="relative z-10 flex justify-between items-start gap-2">
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Sertifikat Tercetak</p>
                    <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-zinc-50">{{ $totalSertifikat }}</p>
                    <p class="mt-1 text-[11px] text-zinc-500 dark:text-zinc-400">
                        @switch($sertifikatRange)
                            @case('week') Minggu ini @break
                            @case('month') Bulan ini @break
                            @case('year') Tahun ini @break
                            @default Semua waktu
                        @endswitch
                    </p>
                </div>
                <div class="relative" x-data="{ open: false }">
                    <button @click.stop="open = !open" type="button" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-xl leading-none px-1">⋯</button>
                    <div x-cloak x-show="open" @click.outside="open = false" class="absolute right-0 mt-1 w-32 rounded-md border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-md text-[11px] text-zinc-600 dark:text-zinc-300">
                        <button type="button" wire:click="setSertifikatRange('all')" class="w-full text-left px-3 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ $sertifikatRange === 'all' ? 'font-semibold text-emerald-600' : '' }}">Semua waktu</button>
                        <button type="button" wire:click="setSertifikatRange('week')" class="w-full text-left px-3 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ $sertifikatRange === 'week' ? 'font-semibold text-emerald-600' : '' }}">Minggu ini</button>
                        <button type="button" wire:click="setSertifikatRange('month')" class="w-full text-left px-3 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ $sertifikatRange === 'month' ? 'font-semibold text-emerald-600' : '' }}">Bulan ini</button>
                        <button type="button" wire:click="setSertifikatRange('year')" class="w-full text-left px-3 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ $sertifikatRange === 'year' ? 'font-semibold text-emerald-600' : '' }}">Tahun ini</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
