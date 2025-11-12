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
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use ZipArchive;
use Illuminate\Support\Str;
use App\Mail\CertificateDelivery;
use App\Models\CertificateOverride;

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
        return view('master.sertifikat.select_grade', compact('template', 'gradeTemplates', 'selectedStudentIds', 'kelas_id'));
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
    private function buildPdfFile($template, $siswas, $elements, $filename_suffix)
    {
        $pdf = Pdf::loadView('master.sertifikat.download_pdf', [
            'template' => $template,
            'siswas' => $siswas,
            'elements' => $elements,
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
            if ($gradeTemplate && $siswaList->isNotEmpty()) {
                $pen = Penilaian::where('template_id', $gradeTemplateId)
                    ->whereIn('siswa_id', $siswaList->pluck('id'))
                    ->get(['siswa_id','nilai_detail']);
                foreach ($pen as $p) {
                    // nilai_detail diharapkan format { nilai: {...}, computed: {...} }
                    $existingPenilaianBySiswa[$p->siswa_id] = $p->nilai_detail;
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
            // Tanpa pilihan siswa/kelas, jangan tampilkan semua siswa
            $kelas = null;
            $siswas = collect();
        }

        // Ambil elements yang sudah disimpan
        $elements = $this->getTemplateElements($template);
        
        // Eligibility map per siswa (true/false)
        $eligibilityService = new CertificateEligibilityService();
        $eligibility = [];
        foreach ($siswas as $s) {
            if (isset($s->id) && $s->id) {
                $eligibility[$s->id] = $eligibilityService->isEligible($s, null, 3);
            } else {
                // custom recipients: default eligible
                $eligibility[spl_object_hash($s)] = true;
            }
        }

        // Optional: grade filter & attach summary for preview
        $gradeTemplateId = $request->query('grade_template_id');
        if ($gradeTemplateId && $siswas->isNotEmpty()) {
            $gradesBySiswa = [];
            $penilaians = Penilaian::where('template_id', (int)$gradeTemplateId)
                ->whereIn('siswa_id', $siswas->pluck('id'))
                ->get(['siswa_id','nilai_detail']);
            foreach ($penilaians as $p) { $gradesBySiswa[$p->siswa_id] = $p->nilai_detail; }
            // Filter: hanya yang punya nilai
            $siswas = $siswas->filter(fn($s) => isset($gradesBySiswa[$s->id]))->values();
            // Attach ringkasan & detail
            foreach ($siswas as $s) {
                $d = $gradesBySiswa[$s->id] ?? null;
                if (is_string($d)) { $d = json_decode($d, true); }
                $cmp = is_array($d) ? ($d['computed'] ?? []) : [];
                $detail = is_array($d) ? ($d['nilai'] ?? []) : [];
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
                'destination' => 'nullable|in:download,email,storage',
                'email' => 'nullable|required_if:destination,email|email',
                'siswa_ids' => 'nullable|array',
                'siswa_ids.*' => 'integer|exists:siswas,id',
                'grade_template_id' => 'nullable|integer|exists:template_penilaian,id',
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
                return back()->with('error', 'Tidak ada siswa yang ditemukan.');
            }

            $format = $request->format;
            $destination = $request->input('destination', 'download');
            $targetEmail = $request->input('email');
            
            // Ambil elements dari database
            $elements = $this->getTemplateElements($template);

            // Optional: nilai untuk halaman belakang jika grade template dipilih
            $gradeTemplateId = $request->input('grade_template_id');
            $gradesBySiswa = [];
            if ($gradeTemplateId && $siswas->isNotEmpty()) {
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
                    $cmp = is_array($d) ? ($d['computed'] ?? []) : [];
                    $detail = is_array($d) ? ($d['nilai'] ?? []) : [];
                    $sum = $cmp['total'] ?? null; $avg = $cmp['avg'] ?? null; $wavg = $cmp['weighted_avg'] ?? null; $grade = $cmp['grade'] ?? null;
                    $parts = [];
                    if ($sum !== null) $parts[] = 'Total: ' . $sum;
                    if ($avg !== null) $parts[] = 'Rata-rata: ' . $avg;
                    if ($wavg !== null) $parts[] = 'W. Avg: ' . $wavg;
                    if ($grade !== null) $parts[] = 'Predikat: ' . $grade;
                    $s->nilai = implode(' | ', $parts);
                    // attach for PDF table
                    $s->nilai_detail_map = $detail; // grouped komponen -> subs
                    $s->nilai_computed = $cmp;
                }
                // Jika setelah filter kosong, hentikan dengan pesan
                if ($siswas->isEmpty()) {
                    return back()->with('error', 'Tidak ada siswa dengan nilai pada template nilai yang dipilih.');
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

            // Branch by destination
            if ($destination === 'download') {
                // Existing download flow
                if ($format === 'pdf') {
                    return $this->generatePDF($template, $siswas, $elements, $filename_suffix);
                }
                if (in_array($format, ['png', 'jpg', 'jpeg'])) {
                    return $this->generateImagesAndZip($template, $siswas, $elements, $format, $filename_suffix);
                }
            } else {
                // Build file to path first
                if ($format === 'pdf') {
                    $built = $this->buildPdfFile($template, $siswas, $elements, $filename_suffix);
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
                    } catch (\Exception $e) {
                        Log::error('Failed to send certificate email', ['error' => $e->getMessage()]);
                        @unlink($built['path']);
                        return back()->with('error', 'Gagal mengirim email: ' . $e->getMessage());
                    }

                    // Cleanup file temp
                    @unlink($built['path']);
                    return back()->with('success', 'Sertifikat telah dikirim ke email tujuan.');
                }

                if ($destination === 'storage') {
                    // Simpan ke storage publik
                    $publicDir = storage_path('app/public/certificates');
                    if (!is_dir($publicDir)) { @mkdir($publicDir, 0755, true); }
                    $destPath = $publicDir . DIRECTORY_SEPARATOR . $built['filename'];
                    @rename($built['path'], $destPath);
                    $publicUrl = \Illuminate\Support\Facades\Storage::url('certificates/' . $built['filename']);
                    return back()->with('success', 'Sertifikat disimpan ke storage. URL: ' . $publicUrl);
                }
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
    private function generatePDF($template, $siswas, $elements, $filename_suffix)
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
    private function generateImagesAndZip($template, $siswas, $elements, $format, $filename_suffix)
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
            Log::info('Starting image generation', [
                'format' => $format,
                'siswa_count' => $siswas->count(),
                'temp_dir' => $tempDir
            ]);

            foreach ($siswas as $index => $siswa) {
                // Load background image
                $image = $manager->read($backgroundPath);
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
                                $overlayWidth = $element['width'] ?? 100;
                                $overlayHeight = $element['height'] ?? 100;
                                
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
                    // Gunakan font size yang sama dengan PDF (dengan scaling)
                    $baseFontSize = $element['fontSize'] ?? 24;
                    $scale = 1122 / 1000; // sama dengan PDF scale
                    $fontSize = $baseFontSize * $scale;
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
                    $image->toJpeg(90)->save($filePath);
                }

                $files[] = $filePath;

                Log::info("Certificate saved", [
                    'file' => basename($filePath),
                    'size' => filesize($filePath)
                ]);

                // Optional: create grade sheet image as second page when nilai exists
                if (isset($siswa->nilai_detail_map) && is_array($siswa->nilai_detail_map) && !empty($siswa->nilai_detail_map)) {
                    try {
                        // Create A4-like white canvas (landscape) ~ 1122x794
                        $w = 1122; $h = 794;
                        $sheet = $manager->create($w, $h)->fill('#ffffff');

                        // Title
                        $title = 'Lampiran Nilai';
                        $sheet->text($title, (int)($w/2), 40, function($font) {
                            $font->size(28);
                            $font->color('#000000');
                            $font->align('center');
                            $font->valign('top');
                        });

                        // Student info
                        $infoY = 80; $lineH = 24; $left = 60;
                        $sheet->text('Nama: ' . ($siswa->nama ?? ''), $left, $infoY, function($font){ $font->size(16); $font->color('#000'); $font->align('left'); $font->valign('top'); });
                        $sheet->text('NIS: ' . ($siswa->nis ?? ''), $left, $infoY + $lineH, function($font){ $font->size(16); $font->color('#000'); });
                        $sheet->text('Kelas: ' . ($siswa->kelas->nama_kelas ?? ''), $left, $infoY + 2*$lineH, function($font){ $font->size(16); $font->color('#000'); });

                        // Table headers
                        $y = $infoY + 3*$lineH + 16;
                        $colNo = $left; $colKomp = $left + 50; $colUraian = $left + 300; $colNilai = $w - 140;
                        $sheet->text('No', $colNo, $y, function($f){ $f->size(16); $f->color('#000'); });
                        $sheet->text('Komponen', $colKomp, $y, function($f){ $f->size(16); $f->color('#000'); });
                        $sheet->text('Uraian', $colUraian, $y, function($f){ $f->size(16); $f->color('#000'); });
                        $sheet->text('Nilai', $colNilai, $y, function($f){ $f->size(16); $f->color('#000'); });
                        $y += 6;

                        // Rows
                        $i = 1; $y += 24;
                        foreach (($siswa->nilai_detail_map ?? []) as $komponen => $subs) {
                            if (is_array($subs)) {
                                foreach ($subs as $uraian => $nilai) {
                                    $sheet->text((string)$i, $colNo, $y, function($f){ $f->size(15); $f->color('#111'); });
                                    $sheet->text((string)$komponen, $colKomp, $y, function($f){ $f->size(15); $f->color('#111'); });
                                    $sheet->text((string)$uraian, $colUraian, $y, function($f){ $f->size(15); $f->color('#111'); });
                                    $sheet->text(is_numeric($nilai) ? number_format((float)$nilai, 2) : (string)$nilai, $colNilai, $y, function($f){ $f->size(15); $f->color('#111'); $f->align('left'); });
                                    $y += 22; $i++;
                                    if ($y > $h - 140) { break; } // simple overflow guard
                                }
                            } else {
                                $sheet->text((string)$i, $colNo, $y, function($f){ $f->size(15); $f->color('#111'); });
                                $sheet->text('-', $colKomp, $y, function($f){ $f->size(15); $f->color('#111'); });
                                $sheet->text((string)$komponen, $colUraian, $y, function($f){ $f->size(15); $f->color('#111'); });
                                $sheet->text(is_numeric($subs) ? number_format((float)$subs, 2) : (string)$subs, $colNilai, $y, function($f){ $f->size(15); $f->color('#111'); });
                                $y += 22; $i++;
                            }
                            if ($y > $h - 140) { break; }
                        }

                        // Summary
                        $cmp = $siswa->nilai_computed ?? [];
                        $y = max($y + 16, $h - 120);
                        $sheet->text('Ringkasan:', $left, $y, function($f){ $f->size(16); $f->color('#000'); });
                        $y += 22;
                        if (isset($cmp['total'])) { $sheet->text('Total: ' . $cmp['total'], $left, $y, function($f){ $f->size(15); $f->color('#111'); }); $y += 20; }
                        if (isset($cmp['avg'])) { $sheet->text('Rata-rata: ' . $cmp['avg'], $left, $y, function($f){ $f->size(15); $f->color('#111'); }); $y += 20; }
                        if (isset($cmp['weighted_avg'])) { $sheet->text('Rata-rata Berbobot: ' . $cmp['weighted_avg'], $left, $y, function($f){ $f->size(15); $f->color('#111'); }); $y += 20; }
                        if (isset($cmp['grade'])) { $sheet->text('Predikat: ' . $cmp['grade'], $left, $y, function($f){ $f->size(15); $f->color('#111'); }); }

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
                    $elements = $decoded;
                    Log::info('Elements loaded from database', [
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
        if (empty($elements)) {
            Log::info('Using default elements for template', ['template_id' => $template->id]);
            $elements = [
                [
                    'id' => 1,
                    'type' => 'text',
                    'content' => 'CERTIFICATE',
                    'x' => 50,
                    'y' => 15,
                    'fontSize' => 48,
                    'fontFamily' => 'Arial',
                    'color' => '#1a1a1a',
                    'bold' => true,
                    'align' => 'center'
                ],
                [
                    'id' => 2,
                    'type' => 'text',
                    'content' => 'of appreciation',
                    'x' => 50,
                    'y' => 23,
                    'fontSize' => 20,
                    'fontFamily' => 'Arial',
                    'color' => '#333333',
                    'bold' => false,
                    'align' => 'center'
                ],
                [
                    'id' => 3,
                    'type' => 'text',
                    'content' => 'is presented to',
                    'x' => 50,
                    'y' => 32,
                    'fontSize' => 16,
                    'fontFamily' => 'Arial',
                    'color' => '#666666',
                    'bold' => false,
                    'align' => 'center'
                ],
                [
                    'id' => 4,
                    'type' => 'variable',
                    'variable' => '$nama_siswa',
                    'value' => 'Student Name',
                    'x' => 50,
                    'y' => 50,
                    'fontSize' => 36,
                    'fontFamily' => 'Times New Roman',
                    'color' => '#000000',
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
            return match($element['variable'] ?? '') {
                '$nama_siswa' => $name,
                '$kelas' => $siswa->kelas->nama_kelas ?? '',
                '$nis' => $siswa->nis ?? '',
                '$tanggal' => now()->locale('id')->isoFormat('D MMMM YYYY'),
                '$jurusan' => $siswa->kelas->jurusan->nama_jurusan ?? '',
                '$nilai' => $siswa->nilai ?? '-',
                '$peringkat' => $siswa->peringkat ?? '-',
                '$ttd' => '(Tanda Tangan)',
                default => $element['value'] ?? $element['variable'] ?? ''
            };
        }
        return '';
    }

    /**
     * History page
     */
    public function history()
    {
        // TODO: Implement history tracking in future
        return view('master.sertifikat.history');
    }
}