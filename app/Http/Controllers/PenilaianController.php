<?php

namespace App\Http\Controllers;
use App\Models\TemplatePenilaian;
use App\Models\Penilaian;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PenilaianController extends Controller
{
    private function mapJenisFromTemplate(string $name): string
    {
        $n = mb_strtolower($name);
        if (str_contains($n, 'ukk') || str_contains($n, 'dudi') || str_contains($n, 'uji')) {
            return 'Uji DUDI';
        }
        if (str_contains($n, 'pkl') || str_contains($n, 'prakerin')) {
            return 'PKL';
        }
        if (str_contains($n, 'program terpadu')) {
            return 'Program Terpadu';
        }
        if (str_contains($n, 'ta') || str_contains($n, 'tugas akhir')) {
            return 'TA';
        }
        return 'Try Out';
    }
    public function index(Request $request)
    {
        $user = Auth::user();
        // sorting params
        $sortBy = $request->query('sort_by', 'tanggal');
        $sortDir = strtolower($request->query('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        // map sort keys to columns
        $sortColumns = [
            'tanggal' => 'penilaian.tanggal_input',
            'jenis' => 'penilaian.jenis_penilaian',
            'siswa' => 'siswas.nama',
            'template' => 'template_penilaian.nama_template',
            // 'nilai' handled specially below (JSON fields)
        ];

        $query = Penilaian::with(['siswa', 'guru', 'template'])
            ->visibleFor($user)
            ->leftJoin('siswas', 'siswas.id', '=', 'penilaian.siswa_id')
            ->leftJoin('template_penilaian', 'template_penilaian.id', '=', 'penilaian.template_id')
            ->select('penilaian.*');

        $format = $request->query('format');
        if (in_array($format, ['TA', 'Uji DUDI'])) {
            $query->where('jenis_penilaian', $format);
        }

        // apply sorting
        if ($sortBy === 'nilai') {
            // unified numeric value across formats: TA.na, UKK/Prakerin.nilai_akhir, manual_bulk.nilai_utama
            $orderExpr = "COALESCE(\n                CAST(JSON_UNQUOTE(JSON_EXTRACT(penilaian.nilai_detail, '$.row.na')) AS DECIMAL(10,2)),\n                CAST(JSON_UNQUOTE(JSON_EXTRACT(penilaian.nilai_detail, '$.row.nilai_akhir')) AS DECIMAL(10,2)),\n                CAST(JSON_UNQUOTE(JSON_EXTRACT(penilaian.nilai_detail, '$.row.nilai_utama')) AS DECIMAL(10,2))\n            )";
            $query->orderByRaw($orderExpr . ' ' . $sortDir);
        } else {
            $orderColumn = $sortColumns[$sortBy] ?? $sortColumns['tanggal'];
            $query->orderBy($orderColumn, $sortDir);
        }
        // Stable tie-breaker to ensure pagination advances
        $query->orderBy('penilaian.id', $sortDir);

        $penilaians = $query->paginate(10)->appends([
            'format' => $format,
            'sort_by' => $sortBy,
            'sort_dir' => $sortDir,
        ]);

        return view('master.nilai.input-nilai.index', compact('penilaians', 'format'));
    }

    public function chooseTemplate()
    {
        $templates = TemplatePenilaian::visibleFor(Auth::user())->get();
        return view('master.nilai.input-nilai.choose-template', compact('templates'));
    }

    public function create(Request $request, $templateId)
    {
        // Ambil template beserta subfield (komponen)
        $template = TemplatePenilaian::findOrFail($templateId);

        // Filter siswa
        $query = Siswa::with(['kelas', 'jurusan', 'tahunAjaran'])->orderBy('nama');

        // Keluarkan siswa yang sudah memiliki penilaian untuk template ini pada tahun aktif
        $activeYearId = TahunAjaran::where('aktif', true)->value('id') ?? TahunAjaran::latest('id')->value('id');
        $gradedIds = Penilaian::where('template_id', $template->id)
            ->where('tahun_ajaran_id', $activeYearId)
            ->pluck('siswa_id');
        if ($gradedIds->count()) {
            $query->whereNotIn('id', $gradedIds);
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }
        if ($request->filled('tahun_ajaran_id')) {
            $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
        }
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('nama', 'like', "%{$q}%")
                    ->orWhere('nis', 'like', "%{$q}%");
            });
        }

        $siswa = $query->get();
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $tahunAjaran = TahunAjaran::orderBy('tahun_ajaran')->get();

        return view('master.nilai.input-nilai.create', compact('template', 'siswa', 'kelas', 'tahunAjaran'));
    }

    public function bulkCreate(Request $request, $templateId)
    {
        $template = TemplatePenilaian::findOrFail($templateId);

        $query = Siswa::with(['kelas', 'jurusan', 'tahunAjaran'])->orderBy('nama');

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }
        if ($request->filled('tahun_ajaran_id')) {
            $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
        }
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('nama', 'like', "%{$q}%")
                    ->orWhere('nis', 'like', "%{$q}%");
            });
        }

        $siswa = $query->get();
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $tahunAjaran = TahunAjaran::orderBy('tahun_ajaran')->get();

        return view('master.nilai.input-nilai.bulk-create', compact('template', 'siswa', 'kelas', 'tahunAjaran'));
    }

    public function bulkStore(Request $request, $templateId)
    {
        $template = TemplatePenilaian::findOrFail($templateId);

        $data = $request->validate([
            'nilai' => 'required|array', // [siswa_id => nilai_utama]
        ]);

        $activeYearId = TahunAjaran::where('aktif', true)->value('id') ?? TahunAjaran::latest('id')->value('id');

        foreach ($data['nilai'] as $siswaId => $nilaiUtama) {
            if ($nilaiUtama === null || $nilaiUtama === '') {
                continue;
            }

            if (!is_numeric($nilaiUtama)) {
                continue;
            }

            Penilaian::updateOrCreate(
                [
                    'template_id' => $template->id,
                    'siswa_id' => (int)$siswaId,
                ],
                [
                    'guru_id' => Auth::id(),
                    'jenis_penilaian' => $this->mapJenisFromTemplate($template->nama_template),
                    'nilai_detail' => [
                        'format' => 'manual_bulk',
                        'row' => [
                            'nilai_utama' => (float)$nilaiUtama,
                        ],
                    ],
                    'visibility' => 'all',
                    'tanggal_input' => now(),
                    'tahun_ajaran_id' => $activeYearId,
                ]
            );
        }

        return redirect()->route('master.nilai.index')->with('success', 'Nilai berhasil disimpan untuk banyak siswa.');
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

        // Hitung ringkasan numerik dan bentuk struktur nilai_detail konsisten
        $numericVals = [];
        foreach ($validated['nilai'] as $komp => $subs) {
            if (is_array($subs)) {
                foreach ($subs as $v) {
                    if ($v !== '' && $v !== null && is_numeric($v)) {
                        $numericVals[] = (float)$v;
                    }
                }
            } elseif ($subs !== '' && $subs !== null && is_numeric($subs)) {
                $numericVals[] = (float)$subs;
            }
        }
        $avg = count($numericVals) ? array_sum($numericVals) / count($numericVals) : null;

        $row = $validated['nilai'];
        if ($avg !== null) {
            $row['nilai_utama'] = (float)number_format($avg, 2, '.', '');
        }

        $nilaiDetail = [
            'format' => 'manual_bulk', // agar index mengenali 'nilai_utama'
            'row' => $row,
            'computed' => array_merge($request->input('computed', []), [
                'avg' => $avg,
            ]),
        ];

        // Cegah duplikasi: update jika sudah ada untuk (template_id, siswa_id)
        $activeYearId = TahunAjaran::where('aktif', true)->value('id') ?? TahunAjaran::latest('id')->value('id');
        try {
            $record = Penilaian::updateOrCreate(
                [
                    'template_id' => $template->id,
                    'siswa_id' => $validated['siswa_id'],
                ],
                [
                    'guru_id' => Auth::id(),
                    'jenis_penilaian' => $this->mapJenisFromTemplate($template->nama_template),
                    'nilai_detail' => $nilaiDetail,
                    'visibility' => $request->input('visibility', 'all'),
                    'tanggal_input' => now(),
                    'tahun_ajaran_id' => $activeYearId,
                ]
            );
            Log::info('Penilaian saved', [
                'id' => $record->id,
                'template_id' => $template->id,
                'siswa_id' => $validated['siswa_id'],
                'by' => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed saving penilaian', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withInput()->with('error', 'Gagal menyimpan nilai: '.$e->getMessage());
        }

        // Redirect kembali ke form create agar tidak kembali ke index via Referer
        $redirect = $request->input('redirect');
        if ($redirect) {
            return redirect($redirect)->with('success', 'Nilai berhasil disimpan!');
        }
        $prefix = in_array($request->segment(1), ['admin','guru','master']) ? $request->segment(1) : 'master';
        $routeName = $prefix.'.nilai.create';
        return redirect()->route($routeName, ['templateId' => $template->id])->with('success', 'Nilai berhasil disimpan!');
    }

    public function show($id)
    {
        $nilai = Penilaian::with(['siswa.kelas', 'siswa.jurusan', 'guru', 'template', 'tahunAjaran'])->findOrFail($id);
        return view('master.nilai.input-nilai.show', compact('nilai'));
    }

    public function edit($id)
    {
        $nilai = Penilaian::with(['siswa', 'guru', 'template'])->findOrFail($id);
        $detail = $nilai->nilai_detail['row'] ?? $nilai->nilai_detail ?? [];
        return view('master.nilai.input-nilai.edit', compact('nilai', 'detail'));
    }

    public function update(Request $request, $id)
    {
        $nilai = Penilaian::findOrFail($id);

        $validated = $request->validate([
            'visibility' => 'nullable|in:admin,all',
            'detail' => 'nullable|array',
        ]);

        $currentDetail = $nilai->nilai_detail['row'] ?? $nilai->nilai_detail ?? [];
        $incoming = $validated['detail'] ?? [];

        foreach ($incoming as $k => $v) {
            // keep original key casing if exists
            if (array_key_exists($k, $currentDetail)) {
                $currentDetail[$k] = $v;
            } else {
                $currentDetail[$k] = $v;
            }
        }

        $payload = $nilai->nilai_detail;
        if (is_array($payload) && array_key_exists('row', $payload)) {
            $payload['row'] = $currentDetail;
        } else {
            $payload = $currentDetail;
        }

        $nilai->nilai_detail = $payload;
        if (isset($validated['visibility'])) {
            $nilai->visibility = $validated['visibility'] === 'all' ? 'all' : 'admin';
        }
        $nilai->save();

        return redirect()->route('master.nilai.show', $nilai->id)->with('success', 'Nilai berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $nilai = Penilaian::findOrFail($id);
        $nilai->delete();
        return redirect()->route('master.nilai.index')->with('success', 'Nilai berhasil dihapus.');
    }
}
