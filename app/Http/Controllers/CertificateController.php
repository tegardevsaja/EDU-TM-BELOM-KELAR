<?php

namespace App\Http\Controllers;

use App\Models\CertificateTemplate;
use App\Models\Siswa;
use App\Models\Certificate;
use App\Models\CertificateElement; // ← pindahkan ke sini
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
class CertificateController extends Controller
{
    /**
     * Simpan elements template
     */


  public function saveElements(Request $request, $templateId)
    {
        // Log untuk debugging
        Log::info('🔹 Save Elements dipanggil', [
            'template_id' => $templateId,
            'request_data' => $request->all()
        ]);

        try {
            $template = CertificateTemplate::findOrFail($templateId);
            
            Log::info('✅ Template ditemukan', ['template' => $template->id]);

            // Validasi request
            $request->validate([
                'elements' => 'required|array',
                'elements.*.type' => 'required|in:text,variable',
                'elements.*.x' => 'required|numeric',
                'elements.*.y' => 'required|numeric',
            ]);

            Log::info('✅ Validasi passed');

            // Hapus elemen lama
            $deletedCount = $template->elements()->delete();
            Log::info('🗑️ Elemen lama dihapus', ['count' => $deletedCount]);

            // Simpan elemen baru
            $savedCount = 0;
            foreach ($request->elements as $index => $el) {
                try {
                    // Tentukan field_name berdasarkan type
                    $fieldName = $el['type'] === 'variable' ? ($el['variable'] ?? 'unknown') : 'text';
                    
                    $element = CertificateElement::create([
                        'template_id' => $template->id,
                        'field_name'  => $fieldName,
                        'x_position'  => (float) $el['x'],
                        'y_position'  => (float) $el['y'],
                        'font_size'   => (int) ($el['fontSize'] ?? 16),
                        'font_family' => $el['fontFamily'] ?? 'Arial',
                        'color'       => $el['color'] ?? '#000000',
                        'alignment'   => $el['align'] ?? 'left',
                        'is_bold'     => (bool) ($el['bold'] ?? false),
                        'content'     => $el['content'] ?? null, // untuk type text
                    ]);

                    $savedCount++;
                    Log::info("✅ Elemen #{$index} disimpan", [
                        'id' => $element->id,
                        'field_name' => $fieldName,
                        'position' => ['x' => $el['x'], 'y' => $el['y']]
                    ]);

                } catch (\Exception $e) {
                    Log::error("❌ Gagal menyimpan elemen #{$index}", [
                        'error' => $e->getMessage(),
                        'element' => $el
                    ]);
                }
            }

            // Update kolom elements di tabel certificate_templates (JSON)
            $template->elements = json_encode($request->elements);
            $template->save();

            Log::info('💾 Template JSON updated');

            return response()->json([
                'success' => true,
                'message' => "Berhasil menyimpan {$savedCount} elemen ke database.",
                'saved_count' => $savedCount,
                'template_id' => $template->id,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('❌ Template tidak ditemukan', ['id' => $templateId]);
            return response()->json([
                'success' => false,
                'message' => 'Template tidak ditemukan.'
            ], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ Validasi gagal', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('💥 Error tidak terduga', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview sertifikat dengan data sample
     */
    public function preview($id)
    {
        $template = CertificateTemplate::findOrFail($id);
        $elements = json_decode($template->elements, true) ?? [];
        
        // Data sample untuk preview
        $sampleData = [
            '$nama_siswa' => 'Ahmad Zulfikar',
            '$kelas' => 'XII RPL',
            '$nis' => '12345678',
            '$tanggal' => now()->locale('id')->translatedFormat('d F Y'),
            '$nilai' => '95',
            '$peringkat' => '1',
            '$jurusan' => 'Rekayasa Perangkat Lunak',
            '$ttd' => '(Tanda Tangan)'
        ];

        return view('master.sertifikat.preview_sertifikat', compact('template', 'elements', 'sampleData'));
    }

    /**
     * Generate sertifikat untuk satu siswa
     */
    public function generateSingle(Request $request, $templateId, $siswaId)
    {
        $template = CertificateTemplate::findOrFail($templateId);
        $siswa = Siswa::findOrFail($siswaId);
        $elements = json_decode($template->elements, true) ?? [];
        
        // Data siswa untuk replace variables
        $studentData = [
            '$nama_siswa' => $siswa->nama,
            '$kelas' => $siswa->kelas,
            '$nis' => $siswa->nis,
            '$tanggal' => now()->locale('id')->translatedFormat('d F Y'),
            '$nilai' => $siswa->nilai ?? '-',
            '$peringkat' => $siswa->peringkat ?? '-',
            '$jurusan' => $siswa->jurusan ?? '-',
            '$ttd' => '(Tanda Tangan)'
        ];

        // Generate HTML
        $html = $this->generateCertificateHtml($template, $elements, $studentData);
        
        // Generate PDF
        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif'
            ]);

        $filename = 'sertifikat_' . str_replace(' ', '_', strtolower($siswa->nama)) . '_' . time() . '.pdf';
        $path = 'certificates/' . $filename;
        
        // Simpan ke storage
        Storage::put($path, $pdf->output());

        // Simpan record ke database
        Certificate::create([
            'template_id' => $template->id,
            'siswa_id' => $siswa->id,
            'file_path' => $path,
            'generated_at' => now()
        ]);

        return $pdf->download($filename);
    }

    /**
     * Generate sertifikat untuk multiple siswa
     */
    public function generateBulk(Request $request, $templateId)
    {
        $request->validate([
            'siswa_ids' => 'required|array',
            'siswa_ids.*' => 'exists:siswa,id'
        ]);

        $template = CertificateTemplate::findOrFail($templateId);
        $elements = json_decode($template->elements, true) ?? [];
        $siswaList = Siswa::whereIn('id', $request->siswa_ids)->get();
        
        $generated = [];
        
        foreach ($siswaList as $siswa) {
            $studentData = [
                '$nama_siswa' => $siswa->nama,
                '$kelas' => $siswa->kelas,
                '$nis' => $siswa->nis,
                '$tanggal' => now()->locale('id')->translatedFormat('d F Y'),
                '$nilai' => $siswa->nilai ?? '-',
                '$peringkat' => $siswa->peringkat ?? '-',
                '$jurusan' => $siswa->jurusan ?? '-',
                '$ttd' => '(Tanda Tangan)'
            ];

            $html = $this->generateCertificateHtml($template, $elements, $studentData);
            
            $pdf = Pdf::loadHTML($html)
                ->setPaper('a4', 'landscape')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'defaultFont' => 'sans-serif'
                ]);

            $filename = 'sertifikat_' . str_replace(' ', '_', strtolower($siswa->nama)) . '_' . time() . '.pdf';
            $path = 'certificates/' . $filename;
            
            Storage::put($path, $pdf->output());

            Certificate::create([
                'template_id' => $template->id,
                'siswa_id' => $siswa->id,
                'file_path' => $path,
                'generated_at' => now()
            ]);

            $generated[] = [
                'siswa' => $siswa->nama,
                'file' => $path
            ];
        }

        return response()->json([
            'success' => true,
            'message' => count($generated) . ' sertifikat berhasil digenerate',
            'data' => $generated
        ]);
    }

    /**
     * Generate HTML untuk sertifikat
     */
    private function generateCertificateHtml($template, $elements, $data)
    {
        $backgroundUrl = asset('storage/' . $template->background_image);
        
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                body {
                    width: 297mm;
                    height: 210mm;
                    position: relative;
                    overflow: hidden;
                }
                .certificate-container {
                    width: 100%;
                    height: 100%;
                    position: relative;
                }
                .background {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background-image: url(' . $backgroundUrl . ');
                    background-size: cover;
                    background-position: center;
                }
                .element {
                    position: absolute;
                    transform: translate(-50%, -50%);
                }
            </style>
        </head>
        <body>
            <div class="certificate-container">
                <div class="background"></div>';
        
        foreach ($elements as $element) {
            $content = '';
            
            if ($element['type'] === 'text') {
                $content = $element['content'];
            } elseif ($element['type'] === 'variable') {
                $content = $data[$element['variable']] ?? $element['variable'];
            }
            
            $style = sprintf(
                'left: %s%%; top: %s%%; font-size: %spx; font-family: %s; color: %s; font-weight: %s; text-align: %s;',
                $element['x'],
                $element['y'],
                $element['fontSize'],
                $element['fontFamily'],
                $element['color'],
                $element['bold'] ? 'bold' : 'normal',
                $element['align']
            );
            
            $html .= sprintf(
                '<div class="element" style="%s">%s</div>',
                $style,
                htmlspecialchars($content)
            );
        }
        
        $html .= '
            </div>
        </body>
        </html>';
        
        return $html;
    }

    /**
     * Download sertifikat yang sudah digenerate
     */
    public function download($id)
    {
        $certificate = Certificate::findOrFail($id);
        
        if (!Storage::exists($certificate->file_path)) {
            abort(404, 'File sertifikat tidak ditemukan');
        }

        return Storage::download($certificate->file_path);
    }

    /**
     * Halaman generate bulk dengan pilihan siswa
     */
    public function generateBulkPage($templateId)
    {
        $template = CertificateTemplate::findOrFail($templateId);
        $siswaList = Siswa::orderBy('nama')->get();
        
        return view('certificates.generate-bulk', compact('template', 'siswaList'));
    }
}