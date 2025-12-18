<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\TahunAjaran;
use Maatwebsite\Excel\Facades\Excel;

class SiswaImportController extends Controller
{
    /**
     * Tampilkan halaman form import
     */
    public function showImportForm()
    {
        return view('master.siswa.import_upload', [ // ✅ Ganti ke import_upload
            'kelas' => Kelas::all(),
            'jurusan' => Jurusan::all(),
            'tahun_ajaran' => TahunAjaran::all(),
        ]);
    }

    /**
     * Preview data sebelum import
     */
    public function previewImport(Request $request)
{
    // Validasi file
    $request->validate([
        'file_excel' => 'required|mimes:xlsx,xls,csv|max:2048',
        'kelas_id' => 'required|exists:kelas,id',
        'jurusan_id' => 'required|exists:jurusans,id',
        'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
        'tahun_masuk' => 'required|digits:4',
        'status' => 'required|in:Aktif,Lulus,Pindah,Keluar',
    ]);

    try {
        $collection = Excel::toCollection(null, $request->file('file_excel'));

        if ($collection->isEmpty() || $collection[0]->count() <= 1) {
            return back()->with('error', 'File Excel kosong atau hanya berisi header!');
        }

        $rows = $collection[0];
        $header = $rows->first();
        $dataRows = $rows->slice(1);

        $previewData = [];
        $errors = [];
        $rowNumber = 2;

        foreach ($dataRows as $row) {
            // Auto-convert jenis kelamin
            $jenisKelamin = strtoupper(trim($row[2] ?? ''));
            
            if (in_array($jenisKelamin, ['LAKI-LAKI', 'LAKI', 'L', 'MALE', 'M'])) {
                $jenisKelamin = 'L';
            } elseif (in_array($jenisKelamin, ['PEREMPUAN', 'P', 'FEMALE', 'F'])) {
                $jenisKelamin = 'P';
            }

            $rowData = [
                'row_number' => $rowNumber,
                'nis' => $row[0] ?? null,
                'nama' => $row[1] ?? null,
                'jenis_kelamin' => $jenisKelamin, 
                'tempat_lahir' => $row[3] ?? null,
                'tanggal_lahir' => $this->parseDate($row[4] ?? null),
                'agama' => $row[5] ?? null,
                'nama_orang_tua' => $row[6] ?? null,
                'alamat_orang_tua' => $row[7] ?? null,
                'no_hp_orang_tua' => $row[8] ?? null,
                'asal_sekolah' => $row[9] ?? null,
            ];

            $rowErrors = [];
            
            if (empty($rowData['nis'])) {
                $rowErrors[] = 'NIS kosong';
            }
            
            if (empty($rowData['nama'])) {
                $rowErrors[] = 'Nama kosong';
            }
            
            if (!in_array($rowData['jenis_kelamin'], ['L', 'P'])) {
                $rowErrors[] = 'Jenis kelamin tidak valid (harus LAKI-LAKI/PEREMPUAN atau L/P)';
            }

            if (!empty($rowData['nis']) && Siswa::where('nis', $rowData['nis'])->exists()) {
                $rowErrors[] = 'NIS sudah terdaftar di database';
            }

            if (!empty($rowErrors)) {
                $rowData['errors'] = $rowErrors;
                $errors[] = $rowData;
            }

            $previewData[] = $rowData;
            $rowNumber++;
        }

        // 🔥 SIMPAN KE SESSION
        session([
            'preview_siswa' => $previewData,
            'import_settings' => [
                'kelas_id' => $request->kelas_id,
                'jurusan_id' => $request->jurusan_id,
                'tahun_ajaran_id' => $request->tahun_ajaran_id,
                'tahun_masuk' => $request->tahun_masuk,
                'status' => $request->status,
            ]
        ]);

        // 🔥 HITUNG STATISTIK
        $totalRows = count($previewData);
        $errorRows = count($errors);
        $validRows = $totalRows - $errorRows;

        // 🔥 LOAD DATA RELASI
        $kelas = Kelas::findOrFail($request->kelas_id);
        $jurusan = Jurusan::findOrFail($request->jurusan_id);
        $tahunAjaran = TahunAjaran::findOrFail($request->tahun_ajaran_id);

        // ✅ RETURN VIEW DENGAN DATA
        return view('master.siswa.import_preview', [
            'previewData' => $previewData,
            'errors' => $errors,
            'totalRows' => $totalRows,
            'validRows' => $validRows,
            'errorRows' => $errorRows,
            'kelas' => $kelas,
            'jurusan' => $jurusan,
            'tahunAjaran' => $tahunAjaran,
            'tahunMasuk' => $request->tahun_masuk,
            'status' => $request->status,
        ]);

    } catch (\Exception $e) {
        return back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
    }
}

    /**
     * Proses import data ke database
     */
    public function storeImport(Request $request)
    {
        $dataPreview = session('preview_siswa');
        $importSettings = session('import_settings');

        if (!$dataPreview || !$importSettings) {
            return redirect()->route('master.siswa.import')
                           ->with('error', 'Tidak ada data yang dipreview! Silakan upload file lagi.');
        }

        try {
            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($dataPreview as $row) {
                // Skip row yang ada error
                if (isset($row['errors']) && !empty($row['errors'])) {
                    $errorCount++;
                    continue;
                }

                try {
                    Siswa::create([
                        'nis' => $row['nis'],
                        'nama' => $row['nama'],
                        'jenis_kelamin' => $row['jenis_kelamin'],
                        'tempat_lahir' => $row['tempat_lahir'],
                        'tanggal_lahir' => $row['tanggal_lahir'],
                        'agama' => $row['agama'],
                        'nama_orang_tua' => $row['nama_orang_tua'],
                        'alamat_orang_tua' => $row['alamat_orang_tua'],
                        'no_hp_orang_tua' => $row['no_hp_orang_tua'],
                        'asal_sekolah' => $row['asal_sekolah'],
                        'kelas_id' => $importSettings['kelas_id'],
                        'jurusan_id' => $importSettings['jurusan_id'],
                        'tahun_ajaran_id' => $importSettings['tahun_ajaran_id'],
                        'tahun_masuk' => $importSettings['tahun_masuk'],
                        'status' => $importSettings['status'],
                    ]);
                    
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Baris {$row['row_number']}: {$e->getMessage()}";
                }
            }

            // Hapus session
            session()->forget(['preview_siswa', 'import_settings']);

            $message = "Berhasil import {$successCount} siswa";
            if ($errorCount > 0) {
                $message .= ", {$errorCount} data gagal/dilewati";
            }

            return redirect()->route('master.siswa.index')
                           ->with('success', $message)
                           ->with('import_errors', $errors);

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal melakukan import: ' . $e->getMessage());
        }
    }

    /**
     * Batalkan preview dan hapus session
     */
    public function cancelPreview()
    {
        session()->forget(['preview_siswa', 'import_settings']);
        
        return redirect()->route('master.siswa.import')
                       ->with('info', 'Preview dibatalkan');
    }

    /**
     * Download template Excel
     */
    public function downloadTemplate()
    {
        $filename = 'template_import_siswa.xlsx';
        $filePath = storage_path('app/templates/' . $filename);

        if (file_exists($filePath)) {
            return response()->download($filePath);
        }

        return back()->with('error', 'Template tidak ditemukan!');
    }

    /**
     * Helper: Parse tanggal dari berbagai format
     */
    private function parseDate($date)
    {
        if (empty($date)) {
            return null;
        }

        try {
            // Coba parse dari Excel serial date
            if (is_numeric($date)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date)->format('Y-m-d');
            }

            // Coba parse dari string
            return date('Y-m-d', strtotime($date));
        } catch (\Exception $e) {
            return null;
        }
    }
}