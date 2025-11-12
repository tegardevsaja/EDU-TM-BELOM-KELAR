<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jurusan;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class JurusanController extends Controller
{
    public function index(Request $request) : View
    {
        $q = trim((string) $request->query('q', ''));
        $jurusan = Jurusan::query()
            ->when($q !== '', function($query) use ($q) {
                $terms = preg_split('/\s+/', $q);
                $query->where(function($outer) use ($terms) {
                    foreach ($terms as $term) {
                        if ($term === '') continue;
                        $outer->where('nama_jurusan', 'like', "%{$term}%");
                    }
                });
            })
            ->latest()
            ->paginate(10)
            ->appends(['q' => $q]);

        return view('master.jurusan.index', compact('jurusan', 'q'));
    }

    public function create(): View
    {
        return view('master.jurusan.create'); 
    }


    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_jurusan' => 'required|string|max:100',
        ]);

        Jurusan::create([
            'nama_jurusan' => $request->nama_jurusan,
        ]);
        
        return redirect()->route('master.jurusan')->with(['success' => 'Data Berhasil Disimpan!']);

    }

    public function edit($id)
    {
        $jurusan = Jurusan::findOrFail($id);
        return view('master.jurusan.edit', compact('jurusan'));
        
    }

    public function update(Request $request, $id)
    {
        $jurusan = Jurusan::findOrFail($id);

        $validated = $request->validate([
            'nama_jurusan' => 'required|string|max:100',
        ]);

        $jurusan->update($validated);
        return redirect()->route('master.jurusan')->with(['success' => 'Data Berhasil Diperbarui!']);

    }

    public function destroy($id)
    {
        $jurusan = Jurusan::findOrFail($id);
        $jurusan->delete();

        return redirect()->route('master.jurusan')->with(['success' => 'Data Berhasil Dihapus!']);

    }
}
