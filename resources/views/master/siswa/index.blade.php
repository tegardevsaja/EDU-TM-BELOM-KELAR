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

<x-layouts.app :title="__('Master Admin Dashboard')">
  <p class="text-2xl font-bold">Data Siswa</p>
  <p class="text-sm text-gray-600 mb-8">daftar data siswa SMK Tunas Media</p>

  {{-- Notifikasi CRUD Siswa --}}
  @if(session('success') || session('error'))
      <div class="mb-4 space-y-2">
          @if(session('success'))
              <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition
                   class="flex items-start gap-2 rounded-md border border-emerald-300 bg-emerald-50 px-3 py-2 text-sm text-emerald-800 dark:bg-emerald-900/40 dark:border-emerald-700 dark:text-emerald-100">
                  <div class="mt-0.5">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                  </div>
                  <div class="flex-1">
                      <p class="font-medium">Berhasil</p>
                      <p class="text-xs mt-0.5">{{ session('success') }}</p>
                  </div>
                  <button type="button" class="text-emerald-700 hover:text-emerald-900 dark:text-emerald-200" @click="show = false">
                      ✕
                  </button>
              </div>
          @endif

          @if(session('error'))
              <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition
                   class="flex items-start gap-2 rounded-md border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-800 dark:bg-red-900/40 dark:border-red-700 dark:text-red-100">
                  <div class="mt-0.5">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/></svg>
                  </div>
                  <div class="flex-1">
                      <p class="font-medium">Terjadi kesalahan</p>
                      <p class="text-xs mt-0.5">{{ session('error') }}</p>
                  </div>
                  <button type="button" class="text-red-700 hover:text-red-900 dark:text-red-200" @click="show = false">
                      ✕
                  </button>
              </div>
          @endif
      </div>
  @endif

  <div class="flex gap-2 mb-4">
    @can('siswa.template')
    <div>
         <a href="{{ route($routePrefix . '.siswa.template-download') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-400 focus:outline-none transition">
            Download Template Excel
        </a>
    </div>
    @endcan
    @can('siswa.import')
    <div>
        <a href="{{ route('master.siswa.import') }}" class="inline-flex items-center px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 focus:ring-2 focus:ring-amber-400 focus:outline-none transition">
            Import Data Siswa
        </a>
    </div>
    @endcan
    
    @can('siswa.create')
    <div>
      <a href="{{ route($routePrefix . '.siswa.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-400 focus:outline-none transition">
        Tambah Siswa
      </a>
    </div>
    @endcan
  </div>  

  <!-- Livewire Component - sudah include search + table + pagination -->
  <livewire:siswa-search />

  @if(session('success') || session('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition
         class="fixed bottom-0 right-0 m-4 rounded-md border border-emerald-300 bg-emerald-50 px-3 py-2 text-sm text-emerald-800 dark:bg-emerald-900/40 dark:border-emerald-700 dark:text-emerald-100">
        @if(session('success'))
          <div class="flex items-start gap-2">
            <div class="mt-0.5">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div class="flex-1">
              <p class="font-medium">Berhasil</p>
              <p class="text-xs mt-0.5">{{ session('success') }}</p>
            </div>
          </div>
        @endif

        @if(session('error'))
          <div class="flex items-start gap-2">
            <div class="mt-0.5">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/></svg>
            </div>
            <div class="flex-1">
              <p class="font-medium">Terjadi kesalahan</p>
              <p class="text-xs mt-0.5">{{ session('error') }}</p>
            </div>
          </div>
        @endif
        <button type="button" class="text-emerald-700 hover:text-emerald-900 dark:text-emerald-200" @click="show = false">
          ✕
        </button>
    </div>
  @endif

</x-layouts.app>