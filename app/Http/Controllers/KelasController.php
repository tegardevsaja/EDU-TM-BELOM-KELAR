<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\User;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $kelas = Kelas::with(['jurusan', 'waliKelas'])
                        ->when($q !== '', function ($query) use ($q) {
                            $terms = preg_split('/\s+/', $q);
                            $query->where(function ($outer) use ($terms) {
                                foreach ($terms as $term) {
                                    if ($term === '') continue;
                                    $outer->where(function ($sub) use ($term) {
                                        $sub->where('nama_kelas', 'like', "%{$term}%")
                                            ->orWhereHas('jurusan', function($j) use ($term) {
                                                $j->where('nama_jurusan', 'like', "%{$term}%");
                                            })
                                            ->orWhereHas('waliKelas', function($w) use ($term) {
                                                $w->where('name', 'like', "%{$term}%");
                                            });
                                    });
                                }
                            });
                        })
                        ->latest()
                        ->paginate(10)
                        ->appends(['q' => $q]);

        return view('master.kelas.index', compact('kelas', 'q'));
    }

    public function customize(Request $request, $templateId)
    {
        $template = \App\Models\Template::findOrFail($templateId);

        $kelas = null;
        if ($request->filled('kelas_id') && $request->kelas_id !== 'none') {
            $kelas = \App\Models\Kelas::find($request->kelas_id);
        }

        $siswas = $kelas ? $kelas->siswas : [];

        return view('master.sertifikat.customize', compact('template', 'kelas', 'siswas'));
    }


    
    public function create(): View
    {
        $jurusans = Jurusan::all();

        $users = User::whereNotIn('id', Kelas::pluck('wali_kelas_id')->filter())->get();
        return view('master.kelas.create', compact('jurusans', 'users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kelas'     => 'required|string|max:50',
            'jurusan_id'     => 'required|exists:jurusans,id',
            'wali_kelas_id'  => 'nullable|exists:users,id',
        ]);

        Kelas::create($validated);

        return redirect()
            ->route('master.kelas')
            ->with('success', 'Kelas berhasil dibuat!');
    }


    public function edit($id): View
    {
        $kelas = Kelas::findOrFail($id);
        $jurusans = Jurusan::all();

        $users = User::whereNotIn('id', Kelas::where('id', '!=', $id)->pluck('wali_kelas_id')->filter())->get();

        return view('master.kelas.edit', compact('kelas', 'jurusans', 'users'));
    }


    public function update(Request $request, $id): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kelas'     => 'required|string|max:50',
            'jurusan_id'     => 'required|exists:jurusans,id',
            'wali_kelas_id'  => 'nullable|exists:users,id',
        ]);

        $kelas = Kelas::findOrFail($id);
        $kelas->update($validated);

        return redirect()
            ->route('master.kelas')
            ->with('success', 'Kelas berhasil diperbarui!');
    }


    public function destroy($id): RedirectResponse
    {
        $kelas = Kelas::findOrFail($id);
        $kelas->delete();

        return redirect()
            ->route('master.kelas')
            ->with('success', 'Kelas berhasil dihapus!');
    }
}
