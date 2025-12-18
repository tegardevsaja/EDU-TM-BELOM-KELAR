<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Jurusan;

class SiswaSearch extends Component
{
    use WithPagination;

    public $search = '';
    public $kelasId = '';
    public $jurusanId = '';
    
    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingKelasId()
    {
        $this->resetPage();
    }

    public function updatingJurusanId()
    {
        $this->resetPage();
    }

    // Method untuk clear search
    public function clearSearch()
    {
        $this->search = '';
        $this->resetPage();
    }

    public function applyFilters()
    {
        // Hanya reset halaman, Livewire akan merender ulang dengan filter terbaru
        $this->resetPage();
    }

    public function render()
    {
        $siswas = Siswa::with(['kelas', 'jurusan', 'tahunAjaran'])
            ->when($this->kelasId, function ($query) {
                $query->where('kelas_id', $this->kelasId);
            })
            ->when($this->jurusanId, function ($query) {
                $query->where('jurusan_id', $this->jurusanId);
            })
            ->when($this->search, function($query) {
                // Split search terms by space untuk multi-keyword search
                $searchTerms = explode(' ', $this->search);
                
                $query->where(function($q) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        if (!empty(trim($term))) {
                            $q->where(function($subQuery) use ($term) {
                                $subQuery->where('nama', 'like', '%' . $term . '%')
                                         ->orWhere('nis', 'like', '%' . $term . '%');
                            });
                        }
                    }
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $jurusanList = Jurusan::orderBy('nama_jurusan')->get();

        return view('livewire.siswa-search', compact('siswas', 'kelasList', 'jurusanList'));
    }
}