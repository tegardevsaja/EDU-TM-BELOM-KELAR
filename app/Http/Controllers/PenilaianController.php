<?php

namespace App\Http\Controllers;
use App\Models\TemplatePenilaian;
use App\Models\Penilaian;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PenilaianController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $penilaians = Penilaian::with(['siswa', 'guru', 'template'])
            ->visibleFor($user)
            ->latest()
            ->paginate(10);

        return view('master.nilai.input-nilai.index', compact('penilaians'));
    }

    public function create($templateId)
    {
        // Ambil template beserta subfield (komponen)
        $template = TemplatePenilaian::findOrFail($templateId);
        $siswa = Siswa::all();
        return view('master.nilai.input-nilai.create', compact('template', 'siswa'));
    }

    public function store(Request $request, $templateId)
    {
        $template = TemplatePenilaian::findOrFail($templateId);

        $validated = $request->validate([
            'siswa_id' => 'required|integer|exists:siswas,id',
            'nilai' => 'required|array', // array dari semua input subfield
            'computed' => 'nullable|array', // total, avg, weighted_avg, grade (hidden fields)
            'visibility' => 'nullable|in:admin,all',
        ]);

        // Bentuk struktur nilai_detail sesuai yang diharapkan customize view
        $nilaiDetail = [
            'nilai' => $validated['nilai'],
            'computed' => $request->input('computed', []),
        ];

        // Cegah duplikasi: update jika sudah ada untuk (template_id, siswa_id)
        $activeYearId = TahunAjaran::where('aktif', true)->value('id') ?? TahunAjaran::latest('id')->value('id');
        Penilaian::updateOrCreate(
            [
                'template_id' => $template->id,
                'siswa_id' => $validated['siswa_id'],
            ],
            [
                'guru_id' => Auth::id(),
                'nilai_detail' => $nilaiDetail,
                'visibility' => $request->input('visibility', 'all'),
                'tanggal_input' => now(),
                'tahun_ajaran_id' => $activeYearId,
            ]
        );

        // Kembali ke halaman sebelumnya (customize atau form input) agar data terbaru langsung terlihat
        $redirect = $request->input('redirect');
        if ($redirect) {
            return redirect($redirect)->with('success', 'Nilai berhasil disimpan!');
        }
        return back()->with('success', 'Nilai berhasil disimpan!');
    }
}
