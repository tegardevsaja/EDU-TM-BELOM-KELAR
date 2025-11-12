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
  <div class="flex gap-2 mb-4">
    @can('siswa.import')
    <div>
        <a href="{{ url($routePrefix . '/siswa/import') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-400 focus:outline-none transition">
            Import Excel
        </a>
    </div>
    @endcan
    
    @can('siswa.template')
    <div>
         <a href="{{ route($routePrefix . '.siswa.template') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-400 focus:outline-none transition">
            Download Template Excel
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

</x-layouts.app>