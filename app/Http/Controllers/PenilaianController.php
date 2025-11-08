<?php

namespace App\Http\Controllers;
use App\Models\TemplatePenilaian;
use App\Models\Penilaian;
use Illuminate\Http\Request;

class PenilaianController extends Controller
{
    public function create($templateId)
    {
        // Ambil template beserta subfield (komponen)
        $template = TemplatePenilaian::findOrFail($templateId);

        return view('penilaian.create', compact('template'));
    }

    public function store(Request $request, $templateId)
    {
        $template = TemplatePenilaian::findOrFail($templateId);

        $validated = $request->validate([
            'siswa_id' => 'required|integer|exists:siswa,id',
            'nilai' => 'required|array', // array dari semua input subfield
        ]);

        // Simpan penilaian
        Penilaian::create([
            'template_id' => $template->id,
            'siswa_id'   => $validated['siswa_id'],
            'nilai'      => $validated['nilai'], // simpan dalam JSON
        ]);

        return redirect()->route('penilaian.index')->with('success', 'Nilai berhasil disimpan!');
    }
}
