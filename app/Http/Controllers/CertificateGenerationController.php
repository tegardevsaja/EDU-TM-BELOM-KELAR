<?php

namespace App\Http\Controllers;

use App\Models\CertificateTemplate;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use ZipArchive;
use Illuminate\Support\Str;

class CertificateGenerationController extends Controller
{
    /**
     * STEP 1: Pilih Template
     */
    public function stepSelectTemplate()
    {
        $templates = CertificateTemplate::all(); 
        return view('master.sertifikat.select_template', compact('templates'));
    }

    /**
     * STEP 2: Pilih Template Nilai (opsional)
     */
    public function stepSelectGrade($template_id)
    {
        return redirect()->route('master.sertifikat.generate.select-class', $template_id);
    }

    /**
     * STEP 3: Pilih Kelas
     */
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
        
        if ($kelas_id) {
            $kelas = Kelas::with('siswas')->findOrFail($kelas_id);
            $siswaList = $kelas->siswas;
        } else {
            $siswaList = Siswa::all();
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

        return view('master.sertifikat.customize', compact('template', 'siswaList', 'elements', 'kelas'));
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

        if (!$kelas_id) {
            $kelas = null;
            $siswas = Siswa::with('kelas.jurusan')->get();
        } else {
            $kelas = Kelas::with('siswas.kelas.jurusan')->findOrFail($kelas_id);
            $siswas = $kelas->siswas;
        }

        // Ambil elements yang sudah disimpan
        $elements = $this->getTemplateElements($template);
        
        // Log untuk debugging
        Log::info('Preview certificate', [
            'template_id' => $template_id,
            'elements_count' => count($elements),
            'elements_sample' => array_slice($elements, 0, 2),
            'siswa_count' => $siswas->count()
        ]);

        return view('master.sertifikat.preview', compact('template', 'kelas', 'siswas', 'elements'));
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
                'format' => 'required|in:pdf,png,jpg',
            ]);

            $template = CertificateTemplate::findOrFail($request->template_id);
            
            $kelas_id = $request->kelas_id;
            
            // Get siswa berdasarkan kelas atau semua
            if ($kelas_id) {
                $kelas = Kelas::findOrFail($kelas_id);
                $siswas = Siswa::where('kelas_id', $kelas->id)->with('kelas.jurusan')->get();
                $filename_suffix = Str::slug($kelas->nama_kelas);
            } else {
                $kelas = null;
                $siswas = Siswa::with('kelas.jurusan')->get();
                $filename_suffix = 'semua_siswa';
            }

            // Check jika tidak ada siswa
            if ($siswas->isEmpty()) {
                return back()->with('error', 'Tidak ada siswa yang ditemukan.');
            }

            $format = $request->format;
            
            // Ambil elements dari database
            $elements = $this->getTemplateElements($template);

            Log::info('Generate certificates started', [
                'template_id' => $template->id,
                'template_name' => $template->nama_template,
                'format' => $format,
                'elements_count' => count($elements),
                'siswa_count' => $siswas->count(),
                'kelas' => $kelas ? $kelas->nama_kelas : 'Semua'
            ]);

            // Generate PDF (satukan semua dalam 1 file)
            if ($format === 'pdf') {
                return $this->generatePDF($template, $siswas, $elements, $filename_suffix);
            }

            // Generate PNG atau JPG (per siswa, di-zip)
            if (in_array($format, ['png', 'jpg'])) {
                return $this->generateImagesAndZip($template, $siswas, $elements, $format, $filename_suffix);
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
                    $content = $this->getElementContent($element, $siswa, $siswa->nama);
                    
                    // Skip jika content kosong
                    if (empty(trim($content))) {
                        continue;
                    }

                    // Konversi persentase ke pixel
                    $x = (int)(($element['x'] / 100) * $width);
                    $y = (int)(($element['y'] / 100) * $height);
                    $fontSize = $element['fontSize'] ?? 24;
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
                } elseif ($format === 'jpg') {
                    $filePath = $tempDir . '/' . $siswaFilename . '.jpg';
                    $image->toJpeg(90)->save($filePath);
                }

                $files[] = $filePath;

                Log::info("Certificate saved", [
                    'file' => basename($filePath),
                    'size' => filesize($filePath)
                ]);
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
                return response()->download($zipPath, $zipFilename)
                    ->deleteFileAfterSend(true)
                    ->withHeaders([
                        'Content-Type' => 'application/zip',
                    ])
                    ->afterSending(function() use ($files, $tempDir) {
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
            'Arial' => $bold ? 'arial-bold.ttf' : 'arial.ttf',
            'Times New Roman' => $bold ? 'times-bold.ttf' : 'times.ttf',
            'Georgia' => $bold ? 'georgia-bold.ttf' : 'georgia.ttf',
            'Courier New' => $bold ? 'courier-bold.ttf' : 'courier.ttf',
            'Verdana' => $bold ? 'verdana-bold.ttf' : 'verdana.ttf',
        ];

        $fontFile = $fontMap[$fontFamily] ?? 'arial.ttf';
        $fontPath = public_path("fonts/{$fontFile}");

        // Fallback ke regular jika bold tidak ada
        if (!file_exists($fontPath) && $bold) {
            $regularFont = str_replace('-bold', '', $fontFile);
            $regularPath = public_path("fonts/{$regularFont}");
            if (file_exists($regularPath)) {
                Log::info('Using regular font as fallback', [
                    'requested' => $fontFile,
                    'fallback' => $regularFont
                ]);
                return $regularPath;
            }
        }

        // Fallback ke arial jika font tidak ditemukan
        if (!file_exists($fontPath)) {
            $arialPath = public_path("fonts/arial.ttf");
            if (file_exists($arialPath)) {
                Log::warning('Font not found, using Arial', [
                    'requested' => $fontFile,
                    'fallback' => 'arial.ttf'
                ]);
                return $arialPath;
            }
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