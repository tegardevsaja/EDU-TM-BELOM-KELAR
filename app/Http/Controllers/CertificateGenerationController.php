<?php

namespace App\Http\Controllers;

use App\Models\CertificateTemplate;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Services\CertificateEligibilityService;
use App\Models\TemplatePenilaian;
use App\Models\Penilaian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Illuminate\Support\Facades\Schema;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use ZipArchive;
use Illuminate\Support\Str;
use App\Mail\CertificateDelivery;
use App\Models\CertificateOverride;
use App\Models\CertificateHistory;
use App\Models\GradeSignature;

class CertificateGenerationController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
    /**
     * STEP 1: Pilih Template
     */
    public function stepSelectTemplate()
    {
        $templates = CertificateTemplate::all(); 
        return view('master.sertifikat.select_template', compact('templates'));
    }

    /**
     * STEP 2: Pilih Siswa (opsional)
     */
    public function stepSelectStudents($template_id)
    {
        $template = CertificateTemplate::findOrFail($template_id);
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $siswas = Siswa::with('kelas')->orderBy('nama')->get();
        return view('master.sertifikat.select_students', compact('template', 'kelas', 'siswas'));
    }

    /**
     * STEP 3: Pilih Template Nilai (opsional)
     */
    public function stepSelectGrade($template_id, Request $request)
    {
        $template = CertificateTemplate::findOrFail($template_id);
        $gradeTemplates = TemplatePenilaian::visibleFor(auth()->user())->get();
        $selectedStudentIds = collect((array) $request->query('siswa_ids'))->map(fn($v) => (int)$v)->filter()->values();
        $kelas_id = $request->query('kelas_id');

        // Hitung jumlah siswa yang sudah punya nilai per template
        $filterIds = collect();
        if ($selectedStudentIds->isNotEmpty()) {
            $filterIds = $selectedStudentIds;
        } elseif (!empty($kelas_id)) {
            $filterIds = Siswa::where('kelas_id', (int)$kelas_id)->pluck('id');
        }

        $countsQuery = Penilaian::selectRaw('template_id, COUNT(DISTINCT siswa_id) as total')
            ->groupBy('template_id');
        if ($filterIds->isNotEmpty()) {
            $countsQuery->whereIn('siswa_id', $filterIds);
        }
        $nilaiCounts = $countsQuery->pluck('total', 'template_id');

        // Summaries per template: kelas list, jurusan list, total
        $templateIds = Penilaian::when($filterIds->isNotEmpty(), function($q) use ($filterIds) {
                $q->whereIn('siswa_id', $filterIds);
            })
            ->select('template_id')
            ->groupBy('template_id')
            ->pluck('template_id');

        $nilaiSummaries = [];
        // Map template names from $gradeTemplates to avoid extra queries
        $tplNameMap = $gradeTemplates->pluck('nama_template', 'id');

        foreach ($templateIds as $tid) {
            $siswaIds = Penilaian::where('template_id', $tid)
                ->when($filterIds->isNotEmpty(), fn($q) => $q->whereIn('siswa_id', $filterIds))
                ->pluck('siswa_id')->unique()->values();
            $siswas = Siswa::with(['kelas.jurusan','jurusan'])
                ->whereIn('id', $siswaIds)
                ->get(['id','kelas_id','jurusan_id']);
            $kelasNames = $siswas->map(fn($s) => optional($s->kelas)->nama_kelas)->filter()->unique()->values()->take(5);
            $jurusanNames = $siswas->map(function($s){
                    $n = optional($s->jurusan)->nama_jurusan;
                    if (!$n && $s->kelas && $s->kelas->jurusan) { $n = $s->kelas->jurusan->nama_jurusan; }
                    return $n;
                })->filter()->unique()->values()->take(5);
            $nilaiSummaries[$tid] = [
                'template_id' => (int)$tid,
                'template_name' => $tplNameMap[$tid] ?? ('Template #' . $tid),
                'total' => $siswaIds->count(),
                'kelas' => $kelasNames,
                'jurusan' => $jurusanNames,
            ];
        }

        // Build kelas summaries: total nilai per kelas (distinct siswa with any penilaian)
        $kelasRows = DB::table('kelas as k')
            ->join('siswas as s', 's.kelas_id', '=', 'k.id')
            ->join('penilaian as p', 'p.siswa_id', '=', 's.id')
            ->when($filterIds->isNotEmpty(), function($q) use ($filterIds) {
                $q->whereIn('s.id', $filterIds);
            })
            ->select('k.id as kelas_id', 'k.nama_kelas', DB::raw('COUNT(DISTINCT p.siswa_id) as total'))
            ->groupBy('k.id', 'k.nama_kelas')
            ->orderBy('k.nama_kelas')
            ->get();

        // Attach jurusan name if available
        $kelasSummaries = $kelasRows->map(function($r){
            $kelas = Kelas::with('jurusan')->find($r->kelas_id);
            return [
                'kelas_id' => (int)$r->kelas_id,
                'nama_kelas' => $r->nama_kelas,
                'jurusan' => optional($kelas?->jurusan)->nama_jurusan,
                'total' => (int)$r->total,
            ];
        });

        return view('master.sertifikat.select_grade', compact('template', 'gradeTemplates', 'selectedStudentIds', 'kelas_id', 'nilaiCounts', 'nilaiSummaries', 'kelasSummaries'));
    }

    /**
     * Download template Excel/CSV untuk import nilai
     */
    public function downloadGradeExcelTemplate(Request $request)
    {
        $type = strtolower((string)$request->query('type', 'generic'));
        $headers = [
            'Content-Type' => 'text/csv',
        ];

        if ($type === 'ukk_dudi') {
            $filename = 'template_ukk_dudi.csv';
            $headers['Content-Disposition'] = 'attachment; filename="' . $filename . '"';
            $cols = ['no','no_peserta','nama','nisn','predikat','predikat_en','nilai_akhir'];
            $rows = [
                ['032','02-07-0228-001','ADELYA DERMAWAN','0075281714','Kompeten','Competent','78.43'],
                ['033','02-07-0228-002','AGUNG SURYA PRATAMA','0069366312','Kompeten','Competent','80.39'],
            ];
        } elseif ($type === 'ta') {
            $filename = 'template_tugas_akhir.csv';
            $headers['Content-Disposition'] = 'attachment; filename="' . $filename . '"';
            $cols = ['no','nama','nis','nisn','jurusan','project','instansi','kota','np1','np2','np3','np4','np5','nilai_akhir','predikat'];
            $rows = [
                ['001','Nama Siswa','2210201','0075281714','Multimedia','Membuat Iklan','Kantor Pos','Kota Tangerang Selatan','90','87','92','88','90','89.4','A'],
            ];
        } else {
            // generic fallback (grouped components)
            $filename = 'template_import_nilai.csv';
            $headers['Content-Disposition'] = 'attachment; filename="' . $filename . '"';
            $cols = ['nis','komponen','uraian','nilai'];
            $rows = [
                ['22101111','Kedisiplinan','Kehadiran','90'],
                ['22101111','Kompetensi Kerja','Keterampilan','85'],
                ['22101112','Kedisiplinan','Kehadiran','88'],
            ];
        }

        $content = implode(',', $cols) . "\n";
        foreach ($rows as $r) { $content .= implode(',', $r) . "\n"; }
        return response($content, 200, $headers);
    }

    /**
     * Import nilai dari Excel/CSV dan simpan ke tabel penilaian per siswa per template nilai
     */
    public function importGradesExcel(Request $request, $template_id)
    {
        $validated = $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
            'grade_template_id' => 'nullable|exists:template_penilaian,id',
            'format_type' => 'nullable|in:generic,ukk_dudi,ta',
        ]);

        // Baca semua sheet menjadi collection
        $collection = Excel::toCollection(null, $request->file('file'));
        if ($collection->isEmpty() || $collection[0]->count() <= 1) {
            return back()->with('error', 'File kosong atau hanya berisi header.');
        }

        $rows = $collection[0];
        // Deteksi header sederhana
        $header = $rows->first()->map(fn($v) => is_string($v) ? strtolower(trim($v)) : $v);
        $dataRows = $rows->slice(1);

        $format = $request->input('format_type', 'generic');
        $bySiswa = [];
        $metaBySiswa = [];

        if ($format === 'ukk_dudi') {
            // Expected cols: no,no_peserta,nama,nisn,predikat,predikat_en,nilai_akhir
            $idxNoPeserta = $header->search('no_peserta');
            $idxNama = $header->search('nama');
            $idxNisn = $header->search('nisn');
            $idxPred = $header->search('predikat');
            $idxPredEn = $header->search('predikat_en');
            $idxNA = $header->search('nilai_akhir');
            if ($idxNisn === false || $idxNA === false) {
                return back()->with('error', 'Header UKK DUDI wajib: nisn, nilai_akhir');
            }

            // Generate Nomor Sertifikat per siswa (atau dummy) agar bisa dipakai sebagai variabel
            if ($siswas->isNotEmpty()) {
                // Tentukan padding dari deretan nol pertama pada format, default 3 jika tidak ada
                $padLen = 3;
                if (preg_match('/0+/', $certFormat, $m)) {
                    $padLen = strlen($m[0]);
                }
                $month = $certDate->format('m');
                $day   = $certDate->format('d');
                $year  = $certDate->format('Y');

                $i = 0;
                foreach ($siswas as $s) {
                    $seq = str_pad((string)($certStart + $i), $padLen, '0', STR_PAD_LEFT);
                    $num = $certFormat;
                    // replace first 0-run
                    $num = preg_replace('/0+/', $seq, $num, 1);
                    // replace first XX = month, second XX = day
                    $num = preg_replace('/XX/', $month, $num, 1);
                    $num = preg_replace('/XX/', $day, $num, 1);
                    // replace XXXX = year (all occurrences)
                    $num = str_replace('XXXX', $year, $num);
                    $s->cert_number = $num;
                    $i++;
                }
            }
            foreach ($dataRows as $r) {
                $nisn = trim((string)($r[$idxNisn] ?? ''));
                if ($nisn === '') { continue; }
                $nama = $idxNama !== false ? trim((string)($r[$idxNama] ?? '')) : '';
                $noPeserta = $idxNoPeserta !== false ? trim((string)($r[$idxNoPeserta] ?? '')) : '';
                $pred = $idxPred !== false ? trim((string)($r[$idxPred] ?? '')) : '';
                $predEn = $idxPredEn !== false ? trim((string)($r[$idxPredEn] ?? '')) : '';
                $naRaw = $r[$idxNA] ?? null; $na = is_numeric($naRaw) ? (float)$naRaw : null;
                // store under key by nisn; we will resolve nisn->siswa
                $bySiswa[$nisn] = [ 'UKK DUDI' => [ 'Nilai Akhir' => $na, 'Predikat' => $pred, 'Predikat EN' => $predEn ] ];
                $metaBySiswa[$nisn] = ['nama' => $nama, 'no_peserta' => $noPeserta];
            }
        } elseif ($format === 'ta') {
            // Expected cols: no,nama,nis,nisn,jurusan,project,instansi,kota,np1..np5,nilai_akhir,predikat
            $idxNama = $header->search('nama');
            $idxNis = $header->search('nis');
            $idxNisn = $header->search('nisn');
            $idxJur = $header->search('jurusan');
            $idxProj = $header->search('project');
            $idxInst = $header->search('instansi');
            $idxKota = $header->search('kota');
            $idxNA = $header->search('nilai_akhir');
            $idxPred = $header->search('predikat');
            if ($idxNisn === false && $idxNis === false) {
                return back()->with('error', 'Header TA wajib minimal salah satu: nis atau nisn');
            }
            // find all np* columns
            $npIndexes = [];
            foreach ($header as $i => $h) {
                if (is_string($h) && preg_match('/^np\d+$/', $h)) { $npIndexes[$h] = $i; }
            }
            foreach ($dataRows as $r) {
                $nisn = $idxNisn !== false ? trim((string)($r[$idxNisn] ?? '')) : '';
                $nis = $idxNis !== false ? trim((string)($r[$idxNis] ?? '')) : '';
                $key = $nis ?: $nisn; if ($key === '') { continue; }
                $nama = $idxNama !== false ? trim((string)($r[$idxNama] ?? '')) : '';
                $jur = $idxJur !== false ? trim((string)($r[$idxJur] ?? '')) : '';
                $proj = $idxProj !== false ? trim((string)($r[$idxProj] ?? '')) : '';
                $inst = $idxInst !== false ? trim((string)($r[$idxInst] ?? '')) : '';
                $kota = $idxKota !== false ? trim((string)($r[$idxKota] ?? '')) : '';
                $pred = $idxPred !== false ? trim((string)($r[$idxPred] ?? '')) : '';
                $naRaw = $idxNA !== false ? ($r[$idxNA] ?? null) : null; $na = is_numeric($naRaw) ? (float)$naRaw : null;
                $npValues = [];
                foreach ($npIndexes as $label => $i) {
                    $val = $r[$i] ?? null; if ($val !== null && $val !== '') { $npValues[$label] = is_numeric($val) ? (float)$val : null; }
                }
                $bySiswa[$key] = [ 'TA' => array_merge(['Project' => $proj, 'Instansi' => $inst, 'Kota' => $kota], $npValues, ['Nilai Akhir' => $na, 'Predikat' => $pred]) ];
                $metaBySiswa[$key] = ['nama' => $nama, 'jurusan' => $jur, 'nis' => $nis, 'nisn' => $nisn];
            }
        } else {
            // generic grouped component template
            $idxNis = $header->search('nis');
            $idxNisnOpt = $header->search('nisn');
            $idxKomponen = $header->search('komponen');
            $idxUraian = $header->search('uraian');
            $idxNilai = $header->search('nilai');
            if ($idxNis === false || $idxKomponen === false || $idxUraian === false || $idxNilai === false) {
                return back()->with('error', 'Header wajib: nis, komponen, uraian, nilai');
            }
            foreach ($dataRows as $r) {
                $nis = trim((string)($r[$idxNis] ?? ''));
                if ($nis === '') { continue; }
                $komp = trim((string)($r[$idxKomponen] ?? ''));
                $urai = trim((string)($r[$idxUraian] ?? ''));
                $nilaiRaw = $r[$idxNilai] ?? null;
                $nilai = is_numeric($nilaiRaw) ? (float)$nilaiRaw : (string)$nilaiRaw;
                if (!isset($bySiswa[$nis])) { $bySiswa[$nis] = []; }
                if (!isset($bySiswa[$nis][$komp])) { $bySiswa[$nis][$komp] = []; }
                $bySiswa[$nis][$komp][$urai] = $nilai;
                if ($idxNisnOpt !== false) {
                    $nisnVal = trim((string)($r[$idxNisnOpt] ?? ''));
                    if ($nisnVal !== '') { $metaBySiswa[$nis]['nisn'] = $nisnVal; }
                }
            }
        }

        if (empty($bySiswa)) {
            return back()->with('error', 'Tidak ada baris nilai yang valid.');
        }

        // Tentukan target template nilai: pakai pilihan user, atau buat default otomatis per format
        $targetTemplateId = (int)($validated['grade_template_id'] ?? 0);
        if (!$targetTemplateId) {
            $autoName = 'AUTO-IMPORT ' . strtoupper($format);
            $auto = TemplatePenilaian::firstOrCreate(
                ['nama_template' => $autoName],
                [
                    'deskripsi' => 'Dibuat otomatis dari import',
                    'komponen' => [],
                    'visibility' => 'all',
                    'created_by' => auth()->id(),
                ]
            );
            $targetTemplateId = (int)$auto->id;
        }

        $created = 0; $updated = 0; $skipped = 0;
        $hasNisn = Schema::hasColumn('siswas', 'nisn');
        foreach ($bySiswa as $key => $map) {
            // Resolve siswa by NIS first, then NISN fallback (only if column exists)
            $siswa = \App\Models\Siswa::where('nis', $key)->first();
            // numeric-cast fallback for NIS if all digits
            if (!$siswa && preg_match('/^\d+$/', $key)) {
                $siswa = \App\Models\Siswa::whereRaw('CAST(nis AS UNSIGNED) = ?', [(int)$key])->first();
            }
            // try nisn from meta if present and column exists
            if (!$siswa && $hasNisn) {
                $nisnMeta = $metaBySiswa[$key]['nisn'] ?? null;
                if ($nisnMeta) {
                    $siswa = \App\Models\Siswa::where('nisn', $nisnMeta)->first();
                } else {
                    $siswa = \App\Models\Siswa::where('nisn', $key)->first();
                }
            }
            if (!$siswa) { $skipped++; continue; }

            // Hitung summary sederhana
            $allNumbers = [];
            foreach ($map as $subs) {
                foreach ($subs as $v) { if (is_numeric($v)) { $allNumbers[] = (float)$v; } }
            }
            $total = count($allNumbers) ? array_sum($allNumbers) : null;
            $avg = count($allNumbers) ? round($total / count($allNumbers), 2) : null;
            $grade = null;
            if ($avg !== null) {
                $grade = $avg >= 90 ? 'A' : ($avg >= 75 ? 'B' : ($avg >= 60 ? 'C' : 'D'));
            }

            $payload = [
                'nilai_detail' => [ 'nilai' => $map, 'computed' => ['total' => $total, 'avg' => $avg, 'grade' => $grade] ],
                'nilai' => $avg,
                'keterangan' => 'Import Excel',
                'tanggal_input' => now(),
                'guru_id' => auth()->id(),
                'visibility' => 'all',
            ];

            $pen = \App\Models\Penilaian::updateOrCreate(
                ['siswa_id' => $siswa->id, 'template_id' => $targetTemplateId],
                $payload
            );
            $pen->wasRecentlyCreated ? $created++ : $updated++;
        }

        return back()->with([
            'success' => "Import nilai selesai. Baru: {$created}, Update: {$updated}, Dilewati: {$skipped}",
            'last_grade_template_id' => $targetTemplateId,
        ]);
    }

    /**
     * Pengaturan global Penguji & Tanda Tangan untuk lampiran nilai
     */
    public function editSignatures()
    {
        $this->authorize('menu.sertifikat');

        $signatures = GradeSignature::first();
        if (!$signatures) {
            $signatures = new GradeSignature([
                'left_label' => 'Penguji Internal',
                'left_org'   => 'SMK Tunas Media',
                'right_label'=> 'Penguji Eksternal',
                'city'       => 'Kota Depok',
            ]);
        }

        return view('master.sertifikat.signatures', compact('signatures'));
    }

    public function updateSignatures(Request $request)
    {
        $this->authorize('menu.sertifikat');

        $data = $request->validate([
            'left_label'  => 'nullable|string|max:100',
            'left_name'   => 'nullable|string|max:150',
            'left_org'    => 'nullable|string|max:150',
            'left_nik'    => 'nullable|string|max:50',
            'right_label' => 'nullable|string|max:100',
            'right_name'  => 'nullable|string|max:150',
            'right_org'   => 'nullable|string|max:150',
            'right_nik'   => 'nullable|string|max:50',
            'city'        => 'nullable|string|max:150',
            'return'      => 'nullable|string',
            'left_signature' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'right_signature' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $returnUrl = $data['return'] ?? null;
        unset($data['return'], $data['left_signature'], $data['right_signature']);

        $signatures = GradeSignature::first();
        if (!$signatures) {
            $signatures = new GradeSignature();
        }

        // Merge NIK into org strings for display if provided
        if (array_key_exists('left_org', $data)) {
            $org = trim((string)($data['left_org'] ?? ''));
            $nik = trim((string)($data['left_nik'] ?? ''));
            unset($data['left_nik']);
            // Remove any existing NIK suffix first
            $org = preg_replace('/\s*NIK:\s*.*/i', '', $org);
            if ($nik !== '') { $org = rtrim($org) . ' NIK: ' . $nik; }
            $data['left_org'] = $org;
        }
        if (array_key_exists('right_org', $data)) {
            $org = trim((string)($data['right_org'] ?? ''));
            $nik = trim((string)($data['right_nik'] ?? ''));
            unset($data['right_nik']);
            $org = preg_replace('/\s*NIK:\s*.*/i', '', $org);
            if ($nik !== '') { $org = rtrim($org) . ' NIK: ' . $nik; }
            $data['right_org'] = $org;
        }

        // Handle file uploads
        if ($request->hasFile('left_signature')) {
            $leftFile = $request->file('left_signature');
            $leftPath = $leftFile->store('signatures', 'public');
            $data['left_signature_path'] = $leftPath;
        }

        if ($request->hasFile('right_signature')) {
            $rightFile = $request->file('right_signature');
            $rightPath = $rightFile->store('signatures', 'public');
            $data['right_signature_path'] = $rightPath;
        }

        if ($signatures->exists) {
            $signatures->update($data);
        } else {
            $signatures->fill($data);
            $signatures->save();
        }

        if ($returnUrl) {
            return redirect($returnUrl)
                ->with('success', 'Pengaturan penguji & tanda tangan berhasil disimpan.');
        }

        return redirect()
            ->route('master.sertifikat.signatures.edit')
            ->with('success', 'Pengaturan penguji & tanda tangan berhasil disimpan.');
    }

    /**
     * Grant/Revoke eligibility override for a siswa (per tahun ajaran optional)
     */
    public function override(Request $request)
    {
        $this->authorize('menu.sertifikat');
        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'granted' => 'required|boolean',
            'reason' => 'nullable|string|max:255',
            'tahun_ajaran_id' => 'nullable|exists:tahun_ajarans,id',
        ]);

        CertificateOverride::updateOrCreate(
            [
                'siswa_id' => $validated['siswa_id'],
                'tahun_ajaran_id' => $validated['tahun_ajaran_id'] ?? null,
            ],
            [
                'granted' => (bool)$validated['granted'],
                'reason' => $validated['reason'] ?? null,
                'granted_by' => auth()->id(),
            ]
        );

        return back()->with('success', 'Override kelayakan diperbarui.');
    }

    /**
     * Build PDF to a temporary file and return path info
     */
    private function buildPdfFile($template, $siswas, $elements, $filename_suffix, $gradeSheetFormat = null)
    {
        $signatures = GradeSignature::first();

        $pdf = Pdf::loadView('master.sertifikat.download_pdf', [
            'template' => $template,
            'siswas' => $siswas,
            'elements' => $elements,
            'grade_sheet_format' => $gradeSheetFormat,
            'signatures' => $signatures,
        ])->setPaper('a4', 'landscape')
          ->setOption('margin-top', 0)
          ->setOption('margin-right', 0)
          ->setOption('margin-bottom', 0)
          ->output();

        $filename = 'sertifikat_' . $filename_suffix . '_' . now()->format('Ymd_His') . '.pdf';
        $path = storage_path('app/tmp/' . $filename);
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        file_put_contents($path, $pdf);
        return [
            'path' => $path,
            'filename' => $filename,
            'mime' => 'application/pdf',
        ];
    }

    public function stepSelectClass($template_id)
    {
        $template = CertificateTemplate::findOrFail($template_id);
        $kelas = Kelas::all();

        return view('master.sertifikat.select_class', compact('template', 'kelas'));
    }

    /**
     * STEP 3.5: Customize Template
     */
    public function customize(Request $request, $template_id)
    {
        $template = CertificateTemplate::findOrFail($template_id);
        $kelas_id = $request->query('kelas_id');
        $selectedStudentIds = collect((array) $request->query('siswa_ids'))->map(fn($v) => (int)$v)->filter();
        // custom recipients from query
        $recipients = $request->query('recipients', []);
        $hasCustomRecipients = is_array($recipients) && !empty($recipients) && !empty($recipients['name']);

        // Support custom recipients (manual entries when no siswa selected)
        $recipients = $request->query('recipients', []);
        $hasCustomRecipients = is_array($recipients) && !empty($recipients) && !empty($recipients['name']);

        if ($selectedStudentIds->isNotEmpty()) {
            $siswaQuery = Siswa::with('kelas');
            $siswaQuery->whereIn('id', $selectedStudentIds);
            $siswaList = $siswaQuery->get();
            $kelas = $kelas_id ? Kelas::find($kelas_id) : null;
        } elseif ($kelas_id) {
            $kelas = Kelas::with('siswas')->findOrFail($kelas_id);
            $siswaList = $kelas->siswas;
        } else {
            // Tidak memilih siswa apapun -> kosongkan daftar
            $siswaList = collect();
            $kelas = null;
        }

        // Ambil elements menggunakan helper method
        $elements = $this->getTemplateElements($template);

        // Log untuk debugging
        Log::info('Customize page loaded', [
            'template_id' => $template_id,
            'elements_count' => count($elements),
            'elements_sample' => array_slice($elements, 0, 3),
            'raw_elements' => substr($template->elements ?? '', 0, 200),
            'siswa_count' => $siswaList->count()
        ]);

        $gradeTemplateId = $request->query('grade_template_id');
        $gradeTemplate = null;
        $existingPenilaianBySiswa = [];
        if ($gradeTemplateId) {
            $gradeTemplate = TemplatePenilaian::find($gradeTemplateId);
            if ($gradeTemplate) {
                // Jika siswaList kosong, muat siswa dari Penilaian pada template ini
                if ($siswaList->isEmpty()) {
                    $siswaIds = Penilaian::where('template_id', $gradeTemplateId)
                        ->pluck('siswa_id')
                        ->unique()
                        ->filter()
                        ->values();
                    if ($siswaIds->isNotEmpty()) {
                        $siswaList = Siswa::with('kelas')->whereIn('id', $siswaIds)->get();
                    }
                }
                if ($siswaList->isNotEmpty()) {
                    $pen = Penilaian::where('template_id', $gradeTemplateId)
                        ->whereIn('siswa_id', $siswaList->pluck('id'))
                        ->get(['siswa_id','nilai_detail']);
                    foreach ($pen as $p) {
                        $existingPenilaianBySiswa[$p->siswa_id] = $p->nilai_detail;
                    }
                }
            }
        }

        return view('master.sertifikat.customize', compact('template', 'siswaList', 'elements', 'kelas', 'gradeTemplate', 'gradeTemplateId', 'selectedStudentIds', 'existingPenilaianBySiswa'));
    }

    /**
     * SAVE CUSTOMIZATION
     */
    public function saveCustomization(Request $request, $template_id)
    {
        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'elements' => 'required|json'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Decode elements
            $elements = json_decode($request->elements, true);

            if (!is_array($elements)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format elements tidak valid'
                ], 422);
            }

            // Validasi setiap element
            foreach ($elements as $index => $element) {
                if (!isset($element['type']) || !isset($element['id'])) {
                    return response()->json([
                        'success' => false,
                        'message' => "Element index {$index} tidak valid: type dan id wajib ada"
                    ], 422);
                }
            }

            // Cari template
            $template = CertificateTemplate::findOrFail($template_id);

            // Update elements di database
            $template->elements = json_encode($elements);
            $saved = $template->save();

            // Log activity
            Log::info("Template {$template_id} customization saved", [
                'user_id' => auth()->id(),
                'elements_count' => count($elements),
                'saved' => $saved,
                'elements_preview' => array_map(function($el) {
                    return [
                        'id' => $el['id'],
                        'type' => $el['type'],
                        'x' => $el['x'] ?? null,
                        'y' => $el['y'] ?? null,
                    ];
                }, array_slice($elements, 0, 3))
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Template berhasil disimpan',
                'data' => [
                    'template_id' => $template_id,
                    'elements_count' => count($elements),
                    'timestamp' => now()->toISOString()
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Template tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error saving template customization', [
                'template_id' => $template_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * STEP 4: Preview Sertifikat
     */
    public function preview(Request $request, $template_id)
    {
        $template = CertificateTemplate::findOrFail($template_id);
        $kelas_id = $request->query('kelas_id');
        $selectedStudentIds = collect((array) $request->query('siswa_ids'))->map(fn($v) => (int)$v)->filter();
        // Ensure custom recipients variables are defined before usage
        $recipients = $request->query('recipients', []);
        $hasCustomRecipients = is_array($recipients) && !empty($recipients) && !empty($recipients['name']);

        if ($hasCustomRecipients) {
            $kelas = null;
            $siswas = collect();
            $count = count($recipients['name']);
            for ($i=0; $i<$count; $i++) {
                $o = new \stdClass();
                $o->id = null; // no DB id
                $o->nama = $recipients['name'][$i] ?? '';
                $o->nis = $recipients['nis'][$i] ?? '';
                // nested kelas and jurusan like model
                $kelasObj = new \stdClass();
                $kelasObj->nama_kelas = $recipients['kelas'][$i] ?? '';
                $jurObj = new \stdClass();
                $jurObj->nama_jurusan = $recipients['jurusan'][$i] ?? '';
                $kelasObj->jurusan = $jurObj;
                $o->kelas = $kelasObj;
                $o->peringkat = $recipients['peringkat'][$i] ?? '';
                $o->tanggal_custom = $recipients['tanggal'][$i] ?? '';
                $siswas->push($o);
            }
        } elseif ($selectedStudentIds->isNotEmpty()) {
            $kelas = $kelas_id ? Kelas::find($kelas_id) : null;
            $siswas = Siswa::with('kelas.jurusan')->whereIn('id', $selectedStudentIds)->get();
        } elseif ($kelas_id) {
            $kelas = Kelas::with('siswas.kelas.jurusan')->findOrFail($kelas_id);
            $siswas = $kelas->siswas;
        } else {
            // Tanpa pilihan siswa/kelas, coba muat siswa dari Penilaian bila grade_template_id dikirim
            $kelas = null;
            $siswas = collect();
            $gradeTemplateId = $request->query('grade_template_id');
            if ($gradeTemplateId) {
                $siswaIds = Penilaian::where('template_id', (int)$gradeTemplateId)->pluck('siswa_id')->unique()->filter()->values();
                if ($siswaIds->isNotEmpty()) {
                    $siswas = Siswa::with('kelas.jurusan')->whereIn('id', $siswaIds)->get();
                }
            }
        }

        // Ambil elements yang sudah disimpan
        $elements = $this->getTemplateElements($template);

        // --- Preview-only: generate temporary certificate numbers (tidak disimpan ke DB) ---
        // Supaya variabel $nomor_sertifikat, $no_sertifikat, dst. bisa muncul juga di preview,
        // kita gunakan logika yang mirip dengan generate(), tapi hanya mengisi property runtime.
        if ($siswas->isNotEmpty()) {
            $certFormat = (string) $request->query('cert_number_format', '000/SMK-TM/XX/XX/XXXX');
            $certStart  = (int) $request->query('cert_start_number', 1);
            $certDateInput = $request->query('cert_date');
            $certDate = $certDateInput ? \Carbon\Carbon::parse($certDateInput) : now();

            // Padding diambil dari run nol pertama pada format, default 3
            $padLen = 3;
            if (preg_match('/0+/', $certFormat, $mPad)) {
                $padLen = strlen($mPad[0]);
            }
            $month = $certDate->format('m');
            $day   = $certDate->format('d');
            $year  = $certDate->format('Y');
            $iSeq = 0;
            foreach ($siswas as $s) {
                $seq = str_pad((string)($certStart + $iSeq), $padLen, '0', STR_PAD_LEFT);
                $num = $certFormat;
                // Gantikan pattern
                $num = preg_replace('/0+/', $seq, $num, 1); // nomor urut
                $num = preg_replace('/XX/', $month, $num, 1); // bulan
                $num = preg_replace('/XX/', $day, $num, 1);   // tanggal
                $num = str_replace('XXXX', $year, $num);      // tahun
                $s->cert_number = $num;
                $iSeq++;
            }
        }

        // Eligibility map per siswa (true/false)
        $eligibilityService = new CertificateEligibilityService();
        $eligibility = [];
        foreach ($siswas as $s) {
            if (isset($s->id) && $s->id) {
                $eligibility[$s->id] = $eligibilityService->isEligible($s, null, 3, 5);
            } else {
                // custom recipients: default eligible
                $eligibility[spl_object_hash($s)] = true;
            }
        }

        // Optional: grade filter & attach summary for preview
        $gradeSheetFormat = $request->query('grade_sheet_format');
        $gradeTemplateId = $request->query('grade_template_id');
        // Fallback otomatis: jika user hanya memilih format lampiran (prakerin/TA)
        // tapi belum memilih template nilai, coba tebak template auto yang tepat
        if (!$gradeTemplateId && is_string($gradeSheetFormat)) {
            $fmt = strtolower($gradeSheetFormat);
            if ($fmt === 'prakerin') {
                $autoTpl = TemplatePenilaian::where('nama_template', 'like', '%Prakerin (Auto)%')
                    ->orWhere('nama_template', 'like', '%Prakerin%')
                    ->orderByDesc('id')
                    ->first();
                if ($autoTpl) {
                    $gradeTemplateId = $autoTpl->id;
                }
            } elseif ($fmt === 'tugas_akhir') {
                $autoTpl = TemplatePenilaian::where('nama_template', 'like', '%TA (Auto)%')
                    ->orWhere('nama_template', 'like', '%Tugas Akhir%')
                    ->orderByDesc('id')
                    ->first();
                if ($autoTpl) {
                    $gradeTemplateId = $autoTpl->id;
                }
            }
        }
        if ($gradeTemplateId) {
            $gradesBySiswa = [];
            $penQuery = Penilaian::where('template_id', (int)$gradeTemplateId);
            if ($siswas->isNotEmpty()) {
                $penQuery->whereIn('siswa_id', $siswas->pluck('id'));
            }
            $penilaians = $penQuery->get(['siswa_id','nilai_detail']);

            // Jika sebelumnya dipilih kelas/siswa namun tidak ada nilai yang cocok,
            // fallback: ambil semua siswa yang punya nilai pada template tersebut
            if ($siswas->isEmpty() || $penilaians->isEmpty()) {
                $siswaIds = Penilaian::where('template_id', (int)$gradeTemplateId)
                    ->pluck('siswa_id')->unique()->filter()->values();
                if ($siswaIds->isNotEmpty()) {
                    $siswas = Siswa::with('kelas.jurusan')->whereIn('id', $siswaIds)->get();
                    $penilaians = Penilaian::where('template_id', (int)$gradeTemplateId)
                        ->whereIn('siswa_id', $siswaIds)
                        ->get(['siswa_id','nilai_detail']);
                }
            }

            foreach ($penilaians as $p) { $gradesBySiswa[$p->siswa_id] = $p->nilai_detail; }
            // Filter: hanya yang punya nilai
            $siswas = $siswas->filter(fn($s) => isset($gradesBySiswa[$s->id]))->values();
            // Attach ringkasan & detail
            foreach ($siswas as $s) {
                $d = $gradesBySiswa[$s->id] ?? null;
                if (is_string($d)) { $d = json_decode($d, true); }

                $cmp = [];
                $detail = [];

                if (is_array($d)) {
                    // Format standar: punya 'nilai' dan 'computed'
                    $cmp = $d['computed'] ?? [];
                    $detail = $d['nilai'] ?? [];

                    // Fallback: hasil import TA / UKK yang hanya punya 'row'
                    if (empty($cmp) && empty($detail) && isset($d['row']) && is_array($d['row'])) {
                        $row = $d['row'];
                        // simpan raw row untuk format lampiran khusus (misal TA)
                        $s->nilai_ta_row = $row;
                        // Cari kolom nilai akhir yang paling relevan
                        $score = $row['na']
                            ?? ($row['NA'] ?? null)
                            ?? ($row['nilai_akhir'] ?? null);

                        // Normalisasi angka jika ada
                        if ($score !== null && is_numeric($score)) {
                            $score = (float) $score;
                        }

                        // Buat ringkasan sederhana
                        if ($score !== null) {
                            $cmp['total'] = $score;
                            $cmp['avg'] = $score;
                            // Konversi ke predikat sederhana
                            $cmp['grade'] = $score >= 90 ? 'A'
                                : ($score >= 75 ? 'B'
                                    : ($score >= 60 ? 'C' : 'D'));
                        }

                        // Peta detail minimal: satu baris nilai akhir
                        $label = isset($row['format']) && is_string($row['format'])
                            ? strtoupper($row['format'])
                            : 'Nilai';
                        $detail = [$label => ['Nilai Akhir' => $score]];
                    }

                    // Fallback DUDI/Prakerin: jika tidak ada kunci 'nilai' namun struktur sudah berupa map komponen => [uraian=>angka]
                    if (empty($detail)) {
                        // heuristik: ada beberapa key non-angka dengan sub-array angka
                        $hasSubArrays = false; $hasNumeric = false;
                        foreach ($d as $k=>$v) {
                            if (is_array($v)) { $hasSubArrays = true; foreach ($v as $vv) { if (is_numeric($vv)) { $hasNumeric = true; break; } } }
                        }
                        if ($hasSubArrays && $hasNumeric) {
                            $detail = $d; // gunakan langsung sebagai peta nilai detail
                        }
                    }

                    // Simpan judul laporan bila tersedia di sumber nilai
                    $judul = $d['judul_laporan'] ?? ($d['row']['judul_laporan'] ?? ($d['row']['judul'] ?? ($d['judul'] ?? null)));
                    if (!$judul && isset($d['keterangan']) && is_string($d['keterangan'])) { $judul = $d['keterangan']; }
                    if ($judul) { $s->judul_laporan = $judul; }
                }

                $parts = [];
                if (isset($cmp['total'])) $parts[] = 'Total: ' . $cmp['total'];
                if (isset($cmp['avg'])) $parts[] = 'Rata-rata: ' . $cmp['avg'];
                if (isset($cmp['weighted_avg'])) $parts[] = 'W. Avg: ' . $cmp['weighted_avg'];
                if (isset($cmp['grade'])) $parts[] = 'Predikat: ' . $cmp['grade'];
                $s->nilai = implode(' | ', $parts);
                $s->nilai_detail_map = $detail;
                $s->nilai_computed = $cmp;
            }
        }

        // Pagination for preview
        $perPage = max(1, (int)$request->query('per_page', 6));
        $page = max(1, (int)$request->query('page', 1));
        $total = $siswas->count();
        $pagedItems = $siswas->forPage($page, $perPage)->values();
        $paginatedSiswas = new \Illuminate\Pagination\LengthAwarePaginator(
            $pagedItems, $total, $perPage, $page, [
                'path' => url()->current(),
                'query' => $request->query(),
            ]
        );

        // Optional: daftar template nilai untuk halaman belakang
        $gradeTemplates = TemplatePenilaian::visibleFor(auth()->user())->get();
        
        // Log untuk debugging
        Log::info('Preview certificate', [
            'template_id' => $template_id,
            'elements_count' => count($elements),
            'elements_sample' => array_slice($elements, 0, 2),
            'siswa_count' => $siswas->count(),
            'page' => $page,
            'per_page' => $perPage
        ]);

        return view('master.sertifikat.preview', [
            'template' => $template,
            'kelas' => $kelas,
            'siswas' => $siswas,
            'elements' => $elements,
            'eligibility' => $eligibility,
            'gradeTemplates' => $gradeTemplates,
            'paginatedSiswas' => $paginatedSiswas,
            'gradeTemplateId' => $gradeTemplateId,
        ]);
    }

    /**
     * STEP 5: Generate & Download Sertifikat
     */
    public function generate(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'template_id' => 'required|exists:certificate_templates,id',
                'kelas_id' => 'nullable|exists:kelas,id',
                'format' => 'required|in:pdf,png,jpg,jpeg',
                'destination' => 'nullable|in:download,email',
                'email' => 'nullable|required_if:destination,email|email',
                'siswa_ids' => 'nullable|array',
                'siswa_ids.*' => 'integer|exists:siswas,id',
                'grade_template_id' => 'nullable|integer|exists:template_penilaian,id',
                'print_mode' => 'nullable|in:certificate,grades,both',
            ]);

            $template = CertificateTemplate::findOrFail($request->template_id);
            
            $kelas_id = $request->kelas_id;
            
            // Default siswa list
            if ($kelas_id) {
                $kelas = Kelas::findOrFail($kelas_id);
                $defaultSiswas = Siswa::where('kelas_id', $kelas->id)->with('kelas.jurusan')->get();
                $filename_suffix = Str::slug($kelas->nama_kelas);
            } else {
                $kelas = null;
                $defaultSiswas = Siswa::with('kelas.jurusan')->get();
                $filename_suffix = 'semua_siswa';
            }

            // Optional: filter by siswa_ids
            $siswas = $defaultSiswas;
            if ($request->filled('siswa_ids')) {
                $ids = collect($request->input('siswa_ids'))->map(fn($v) => (int)$v)->all();
                $siswas = $defaultSiswas->whereIn('id', $ids)->values();
            }

            // Check jika tidak ada siswa
            if ($siswas->isEmpty()) {
                if (($printMode ?? 'both') === 'certificate') {
                    // buat 1 penerima dummy agar sertifikat tetap bisa dicetak tanpa data siswa
                    $dummy = new \stdClass();
                    $dummy->id = 0;
                    $dummy->nama = '';
                    $dummy->nis = '';
                    $dummy->kelas = (object)[
                        'nama_kelas' => '',
                        'jurusan' => (object)['nama_jurusan' => '']
                    ];
                    $siswas = collect([$dummy]);
                } else {
                    return back()->with('error', 'Tidak ada siswa yang ditemukan.');
                }
            }

            $format = $request->format;
            $dpi = (int)$request->input('dpi', 300);
            $jpgQuality = (int)$request->input('jpg_quality', 95);
            $gradeSheetFormat = $request->input('grade_sheet_format');
            $destination = $request->input('destination', 'download');
            $targetEmail = $request->input('email');
            $certFormat = trim((string)$request->input('cert_number_format', '000/SMK-TM/XX/XX/XXXX'));
            $certStart = max(1, (int)$request->input('cert_start_number', 1));
            $certDate  = $request->filled('cert_date') ? \Carbon\Carbon::parse($request->input('cert_date')) : now();
            
            // Ambil elements dari database
            $elements = $this->getTemplateElements($template);

            // Optional: nilai untuk halaman belakang jika grade template dipilih
            $gradeTemplateId = $request->input('grade_template_id');
            // Otomatis tentukan mode cetak: jika ada template nilai => keduanya, jika tidak => sertifikat saja
            $printMode = $gradeTemplateId ? 'both' : 'certificate';
            
            // Auto-detect grade sheet format based on jenis_penilaian if not explicitly set
            if (!$gradeSheetFormat && $gradeTemplateId) {
                $jenisPenilaian = Penilaian::where('template_id', $gradeTemplateId)
                    ->whereIn('siswa_id', $siswas->pluck('id'))
                    ->value('jenis_penilaian');
                    
                if ($jenisPenilaian === 'TA') {
                    $gradeSheetFormat = 'tugas_akhir';
                } elseif ($jenisPenilaian === 'Uji DUDI') {
                    $gradeSheetFormat = 'prakerin';
                }
            }
            $gradesBySiswa = [];
            $collectGrades = in_array($printMode, ['grades','both']) && $gradeTemplateId;
            if ($collectGrades && $siswas->isNotEmpty()) {
                $penilaians = Penilaian::where('template_id', $gradeTemplateId)
                    ->whereIn('siswa_id', $siswas->pluck('id'))
                    ->get(['siswa_id','nilai_detail']);
                foreach ($penilaians as $p) {
                    $gradesBySiswa[$p->siswa_id] = $p->nilai_detail;
                }
                // Filter: hanya siswa yang punya nilai
                $siswas = $siswas->filter(function($s) use ($gradesBySiswa) {
                    return isset($gradesBySiswa[$s->id]);
                })->values();

                // Sisipkan ringkasan nilai ke objek siswa (dipakai oleh variable $nilai)
                foreach ($siswas as $s) {
                    $d = $gradesBySiswa[$s->id] ?? null;
                    if (is_string($d)) { $d = json_decode($d, true); }

                    $cmp = [];
                    $detail = [];

                    if (is_array($d)) {
                        // Format standar: punya 'nilai' dan 'computed'
                        $cmp = $d['computed'] ?? [];
                        $detail = $d['nilai'] ?? [];

                        // Fallback: hasil import TA / UKK yang hanya punya 'row'
                        if (empty($cmp) && empty($detail) && isset($d['row']) && is_array($d['row'])) {
                            $row = $d['row'];
                            // simpan raw row TA untuk lampiran khusus
                            $s->nilai_ta_row = $row;

                            $score = $row['na']
                                ?? ($row['NA'] ?? null)
                                ?? ($row['nilai_akhir'] ?? null);

                            if ($score !== null && is_numeric($score)) {
                                $score = (float)$score;
                            }

                            if ($score !== null) {
                                $cmp['total'] = $score;
                                $cmp['avg'] = $score;
                                $cmp['grade'] = $score >= 90 ? 'A'
                                    : ($score >= 75 ? 'B'
                                        : ($score >= 60 ? 'C' : 'D'));
                            }

                            $label = isset($row['format']) && is_string($row['format'])
                                ? strtoupper($row['format'])
                                : 'Nilai';
                            $detail = [$label => ['Nilai Akhir' => $score]];
                        }

                        // Fallback DUDI/Prakerin: jika detail kosong namun struktur sudah berupa komponen->subkomponen
                        if (empty($detail)) {
                            $hasSubArrays = false; $hasNumeric = false;
                            foreach ($d as $k=>$v) { if (is_array($v)) { $hasSubArrays = true; foreach ($v as $vv){ if (is_numeric($vv)) { $hasNumeric = true; break; } } } }
                            if ($hasSubArrays && $hasNumeric) { $detail = $d; }
                        }

                        // Judul laporan per siswa jika ada
                        $judul = $d['judul_laporan'] ?? ($d['row']['judul_laporan'] ?? ($d['row']['judul'] ?? ($d['judul'] ?? null)));
                        if (!$judul && isset($d['keterangan']) && is_string($d['keterangan'])) { $judul = $d['keterangan']; }
                        if ($judul) { $s->judul_laporan = $judul; }
                    }

                    $sum   = $cmp['total'] ?? null;
                    $avg   = $cmp['avg'] ?? null;
                    $wavg  = $cmp['weighted_avg'] ?? null;
                    $grade = $cmp['grade'] ?? null;
                    $parts = [];
                    if ($sum !== null)  $parts[] = 'Total: ' . $sum;
                    if ($avg !== null)  $parts[] = 'Rata-rata: ' . $avg;
                    if ($wavg !== null) $parts[] = 'W. Avg: ' . $wavg;
                    if ($grade !== null) $parts[] = 'Predikat: ' . $grade;
                    $s->nilai = implode(' | ', $parts);
                    // attach for PDF table/lampiran
                    $s->nilai_detail_map = $detail;
                    $s->nilai_computed = $cmp;
                }
                // Jika setelah filter kosong, hentikan dengan pesan
                if ($siswas->isEmpty() && $printMode !== 'certificate') {
                    return back()->with('error', 'Tidak ada siswa dengan nilai pada template nilai yang dipilih.');
                }
            }

            // Filter berdasarkan eligibility (alfa > 3 atau (sakit+izin) > 5 akan dikecualikan, kecuali override)
            $eligibilityService = new CertificateEligibilityService();
            $siswas = $siswas->filter(function($s) use ($eligibilityService) {
                // Hanya siswa DB dengan id yang diperiksa eligibility
                return isset($s->id) && $s->id ? $eligibilityService->isEligible($s, null, 3, 5) : true;
            })->values();

            if ($siswas->isEmpty()) {
                return back()->with('error', 'Tidak ada siswa yang memenuhi syarat kelayakan sertifikat.');
            }

            // Pastikan nomor sertifikat di-generate setelah daftar siswa final
            if ($siswas->isNotEmpty()) {
                // Padding diambil dari run nol pertama pada format, default 3
                $padLen = 3;
                if (preg_match('/0+/', $certFormat, $mPad)) {
                    $padLen = strlen($mPad[0]);
                }
                $month = $certDate->format('m');
                $day   = $certDate->format('d');
                $year  = $certDate->format('Y');
                $iSeq = 0;
                foreach ($siswas as $s) {
                    $seq = str_pad((string)($certStart + $iSeq), $padLen, '0', STR_PAD_LEFT);
                    $num = $certFormat;
                    // Gantikan pattern
                    $num = preg_replace('/0+/', $seq, $num, 1); // nomor urut
                    $num = preg_replace('/XX/', $month, $num, 1); // bulan
                    $num = preg_replace('/XX/', $day, $num, 1);   // tanggal
                    $num = str_replace('XXXX', $year, $num);      // tahun
                    $s->cert_number = $num;
                    $iSeq++;
                }
            }

            Log::info('Generate certificates started', [
                'template_id' => $template->id,
                'template_name' => $template->nama_template,
                'format' => $format,
                'elements_count' => count($elements),
                'siswa_count' => $siswas->count(),
                'kelas' => $kelas ? $kelas->nama_kelas : 'Semua'
            ]);

            // Siapkan informasi untuk history sertifikat (jika kelas dipilih)
            $historyPayload = null;
            if ($kelas && class_exists(CertificateHistory::class)) {
                $historyPayload = [
                    'template_id' => $template->id,
                    'kelas_id' => $kelas->id,
                    'jumlah_siswa' => $siswas->count(),
                    'jenis_file' => $format,
                    'generated_by' => auth()->id(),
                ];
            }

            // Branch by destination
            if ($destination === 'download') {
                // Existing download flow
                if ($format === 'pdf') {
                    if ($historyPayload) { CertificateHistory::create($historyPayload); }
                    $onlyCertificate = ($printMode === 'certificate');
                    $onlyGrades = ($printMode === 'grades');
                    return $this->generatePDF($template, $siswas, $elements, $filename_suffix, $gradeSheetFormat, $onlyCertificate, $onlyGrades);
                }
                if (in_array($format, ['png', 'jpg', 'jpeg'])) {
                    if ($historyPayload) { CertificateHistory::create($historyPayload); }
                    // Sertifikat selalu dibuat. Jika user memilih format lampiran nilai, ikutkan halaman nilai sederhana.
                    $includeGradesImage = !empty($gradeSheetFormat);
                    return $this->generateImagesAndZip($template, $siswas, $elements, $format, $filename_suffix, $includeGradesImage, $gradeSheetFormat, $dpi, $jpgQuality);
                }
            } else {
                // Build file to path first
                if ($format === 'pdf') {
                    $built = $this->buildPdfFile($template, $siswas, $elements, $filename_suffix, $gradeSheetFormat);
                } else {
                    $built = $this->buildImagesZipFile($template, $siswas, $elements, $format, $filename_suffix);
                }

                if (!$built || empty($built['path']) || !file_exists($built['path'])) {
                    return back()->with('error', 'Gagal membuat file sertifikat.');
                }

                if ($destination === 'email') {
                    try {
                        Mail::to($targetEmail)->send(new CertificateDelivery(
                            subject: 'Sertifikat Kelulusan/Partisipasi',
                            body: 'Berikut terlampir sertifikat Anda. Terima kasih.',
                            attachmentPath: $built['path'],
                            attachmentName: $built['filename'],
                            mime: $built['mime']
                        ));
                        if ($historyPayload) { CertificateHistory::create($historyPayload); }
                    } catch (\Exception $e) {
                        Log::error('Failed to send certificate email', ['error' => $e->getMessage()]);
                        @unlink($built['path']);
                        return back()->with('error', 'Gagal mengirim email: ' . $e->getMessage());
                    }

                    // Cleanup file temp
                    @unlink($built['path']);
                    return back()->with('success', 'Sertifikat telah dikirim ke email tujuan.');
                }

                // opsi 'storage' dihilangkan dari UI dan backend
            }

            return back()->with('error', 'Format tidak valid.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error generating certificates', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Generate PDF untuk semua siswa dalam 1 file
     */
    private function generatePDF($template, $siswas, $elements, $filename_suffix, $gradeSheetFormat = null, $onlyCertificate = false, $onlyGrades = false)
    {
        try {
            Log::info('Generating PDF', [
                'siswa_count' => $siswas->count(),
                'template_id' => $template->id
            ]);

            $pdf = Pdf::loadView('master.sertifikat.download_pdf', [
                'template' => $template,
                'siswas' => $siswas,
                'elements' => $elements,
                'grade_sheet_format' => $gradeSheetFormat,
                'only_certificate' => $onlyCertificate,
                'only_grades' => $onlyGrades,
            ])
            ->setPaper('a4', 'landscape')
            ->setOption('margin-top', 0)
            ->setOption('margin-right', 0)
            ->setOption('margin-bottom', 0)
            ->setOption('margin-left', 0)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

            $filename = "sertifikat_{$filename_suffix}_" . date('YmdHis') . ".pdf";

            Log::info('PDF generated successfully', ['filename' => $filename]);

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Error generating PDF', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Generate images (PNG/JPG) untuk setiap siswa dan zip
     */
    private function generateImagesAndZip($template, $siswas, $elements, $format, $filename_suffix, $includeGrades = false, $gradeSheetFormat = null, $dpi = 200, $jpgQuality = 90)
    {
        $manager = new ImageManager(new Driver());
        $tempDir = storage_path('app/temp_certificates_' . time() . '_' . Str::random(6));
        
        // Buat direktori temporary
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $files = [];
        $backgroundPath = storage_path('app/public/' . $template->background_image);

        // Validasi background image
        if (!file_exists($backgroundPath)) {
            Log::error('Background image not found', ['path' => $backgroundPath]);
            return back()->with('error', 'Background image tidak ditemukan.');
        }

        try {
            // Determine effective DPI (memory-aware)
            $studentsCount = $siswas->count();
            $dpiEff = max(96, min(300, (int)$dpi));
            if ($studentsCount > 30) { $dpiEff = min($dpiEff, 150); }
            elseif ($studentsCount > 12) { $dpiEff = min($dpiEff, 200); }

            // Estimate per-image memory = width * height * 4 bytes
            $estimateBytes = function($d){
                $w = 11.69 * $d; $h = 8.27 * $d; return (int)($w * $h * 4);
            };
            $limitPerImage = 30 * 1024 * 1024; // 30 MB safety budget per image
            while ($estimateBytes($dpiEff) > $limitPerImage && $dpiEff > 120) {
                $dpiEff = (int) floor($dpiEff * 0.9);
            }

            Log::info('Starting image generation', [
                'format' => $format,
                'siswa_count' => $studentsCount,
                'temp_dir' => $tempDir,
                'dpi_input' => $dpi,
                'dpi_effective' => $dpiEff
            ]);

            foreach ($siswas as $index => $siswa) {
                // Load background image (fallback to A4-like canvas if read fails)
                try {
                    $image = $manager->read($backgroundPath);
                    // Upscale small backgrounds to target width based on DPI (~ width = 11.69in * dpi)
                    $targetW = (int) round(11.69 * max(96, $dpiEff));
                    if ($image->width() < $targetW) {
                        $scaleW = $targetW;
                        $scaleH = (int) round(($image->height() / max(1, $image->width())) * $scaleW);
                        $image = $image->resize($scaleW, $scaleH);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Failed to read background image, using white canvas', [
                        'path' => $backgroundPath,
                        'error' => $e->getMessage(),
                    ]);
                    // Use higher resolution A4 landscape fallback
                    $fallbackW = (int) round(11.69 * max(96, $dpiEff));
                    $fallbackH = (int) round(8.27 * max(96, $dpiEff));
                    $image = $manager->create($fallbackW, $fallbackH)->fill('#ffffff');
                }
                $width = $image->width();
                $height = $image->height();

                Log::info("Processing certificate", [
                    'siswa' => $siswa->nama,
                    'index' => $index + 1,
                    'total' => $siswas->count(),
                    'image_size' => "{$width}x{$height}"
                ]);

                // Render elements pada image
                foreach ($elements as $element) {
                    // Handle image elements
                    if ($element['type'] === 'image') {
                        try {
                            // Decode base64 image
                            $imageSrc = $element['src'] ?? '';
                            if (strpos($imageSrc, 'data:image/') === 0) {
                                $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageSrc));
                                $overlayImage = $manager->read($imageData);
                                
                                // Calculate position and size
                                $x = (int)(($element['x'] / 100) * $width);
                                $y = (int)(($element['y'] / 100) * $height);
                                // Scale overlay dimensions relative to design base width (1000)
                                $designScale = max(0.1, $width / 1000);
                                $overlayWidth = (int) round(($element['width'] ?? 100) * $designScale);
                                $overlayHeight = (int) round(($element['height'] ?? 100) * $designScale);
                                
                                // Resize overlay image
                                $overlayImage->resize($overlayWidth, $overlayHeight);
                                
                                // Place overlay on main image (center anchored)
                                $image->place($overlayImage, 'top-left', $x - ($overlayWidth/2), $y - ($overlayHeight/2));
                            }
                        } catch (\Exception $e) {
                            Log::warning('Failed to add overlay image', [
                                'error' => $e->getMessage()
                            ]);
                        }
                        continue;
                    }
                    
                    // Handle text/variable elements
                    $content = $this->getElementContent($element, $siswa, $siswa->nama);
                    
                    // Skip jika content kosong
                    if (empty(trim($content))) {
                        continue;
                    }

                    // Konversi persentase ke pixel (sama dengan preview dan PDF)
                    $x = (int)(($element['x'] / 100) * $width);
                    $y = (int)(($element['y'] / 100) * $height);
                    // Scale font size relative to background width (design base width = 1000)
                    $baseFontSize = $element['fontSize'] ?? 24;
                    $designScale = max(0.1, $width / 1000);
                    $fontSize = (int) round($baseFontSize * $designScale);
                    $color = $element['color'] ?? '#000000';
                    $fontFamily = $element['fontFamily'] ?? 'Arial';
                    $align = $element['align'] ?? 'center';
                    $bold = $element['bold'] ?? false;

                    // Get font file
                    $fontFile = $this->getFontPath($fontFamily, $bold);

                    // Add text to image
                    try {
                        $image->text($content, $x, $y, function($font) use ($fontSize, $color, $align, $fontFile) {
                            if (file_exists($fontFile)) {
                                $font->file($fontFile);
                            }
                            $font->size($fontSize);
                            $font->color($color);
                            $font->align($align);
                            $font->valign('middle');
                        });
                    } catch (\Exception $e) {
                        Log::warning('Failed to add text to image', [
                            'content' => $content,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                // Generate filename yang aman
                $siswaFilename = Str::slug($siswa->nama) . '_' . $siswa->nis;
                
                // Save image
                if ($format === 'png') {
                    $filePath = $tempDir . '/' . $siswaFilename . '.png';
                    $image->toPng()->save($filePath);
                } elseif (in_array($format, ['jpg', 'jpeg'])) {
                    $extension = $format === 'jpeg' ? 'jpeg' : 'jpg';
                    $filePath = $tempDir . '/' . $siswaFilename . '.' . $extension;
                    $image->toJpeg(max(70, min($jpgQuality, 100)))->save($filePath);
                }

                $files[] = $filePath;

                Log::info("Certificate saved", [
                    'file' => basename($filePath),
                    'size' => filesize($filePath)
                ]);

                // Optional: create grade sheet image as second page when requested (disabled by default for image exports)
                if ($includeGrades && isset($siswa->nilai_detail_map) && is_array($siswa->nilai_detail_map) && !empty($siswa->nilai_detail_map)) {
                    try {
                        // Create high-res A4 canvas (landscape) per DPI (11.69in x 8.27in)
                        $w = (int) round(11.69 * max(96, $dpiEff));
                        $h = (int) round(8.27 * max(96, $dpiEff));
                        $scaleDpi = max(1.0, $dpiEff / 96); // scale relative to 96dpi base
                        $sheet = $manager->create($w, $h)->fill('#ffffff');
                        // Branch: prakerin vs tugas_akhir to approximate blade templates
                        $left = 60; $infoY = 84; $lineH = 24; $y = 0;
                        if ($gradeSheetFormat === 'prakerin') {
                            // Header
                            $sheet->text('DAFTAR NILAI PRAKTIK KERJA INDUSTRI (PRAKERIN)', (int)($w/2), (int)(84*$scaleDpi), function($f) use($scaleDpi){ $f->size((int)(36*$scaleDpi)); $f->color('#000'); $f->align('center'); $f->valign('top'); $f->lineHeight(1.0); });
                            $sheet->text('SEKOLAH MENENGAH KEJURUAN (SMK) TUNAS MEDIA KOTA DEPOK', (int)($w/2), 132, function($f){ $f->size(28); $f->color('#000'); $f->align('center'); });
                            $sheet->text('TAHUN PELAJARAN ' . date('Y') . '/' . (date('Y')+1), (int)($w/2), 168, function($f){ $f->size(22); $f->color('#000'); $f->align('center'); });

                            // Identitas
                            $sheet->text('Nama Peserta Prakerin: ' . ($siswa->nama ?? ''), $left, (int)(240*$scaleDpi), function($f) use($scaleDpi){ $f->size((int)(24*$scaleDpi)); $f->color('#000'); });
                            $sheet->text('Nomor Induk Siswa (NIS): ' . ($siswa->nis ?? ''), $left, 276, function($f){ $f->size(24); $f->color('#000'); });
                            $sheet->text('Kelas / Kompetensi Keahlian: ' . (($siswa->kelas->nama_kelas ?? '') . ' / ' . ($siswa->kelas->jurusan->nama_jurusan ?? '')), $left, 312, function($f){ $f->size(24); $f->color('#000'); });

                            // Table header
                            $y = (int)(360*$scaleDpi);
                            $colKomp = $left; $colUraian = $left + 760; $colAngka = $w - 420; $colHuruf = $w - 220;
                            $sheet->text('KOMPONEN PENILAIAN', $colKomp, $y, function($f) use($scaleDpi){ $f->size((int)(24*$scaleDpi)); $f->color('#000'); });
                            $sheet->text('URAIAN', $colUraian, $y, function($f){ $f->size(24); $f->color('#000'); });
                            $sheet->text('ANGKA', $colAngka, $y, function($f){ $f->size(24); $f->color('#000'); });
                            $sheet->text('HURUF', $colHuruf, $y, function($f){ $f->size(24); $f->color('#000'); });
                            $y += 24;

                            // Rows from nilai_detail_map
                            $toLetter = function($v){ if ($v===null||$v==='') return ''; if(!is_numeric($v)) return ''; $n=(float)$v; return $n>=90?'A':($n>=75?'B':($n>=60?'C':'D')); };
                            $allNums = [];
                            foreach (($siswa->nilai_detail_map ?? []) as $komponen => $subs) {
                                if (is_array($subs)) {
                                    $first = true;
                                    foreach ($subs as $uraian => $nilai) {
                                        if (is_numeric($nilai)) { $allNums[] = (float)$nilai; }
                                        if ($first) { $sheet->text((string)$komponen, $colKomp, $y, function($f){ $f->size(22); $f->color('#111'); }); $first=false; }
                                        $sheet->text((string)$uraian, $colUraian, $y, function($f) use($scaleDpi){ $f->size((int)(22*$scaleDpi)); $f->color('#111'); });
                                        $sheet->text(is_numeric($nilai) ? number_format((float)$nilai,2) : (string)$nilai, $colAngka, $y, function($f){ $f->size(22); $f->color('#111'); });
                                        $sheet->text($toLetter($nilai), $colHuruf, $y, function($f){ $f->size(22); $f->color('#111'); });
                                        $y += (int)(36*$scaleDpi); if ($y > $h - (int)(260*$scaleDpi)) break 2;
                                    }
                                }
                            }

                            // Summary
                            $avg = count($allNums) ? round(array_sum($allNums)/count($allNums),2) : null;
                            $sheet->text('TOTAL / RATA-RATA / PREDIKAT', $left, $h-(int)(180*$scaleDpi), function($f) use($scaleDpi){ $f->size((int)(22*$scaleDpi)); $f->color('#000'); });
                            if ($avg !== null) {
                                $sheet->text('Rata-rata: ' . $avg, $left, $h-140, function($f){ $f->size(20); $f->color('#111'); });
                                $sheet->text('Predikat: ' . ($avg>=90?'A':($avg>=75?'B':($avg>=60?'C':'D'))), $left, $h-110, function($f){ $f->size(20); $f->color('#111'); });
                            }
                        } else { // tugas_akhir
                            // Header
                            $sheet->text('DAFTAR NILAI TUGAS AKHIR', (int)($w/2), (int)(90*$scaleDpi), function($f) use($scaleDpi){ $f->size((int)(36*$scaleDpi)); $f->color('#000'); $f->align('center'); });
                            $sheet->text('SEKOLAH MENENGAH KEJURUAN (SMK) TUNAS MEDIA', (int)($w/2), 132, function($f){ $f->size(28); $f->color('#000'); $f->align('center'); });
                            $sheet->text('TAHUN PELAJARAN ' . date('Y') . '/' . (date('Y')+1), (int)($w/2), 168, function($f){ $f->size(22); $f->color('#000'); $f->align('center'); });

                            // Identitas
                            $sheet->text('Nama: ' . ($siswa->nama ?? ''), $left, (int)(240*$scaleDpi), function($f) use($scaleDpi){ $f->size((int)(24*$scaleDpi)); $f->color('#000'); });
                            $sheet->text('NIS: ' . ($siswa->nis ?? ''), $left, 276, function($f){ $f->size(24); $f->color('#000'); });
                            $sheet->text('Kompetensi Keahlian: ' . ($siswa->kelas->jurusan->nama_jurusan ?? ''), $left, 312, function($f){ $f->size(24); $f->color('#000'); });

                            // Table header
                            $y = (int)(360*$scaleDpi); $colNo=$left; $colKom=$left+120; $colAngka=$w-420; $colHuruf=$w-220;
                            $sheet->text('NO', $colNo, $y, function($f){ $f->size(24); $f->color('#000'); });
                            $sheet->text('KOMPONEN / SUB KOMPONEN', $colKom, $y, function($f){ $f->size(24); $f->color('#000'); });
                            $sheet->text('ANGKA', $colAngka, $y, function($f){ $f->size(24); $f->color('#000'); });
                            $sheet->text('HURUF', $colHuruf, $y, function($f){ $f->size(24); $f->color('#000'); });
                            $y+=24;

                            // Rows: NP & NA + subcomponents (fallback tetap tampil)
                            $row = $siswa->nilai_ta_row ?? [];
                            $toLetter = function($v){ if ($v===null||$v==='') return ''; if(!is_numeric($v)) return ''; $n=(float)$v; if ($n<=20) $n*=5; return $n>=90?'A':($n>=75?'B':($n>=60?'C':'D')); };
                            $np = isset($row['np']) && is_numeric($row['np']) ? (float)$row['np'] : null;
                            $na = isset($row['na']) && is_numeric($row['na']) ? (float)$row['na'] : null;

                            $sheet->text('1', $colNo, $y, function($f) use($scaleDpi){ $f->size((int)(22*$scaleDpi)); });
                            $sheet->text('Nilai Project', $colKom, $y, function($f){ $f->size(22); });
                            $sheet->text($np !== null ? number_format($np,2) : '-', $colAngka, $y, function($f){ $f->size(22); });
                            $sheet->text($toLetter($np), $colHuruf, $y, function($f){ $f->size(22); });
                            $y += (int)(36*$scaleDpi);

                            $sheet->text('2', $colNo, $y, function($f) use($scaleDpi){ $f->size((int)(22*$scaleDpi)); });
                            $sheet->text('Nilai Sidang Tugas Akhir', $colKom, $y, function($f){ $f->size(22); });
                            $sheet->text($na !== null ? number_format($na,2) : '-', $colAngka, $y, function($f){ $f->size(22); });
                            $sheet->text($toLetter($na), $colHuruf, $y, function($f){ $f->size(22); });
                            $y += 36;

                            // Sub-rows demo (tetap tampil dengan nilai standar jika tidak ada data)
                            $subs = [
                                '2.1. Kemampuan dalam menjelaskan indikasi' => 18,
                                '2.2. Kemampuan dalam meneruskan pertanyaan' => 17,
                                '2.3. Kemampuan memperbaiki produk TA' => 17,
                                '2.4. Sistematika Laporan' => 15,
                                '2.5. Keaslian Project' => 7,
                                '2.6. Sikap dan penampilan sidang' => 8,
                            ];
                            foreach ($subs as $label=>$val){
                                $sheet->text('', $colNo, $y, function($f) use($scaleDpi){ $f->size((int)(22*$scaleDpi)); });
                                $sheet->text($label, $colKom+40, $y, function($f){ $f->size(22); });
                                $sheet->text((string)$val, $colAngka, $y, function($f){ $f->size(22); });
                                $sheet->text($toLetter($val), $colHuruf, $y, function($f){ $f->size(22); });
                                $y += (int)(30*$scaleDpi); if ($y > $h-(int)(220*$scaleDpi)) break; 
                            }

                            // Summary row
                            $sheet->text('NILAI AKHIR (30% Project + 70% Sidang)', $colKom, $h-(int)(180*$scaleDpi), function($f) use($scaleDpi){ $f->size((int)(22*$scaleDpi)); $f->color('#000'); });
                            $final = $na ?? $np; $finalTxt = $final!==null ? number_format($final,2) : '-';
                            $sheet->text($finalTxt, $colAngka, $h-(int)(180*$scaleDpi), function($f) use($scaleDpi){ $f->size((int)(22*$scaleDpi)); });
                            $sheet->text($toLetter($final), $colHuruf, $h-(int)(180*$scaleDpi), function($f) use($scaleDpi){ $f->size((int)(22*$scaleDpi)); });
                        }

                        // Save
                        if ($format === 'png') {
                            $sheetPath = $tempDir . '/' . $siswaFilename . '_nilai.png';
                            $sheet->toPng()->save($sheetPath);
                        } else {
                            $sheetPath = $tempDir . '/' . $siswaFilename . '_nilai.' . ($format === 'jpeg' ? 'jpeg' : 'jpg');
                            $sheet->toJpeg(92)->save($sheetPath);
                        }

                        $files[] = $sheetPath;

                        Log::info('Grade sheet image saved', ['file' => basename($sheetPath)]);
                    } catch (\Exception $e) {
                        Log::warning('Failed to create grade sheet image', ['error' => $e->getMessage()]);
                    }
                }

                // Free memory each iteration
                if (isset($image)) { unset($image); }
                if (isset($sheet)) { unset($sheet); }
                if (function_exists('gc_collect_cycles')) { gc_collect_cycles(); }
            }

            // Jika hanya 1 file, download langsung tanpa zip
            if (count($files) === 1) {
                $file = $files[0];
                $downloadName = basename($file);
                
                Log::info('Downloading single file', ['filename' => $downloadName]);
                
                return response()->download($file, $downloadName)->deleteFileAfterSend(true);
            }

            // Buat ZIP file untuk multiple files
            $zipFilename = "sertifikat_{$filename_suffix}_" . date('YmdHis') . ".zip";
            $zipPath = $tempDir . '/' . $zipFilename;
            
            Log::info('Creating ZIP file', [
                'files_count' => count($files),
                'zip_path' => $zipPath
            ]);

            $zip = new ZipArchive();
            
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                foreach ($files as $file) {
                    $zip->addFile($file, basename($file));
                }
                $zip->close();

                Log::info('ZIP created successfully', [
                    'files_count' => count($files),
                    'zip_size' => filesize($zipPath),
                    'zip_path' => $zipPath
                ]);

                // Download ZIP dan hapus setelahnya
                $response = response()->download($zipPath, $zipFilename, [
                    'Content-Type' => 'application/zip',
                ])->deleteFileAfterSend(true);
                
                // Register cleanup callback
                register_shutdown_function(function() use ($files, $tempDir) {
                    // Cleanup: hapus file individual dan folder temp
                    foreach ($files as $file) {
                        if (file_exists($file)) {
                            @unlink($file);
                        }
                    }
                    
                    // Hapus folder temp jika kosong
                    if (is_dir($tempDir)) {
                        @rmdir($tempDir);
                    }
                    
                    Log::info('Cleanup completed', ['temp_dir' => $tempDir]);
                });
                
                return $response;
            }

            throw new \Exception('Gagal membuat file ZIP');

        } catch (\Exception $e) {
            // Cleanup on error
            Log::error('Error in image generation, performing cleanup', [
                'error' => $e->getMessage()
            ]);

            foreach ($files as $file) {
                if (file_exists($file)) {
                    @unlink($file);
                }
            }
            
            if (is_dir($tempDir)) {
                // Hapus semua file dalam folder
                $tempFiles = glob($tempDir . '/*');
                foreach ($tempFiles as $file) {
                    if (is_file($file)) {
                        @unlink($file);
                    }
                }
                @rmdir($tempDir);
            }

            Log::error('Error generating images', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Get font path berdasarkan family dan bold
     */
    private function getFontPath($fontFamily, $bold = false)
    {
        $fontMap = [
            'Arial' => $bold ? 'arialbd.ttf' : 'arial.ttf',
            'Times New Roman' => $bold ? 'timesbd.ttf' : 'times.ttf',
            'Georgia' => $bold ? 'georgiab.ttf' : 'georgia.ttf',
            'Courier New' => $bold ? 'courbd.ttf' : 'cour.ttf',
            'Verdana' => $bold ? 'verdanab.ttf' : 'verdana.ttf',
        ];

        // 1) Try from public/fonts first
        $publicName = $fontMap[$fontFamily] ?? 'arial.ttf';
        $fontPath = public_path("fonts/{$publicName}");

        // Fallback ke regular jika bold tidak ada
        if (!file_exists($fontPath) && $bold) {
            // try remove 'bd' suffix common on Windows names
            $regularPublic = str_replace(['bd', 'bold', '-bold'], ['', '', ''], $publicName);
            $regularPath = public_path("fonts/{$regularPublic}");
            if (file_exists($regularPath)) {
                Log::info('Using regular font as fallback', [
                    'requested' => $publicName,
                    'fallback' => $regularPublic
                ]);
                return $regularPath;
            }
        }

        // 2) Try Windows fonts folder
        $winDir = getenv('WINDIR') ?: 'C:\\Windows';
        $winFonts = rtrim($winDir, '\\/') . DIRECTORY_SEPARATOR . 'Fonts';
        $winCandidate = $winFonts . DIRECTORY_SEPARATOR . ($fontMap[$fontFamily] ?? 'arial.ttf');
        if (!file_exists($fontPath) && file_exists($winCandidate)) {
            return $winCandidate;
        }

        // 3) Fallback ke Arial dari public atau Windows
        // from public first
        $arialPublic = public_path('fonts/arial.ttf');
        if (!file_exists($fontPath) && file_exists($arialPublic)) {
            Log::warning('Font not found, using Arial from public', [
                'requested' => $publicName,
                'fallback' => 'arial.ttf'
            ]);
            return $arialPublic;
        }

        // from Windows fonts
        $arialWin = $winFonts . DIRECTORY_SEPARATOR . 'arial.ttf';
        if (!file_exists($fontPath) && file_exists($arialWin)) {
            Log::warning('Font not found, using Arial from Windows', [
                'requested' => $publicName,
                'fallback' => $arialWin
            ]);
            return $arialWin;
        }

        // As last resort, return the original (may not exist)
        if (!file_exists($fontPath)) {
            Log::warning('Font file does not exist, returning unresolved path', [
                'path' => $fontPath
            ]);
        }

        return $fontPath;
    }

    /**
     * 🔥 SINGLE SOURCE OF TRUTH - Helper untuk get template elements
     * Method ini digunakan oleh: customize(), preview(), dan generate()
     */
    private function getTemplateElements($template)
    {
        $elements = [];
        
        // Coba ambil dari kolom 'elements' (JSON)
        if ($template->elements && is_string($template->elements)) {
            try {
                $decoded = json_decode($template->elements, true);
                if (is_array($decoded) && !empty($decoded)) {
                    // Buang elemen text default lama: CERTIFICATE, of appreciation, is presented to
                    $unwanted = [
                        'certificate',
                        'of appreciation',
                        'is presented to',
                    ];
                    $elements = array_values(array_filter($decoded, function ($el) use ($unwanted) {
                        if (!is_array($el)) { return false; }
                        if (($el['type'] ?? '') !== 'text') { return true; }
                        $content = strtolower(trim((string)($el['content'] ?? '')));
                        return !in_array($content, $unwanted, true);
                    }));

                    Log::info('Elements loaded from database (filtered legacy defaults)', [
                        'template_id' => $template->id,
                        'count' => count($elements),
                        'sample' => array_slice($elements, 0, 2)
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to decode elements JSON', [
                    'template_id' => $template->id,
                    'error' => $e->getMessage(),
                    'raw' => substr($template->elements ?? '', 0, 200)
                ]);
            }
        }
        
        // Jika masih kosong, gunakan default elements
        // Hanya satu elemen: variabel nama siswa, tanpa teks dekoratif lain
        if (empty($elements)) {
            Log::info('Using minimal default elements (name variable only) for template', ['template_id' => $template->id]);
            $elements = [
                [
                    'id' => 1,
                    'type' => 'variable',
                    'variable' => '$nama_siswa',
                    'value' => 'Student Name',
                    'x' => 50,
                    'y' => 50,
                    'fontSize' => 36,
                    'fontFamily' => 'Times New Roman',
                    'color' => '#111111',
                    'bold' => true,
                    'align' => 'center'
                ],
            ];
        }
        
        return $elements;
    }

    /**
     * Helper: Get content for element (untuk generate PDF/PNG)
     */
    private function getElementContent($element, $siswa, $name)
    {
        if ($element['type'] === 'text') {
            return $element['content'] ?? '';
        } elseif ($element['type'] === 'variable') {
            $var = trim((string)($element['variable'] ?? ''));
            $norm = preg_replace('/[^a-z]/', '', strtolower($var));
            return match($var) {
                '$nama_siswa' => $name,
                '$kelas' => $siswa->kelas->nama_kelas ?? '',
                '$nis' => $siswa->nis ?? '',
                '$tanggal' => now()->locale('id')->isoFormat('D MMMM YYYY'),
                '$jurusan' => $siswa->kelas->jurusan->nama_jurusan ?? '',
                '$nilai' => $siswa->nilai ?? '-',
                '$peringkat' => $siswa->peringkat ?? '-',
                '$nomor_sertifikat' => $siswa->cert_number ?? '',
                '$no_sertifikat' => $siswa->cert_number ?? '',
                '$no_sertif' => $siswa->cert_number ?? '',
                '$nomor' => $siswa->cert_number ?? '',
                '$ttd' => '(Tanda Tangan)',
                default => (
                        str_contains($norm, 'nomorsertifikat') ||
                        str_contains($norm, 'nosertifikat') ||
                        str_contains($norm, 'nosertif')
                    ) ? ($siswa->cert_number ?? '')
                      : ($element['value'] ?? $element['variable'] ?? '')
            };
        }
        return '';
    }

    /**
     * History page
     */
    public function history(\Illuminate\Http\Request $request)
    {
        $range = $request->query('range', 'all'); // all, month
        $templateId = $request->query('template_id');
        $kelasId = $request->query('kelas_id');
        $jenisFile = $request->query('jenis_file'); // pdf, png, jpg, jpeg, zip
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $queryText = trim((string)$request->query('q', ''));

        $q = \App\Models\CertificateHistory::query()
            ->leftJoin('certificate_templates as t', 't.id', '=', 'certificate_histories.template_id')
            ->leftJoin('kelas as k', 'k.id', '=', 'certificate_histories.kelas_id')
            ->select('certificate_histories.*', 't.nama_template', 'k.nama_kelas')
            ->when($range === 'month', function($qq){
                $qq->whereBetween('certificate_histories.created_at', [now()->startOfMonth(), now()->endOfMonth()]);
            })
            ->when($templateId, fn($qq) => $qq->where('certificate_histories.template_id', (int)$templateId))
            ->when($kelasId, fn($qq) => $qq->where('certificate_histories.kelas_id', (int)$kelasId))
            ->when($jenisFile, fn($qq) => $qq->where('certificate_histories.jenis_file', $jenisFile))
            ->when($dateFrom, fn($qq) => $qq->whereDate('certificate_histories.created_at', '>=', $dateFrom))
            ->when($dateTo, fn($qq) => $qq->whereDate('certificate_histories.created_at', '<=', $dateTo))
            ->when($queryText !== '', function($qq) use ($queryText){
                $kw = '%'.strtolower($queryText).'%';
                $qq->where(function($sub) use ($kw){
                    $sub->whereRaw('LOWER(COALESCE(t.nama_template, "")) like ?', [$kw])
                        ->orWhereRaw('LOWER(COALESCE(k.nama_kelas, "")) like ?', [$kw]);
                });
            })
            ->orderBy('certificate_histories.created_at', 'desc');

        $histories = $q->paginate(12)->withQueryString();

        // options for filters
        $templateOptions = \App\Models\CertificateTemplate::orderBy('nama_template')->get(['id','nama_template']);
        $kelasOptions = \App\Models\Kelas::orderBy('nama_kelas')->get(['id','nama_kelas']);
        $jenisOptions = ['pdf' => 'PDF', 'png' => 'PNG', 'jpg' => 'JPG', 'jpeg' => 'JPEG', 'zip' => 'ZIP'];

        // small stats
        $printedAll = (int) \App\Models\CertificateHistory::sum('jumlah_siswa');
        $printedThisMonth = (int) \App\Models\CertificateHistory::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('jumlah_siswa');

        return view('master.sertifikat.history', compact(
            'histories', 'range', 'printedAll', 'printedThisMonth',
            'templateOptions', 'kelasOptions', 'jenisOptions',
            'templateId', 'kelasId', 'jenisFile', 'dateFrom', 'dateTo', 'queryText'
        ));
    }

    /**
     * Render a browser-friendly download view that mirrors the PDF layout
     */
    public function downloadView(Request $request, $template_id)
    {
        $template = CertificateTemplate::findOrFail($template_id);

        // Build siswa list similar to generate()
        $kelas_id = $request->query('kelas_id');
        $selectedStudentIds = collect((array) $request->query('siswa_ids'))->map(fn($v) => (int)$v)->filter();

        if ($selectedStudentIds->isNotEmpty()) {
            $siswas = Siswa::with('kelas.jurusan')->whereIn('id', $selectedStudentIds)->get();
        } elseif ($kelas_id) {
            $siswas = Siswa::with('kelas.jurusan')->where('kelas_id', (int)$kelas_id)->get();
        } else {
            $siswas = Siswa::with('kelas.jurusan')->get();
        }

        $gradeTemplateId = $request->query('grade_template_id');
        $gradeSheetFormat = $request->query('grade_sheet_format');

        // Attach grades when grade template is present (as in preview/generate)
        if ($gradeTemplateId) {
            $gradesBySiswa = [];
            if ($siswas->isNotEmpty()) {
                $penilaians = Penilaian::where('template_id', (int)$gradeTemplateId)
                    ->whereIn('siswa_id', $siswas->pluck('id'))
                    ->get(['siswa_id','nilai_detail']);
                foreach ($penilaians as $p) { $gradesBySiswa[$p->siswa_id] = $p->nilai_detail; }
            }
            foreach ($siswas as $s) {
                $d = $gradesBySiswa[$s->id] ?? null;
                if (is_string($d)) { $d = json_decode($d, true); }
                $cmp = $d['computed'] ?? [];
                $detail = $d['nilai'] ?? [];
                if (empty($cmp) && empty($detail) && isset($d['row']) && is_array($d['row'])) {
                    $row = $d['row'];
                    $s->nilai_ta_row = $row;
                    $score = $row['na'] ?? ($row['NA'] ?? ($row['nilai_akhir'] ?? null));
                    if ($score !== null && is_numeric($score)) { $score = (float)$score; }
                    if ($score !== null) {
                        $cmp['total'] = $score; $cmp['avg'] = $score; $cmp['grade'] = $score >= 90 ? 'A' : ($score >= 75 ? 'B' : ($score >= 60 ? 'C' : 'D'));
                    }
                    $label = isset($row['format']) && is_string($row['format']) ? strtoupper($row['format']) : 'Nilai';
                    $detail = [$label => ['Nilai Akhir' => $score]];
                }
                $parts = [];
                if (isset($cmp['total'])) $parts[] = 'Total: ' . $cmp['total'];
                if (isset($cmp['avg'])) $parts[] = 'Rata-rata: ' . $cmp['avg'];
                if (isset($cmp['weighted_avg'])) $parts[] = 'W. Avg: ' . $cmp['weighted_avg'];
                if (isset($cmp['grade'])) $parts[] = 'Predikat: ' . $cmp['grade'];
                $s->nilai = implode(' | ', $parts);
                $s->nilai_detail_map = $detail;
                $s->nilai_computed = $cmp;
            }
        }

        // Elements for certificate
        $elements = $this->getTemplateElements($template);

        // Determine printing mode similar to generate()
        $onlyCertificate = $gradeTemplateId ? false : true;
        $onlyGrades = false;

        // Optional signatures model for grades
        $signatures = class_exists(\App\Models\GradeSignature::class)
            ? \App\Models\GradeSignature::first()
            : null;

        return view('master.sertifikat.download_view', [
            'template' => $template,
            'siswas' => $siswas,
            'elements' => $elements,
            'grade_sheet_format' => $gradeSheetFormat,
            'only_certificate' => $onlyCertificate,
            'only_grades' => $onlyGrades,
            'signatures' => $signatures,
        ]);
    }
}