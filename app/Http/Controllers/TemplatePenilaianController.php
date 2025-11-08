<?php

namespace App\Http\Controllers;

use App\Models\TemplatePenilaian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TemplatePenilaianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $template = TemplatePenilaian::with('user')->get();
        return view('master.nilai.index', compact('template'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('master.nilai.template.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_template' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'komponen' => 'nullable|array',
        ]);

        TemplatePenilaian::create([
            'nama_template' => $request->nama_template,
            'deskripsi' => $request->deskripsi,
            'komponen' => $request->komponen,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('master.penilaian')->with('success', 'Template berhasil dibuat');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $template = TemplatePenilaian::findOrFail($id);
        return view('master.nilai.template.edit', compact('template'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_template' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'komponen' => 'nullable|array',
        ]);

        $template = TemplatePenilaian::findOrFail($id);
        $template->update([
            'nama_template' => $request->nama_template,
            'deskripsi' => $request->deskripsi,
            'komponen' => $request->komponen,
        ]);

        return redirect()->route('master.penilaian')->with('success', 'Template berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $template = TemplatePenilaian::findOrFail($id);
        $template->delete();

        return back()->with('success', 'Template berhasil dihapus');
    }
}
