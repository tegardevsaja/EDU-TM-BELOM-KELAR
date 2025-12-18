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

<div class="mt-4">
    <!-- Filter & Search Bar -->
    <div class="mb-3 flex flex-col gap-2 md:flex-row md:items-center">
        <!-- Input Search dengan tombol Clear (X) -->
        <div class="relative flex-1">
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search"
                placeholder="Cari siswa berdasarkan nama atau NIS..."
                class="border rounded-lg px-3 py-2 pr-10 w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
            
            <!-- Tombol X untuk clear search -->
            @if($search)
                <button 
                    wire:click="clearSearch"
                    type="button"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none"
                    title="Clear search"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            @endif
        </div>

        <!-- Filter Kelas & Jurusan + tombol Cari + Export -->
        <div class="flex flex-col gap-2 md:flex-row md:items-center">
            <select
                wire:model="kelasId"
                class="border rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
                <option value="">Semua Kelas</option>
                @foreach($kelasList as $k)
                    <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                @endforeach
            </select>

            <select
                wire:model="jurusanId"
                class="border rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
                <option value="">Semua Jurusan</option>
                @foreach($jurusanList as $j)
                    <option value="{{ $j->id }}">{{ $j->nama_jurusan }}</option>
                @endforeach
            </select>

            <button
                type="button"
                wire:click="applyFilters"
                class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-lg border border-blue-500 text-blue-600 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                Cari
            </button>

            @can('siswa.export')
            <a
                href="{{ route($routePrefix . '.siswa.export.excel') }}?kelas_id={{ $kelasId }}&jurusan_id={{ $jurusanId }}&search={{ urlencode($search) }}"
                class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-lg border border-emerald-600 text-emerald-700 hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-500"
            >
                Export Excel
            </a>
            <a
                href="{{ route($routePrefix . '.siswa.export.csv') }}?kelas_id={{ $kelasId }}&jurusan_id={{ $jurusanId }}&search={{ urlencode($search) }}"
                class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-lg border border-slate-500 text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-500"
            >
                Export CSV
            </a>
            @endcan
        </div>
    </div>

    <!-- Info hasil search -->
    @if($search)
        <div class="mb-3 text-sm text-gray-600">
            Menampilkan hasil pencarian untuk: <span class="font-semibold">"{{ $search }}"</span>
        </div>
    @endif

    <table class="min-w-full text-sm text-left border">
        <thead class="border-b bg-gray-50 text-gray-700 uppercase text-xs">
            <tr>
                <th class="py-3 px-4 font-semibold">No</th>
                <th class="py-3 px-4 font-semibold">Nama</th>
                <th class="py-3 px-4 font-semibold">NIS</th>
                <th class="py-3 px-4 font-semibold">Kelas</th>
                <th class="py-3 px-4 font-semibold">Jurusan</th>
                <th class="py-3 px-4 font-semibold">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($siswas as $index => $swa)
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="py-2 px-3">{{ $siswas->firstItem() + $index }}</td>
                    <td class="py-2 px-3">
                        {!! $search ? preg_replace('/(' . preg_quote($search, '/') . ')/i', '<mark class="bg-yellow-200">$1</mark>', e($swa->nama)) : e($swa->nama) !!}
                    </td>
                    <td class="py-2 px-3">
                        {!! $search ? preg_replace('/(' . preg_quote($search, '/') . ')/i', '<mark class="bg-yellow-200">$1</mark>', e($swa->nis)) : e($swa->nis) !!}
                    </td>
                    <td class="py-2 px-3">{{ $swa->kelas->nama_kelas ?? '-' }}</td>
                    <td class="py-2 px-3">{{ $swa->jurusan->nama_jurusan ?? '-' }}</td>
                    <td class="py-3 px-4 align-top">
                        <div class="flex flex-wrap items-center gap-2">
                            @can('siswa.update')
                                <a href="{{ route($routePrefix . '.siswa.edit', $swa->id) }}"
                                   class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-2.5 py-1 text-xs font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="w-3.5 h-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 3.5 20.5 8.25M4 20h4.75L20.5 8.25l-4.75-4.75L4 15.25V20Z" />
                                    </svg>
                                    <span>Edit</span>
                                </a>
                            @endcan

                            @can('siswa.delete')
                                <form action="{{ route($routePrefix . '.siswa.destroy', $swa->id) }}"
                                      method="POST"
                                      class="inline delete-siswa-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        class="inline-flex items-center gap-1 rounded-lg bg-red-600 px-2.5 py-1 text-xs font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                                        data-confirm-delete
                                        data-name="{{ $swa->nama }}"
                                        data-title="Hapus Siswa?"
                                        data-confirm-label="Ya, hapus"
                                        data-cancel-label="Batal">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="w-3.5 h-3.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M10 11v6m4-6v6M9 4h6a1 1 0 0 1 1 1v2H8V5a1 1 0 0 1 1-1Zm-2 3h12l-1 13H8L7 7Z" />
                                        </svg>
                                        <span>Hapus</span>
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-6 text-gray-500">
                        @if($search)
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                <p>Tidak ada data siswa dengan kata kunci <strong>"{{ $search }}"</strong></p>
                                <button 
                                    wire:click="clearSearch"
                                    class="text-blue-600 hover:underline text-sm"
                                >
                                    Tampilkan semua data
                                </button>
                            </div>
                        @else
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                <p>Tidak ada data siswa.</p>
                            </div>
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-3">
        {{ $siswas->links('vendor.pagination.siswa') }}
    </div>
</div>