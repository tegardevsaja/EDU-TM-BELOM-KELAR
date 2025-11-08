<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Siswa;

class SiswaSearch extends Component
{
    use WithPagination;

    public $search = '';
    
    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // Method untuk clear search
    public function clearSearch()
    {
        $this->search = '';
        $this->resetPage();
    }

    public function render()
    {
        $siswas = Siswa::with(['kelas', 'jurusan', 'tahunAjaran'])
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

        return view('livewire.siswa-search', compact('siswas'));
    }
}