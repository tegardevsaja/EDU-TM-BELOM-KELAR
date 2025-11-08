<x-layouts.app :title="__('Master Admin Dashboard')">
  <div class="flex gap-2 mb-4">
    <div>
        <a href="{{ url('master/siswa/import') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-400 focus:outline-none transition">
            Import Excel
        </a>
    </div>
    <div>
         <a href="{{ route('master.siswa.template') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-400 focus:outline-none transition">
            Download Template Excel
        </a>
    </div>
    <div>
      <a href="{{ route('master.siswa.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-400 focus:outline-none transition">
        Tambah Siswa
      </a>
    </div>
  </div>  

  <!-- Livewire Component - sudah include search + table + pagination -->
  <livewire:siswa-search />

</x-layouts.app>