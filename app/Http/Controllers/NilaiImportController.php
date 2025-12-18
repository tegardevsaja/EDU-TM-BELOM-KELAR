<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Siswa;
use App\Models\Penilaian;
use App\Models\TahunAjaran;
use App\Models\TemplatePenilaian;
use Maatwebsite\Excel\Facades\Excel;

class NilaiImportController extends Controller
{
    private function firstDataSheet($collection)
    {
        foreach ($collection as $sheet) {
            if ($sheet->count() > 1) { // header + at least 1 row
                return $sheet;
            }
        }
        return null;
    }

    public function importPrakerin(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls,csv|max:4096',
        ]);

        // Create or get template for Prakerin format
        $template = TemplatePenilaian::firstOrCreate(
            ['nama_template' => 'Template Nilai Prakerin (Auto)'],
            [
                'deskripsi' => 'Template otomatis untuk import nilai Prakerin/DUDI',
                'komponen' => [
                    ['nama' => 'Kedisiplinan', 'bobot' => 25],
                    ['nama' => 'Kompetensi Kerja', 'bobot' => 35],
                    ['nama' => 'Kemampuan Beradaptasi', 'bobot' => 25],
                    ['nama' => 'Lain-lain', 'bobot' => 15],
                ],
                'created_by' => Auth::id(),
                'visibility' => 'all'
            ]
        );

        $collection = Excel::toCollection(null, $request->file('file_excel'));
        $sheet = $this->firstDataSheet($collection);
        if (!$sheet) {
            return back()->with('error', 'File kosong atau hanya berisi header');
        }

        $rows = $sheet;
        $header = $rows->first();
        $dataRows = $rows->slice(1)->filter(function($row) {
            return collect($row)->filter(fn($v) => !is_null($v) && trim((string)$v) !== '')->isNotEmpty();
        });

        $activeYearId = TahunAjaran::where('aktif', true)->value('id') ?? TahunAjaran::latest('id')->value('id');

        $success = 0; $skipped = 0; $skippedNoKey = 0; $skippedNotFound = 0;

        // Map indices from header for Prakerin (support our dummy CSV labels)
        $iNama = $this->headerIndex($header, ['nama']);
        $iNis = $this->headerIndex($header, ['nis']);
        $iKelas = $this->headerIndex($header, ['kelas']);
        $iJudul = $this->headerIndex($header, ['judul_laporan','judul laporan']);

        // Kategori: kedisiplinan_
        $iKedisiplinan = [
            'Kehadiran, Waktu/Disiplin' => $this->headerIndex($header, ['kedisiplinan_kehadiran']),
            'Sikap Kerja/Prosedur Kerja' => $this->headerIndex($header, ['kedisiplinan_sikap_kerja']),
            'Tanggung Jawab Terhadap Tugas' => $this->headerIndex($header, ['kedisiplinan_tanggung_jawab']),
            'Kehadiran/Absensi' => $this->headerIndex($header, ['kedisiplinan_absensi']),
        ];
        $iKompetensi = [
            'Kemampuan Kerja' => $this->headerIndex($header, ['kompetensi_kemampuan_kerja']),
            'Keterampilan Kerja' => $this->headerIndex($header, ['kompetensi_keterampilan_kerja']),
            'Kualitas Hasil Kerja' => $this->headerIndex($header, ['kompetensi_kualitas_hasil']),
        ];
        $iAdaptasi = [
            'Kemampuan Berkomunikasi' => $this->headerIndex($header, ['adaptasi_komunikasi']),
            'Kerjasama' => $this->headerIndex($header, ['adaptasi_kerjasama']),
            'Kreatif/Inisiatif' => $this->headerIndex($header, ['adaptasi_kreatif']),
        ];
        $iLain = [
            'Memiliki rasa percaya diri' => $this->headerIndex($header, ['lainnya_percaya_diri']),
            'Mematuhi peraturan dan tata tertib' => $this->headerIndex($header, ['lainnya_tata_tertib']),
            'Penampilan / Kerapihan' => $this->headerIndex($header, ['lainnya_kerapihan']),
        ];

        foreach ($dataRows as $row) {
            $nama = $row[$iNama ?? 0] ?? null;
            $nis = $row[$iNis ?? 1] ?? null;
            $judul = $row[$iJudul ?? 3] ?? null;

            $nisNorm = $this->normalizeNis($nis);
            $nameNorm = $this->normalizeName($nama);
            if (empty($nisNorm) && empty($nameNorm)) { $skipped++; $skippedNoKey++; continue; }

            $siswa = null;
            if (!empty($nisNorm)) {
                $nisTrim = ltrim($nisNorm, '0');
                $siswa = Siswa::whereIn('nis', array_values(array_unique(array_filter([$nisNorm,$nisTrim]))))->first();
            }
            if (!$siswa && !empty($nameNorm)) {
                $siswa = Siswa::whereRaw('LOWER(TRIM(REPLACE(nama, "  ", " "))) = ?', [$nameNorm])
                    ->orWhere('nama', 'like', '%' . ($nama ?? '') . '%')->first();
            }
            if (!$siswa) { $skipped++; $skippedNotFound++; continue; }

            $mapFrom = function($row, $indexMap) {
                $out = [];
                foreach ($indexMap as $label => $idx) {
                    $val = $idx !== null ? ($row[$idx] ?? null) : null;
                    if ($val !== null && $val !== '') {
                        $num = is_numeric($val) ? (float)$val : null;
                        if ($num !== null) { $out[$label] = $num; }
                    }
                }
                return $out;
            };

            $nilaiDetail = [
                'format' => 'PRAKERIN',
                'judul_laporan' => $judul,
                'KEDISIPLINAN' => $mapFrom($row, $iKedisiplinan),
                'KOMPETENSI KERJA' => $mapFrom($row, $iKompetensi),
                'KEMAMPUAN BERADAPTASI' => $mapFrom($row, $iAdaptasi),
                'LAIN-LAIN' => $mapFrom($row, $iLain),
            ];

            Penilaian::updateOrCreate(
                [
                    'siswa_id' => $siswa->id,
                    'template_id' => $template->id,
                ],
                [
                    'jenis_penilaian' => 'Uji DUDI',
                    'guru_id' => Auth::id(),
                    'nilai_detail' => $nilaiDetail,
                    'visibility' => 'all',
                    'tanggal_input' => now(),
                    'tahun_ajaran_id' => $activeYearId,
                ]
            );
            $success++;
        }

        $msg = "Import Prakerin selesai: {$success} berhasil, {$skipped} dilewati (tanpa kunci: {$skippedNoKey}, siswa tidak ditemukan: {$skippedNotFound})";
        return redirect()->route('master.nilai.index')->with('success', $msg);
    }

    private function normalizeNis($value)
    {
        if ($value === null) return null;
        $str = trim((string)$value);
        // If Excel numeric, drop decimals
        if (is_numeric($str)) {
            $str = (string)(int)$str;
        }
        return $str;
    }

    private function normalizeName($value)
    {
        if ($value === null) return null;
        $s = trim(preg_replace('/\s+/', ' ', (string)$value));
        return mb_strtolower($s);
    }

    private function normalizeNisn($value)
    {
        return $this->normalizeNis($value);
    }

    private function headerIndex($headerRow, array $candidates)
    {
        // Return index of first header cell that matches any candidate (case-insensitive, trims)
        $map = [];
        foreach ($headerRow as $idx => $cell) {
            $label = mb_strtolower(trim((string)$cell));
            $map[$idx] = $label;
        }
        foreach ($map as $idx => $label) {
            foreach ($candidates as $cand) {
                $candNorm = mb_strtolower(trim($cand));
                if ($label === $candNorm) return $idx;
                // loose contains match
                if ($candNorm && str_contains($label, $candNorm)) return $idx;
            }
        }
        return null;
    }
    public function index()
    {
        return view('master.nilai.import.index');
    }

    public function importTA(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls,csv|max:4096',
        ]);

        // Create or get template for TA format
        $template = TemplatePenilaian::firstOrCreate(
            ['nama_template' => 'Template Nilai TA (Auto)'],
            [
                'deskripsi' => 'Template otomatis untuk import nilai Tugas Akhir',
                'komponen' => [
                    ['nama' => 'Nilai Praktek', 'bobot' => 40],
                    ['nama' => 'Nilai Sikap', 'bobot' => 60],
                ],
                'created_by' => Auth::id(),
                'visibility' => 'all'
            ]
        );

        $collection = Excel::toCollection(null, $request->file('file_excel'));
        $sheet = $this->firstDataSheet($collection);
        if (!$sheet) {
            return back()->with('error', 'File kosong atau hanya berisi header');
        }

        $rows = $sheet;
        $header = $rows->first();
        $dataRows = $rows->slice(1)->filter(function($row) {
            return collect($row)->filter(fn($v) => !is_null($v) && trim((string)$v) !== '')->isNotEmpty();
        });

        $activeYearId = TahunAjaran::where('aktif', true)->value('id') ?? TahunAjaran::latest('id')->value('id');

        $success = 0;
        $skipped = 0;
        $skippedNoKey = 0;
        $skippedNotFound = 0;
        // Map indices from header for TA
        $idxNo = $this->headerIndex($header, ['NO', 'No']);
        $idxNama = $this->headerIndex($header, ['NAMA', 'Nama siswa', 'Nama']);
        $idxNis = $this->headerIndex($header, ['NIS']);
        $idxNisn = $this->headerIndex($header, ['NISN']);
        $idxJurusan = $this->headerIndex($header, ['JURUSAN']);
        $idxProject = $this->headerIndex($header, ['PROJECT','Project']);
        $idxInstansi1 = $this->headerIndex($header, ['INSTANSI 1','Instansi 1']);
        $idxKota1 = $this->headerIndex($header, ['KOTA 1','Kota 1']);
        $idxNp = $this->headerIndex($header, ['NP']);
        $idxNs1 = $this->headerIndex($header, ['NS1']);
        $idxNs2 = $this->headerIndex($header, ['NS2']);
        $idxNs3 = $this->headerIndex($header, ['NS3']);
        $idxNs4 = $this->headerIndex($header, ['NS4']);
        $idxNs5 = $this->headerIndex($header, ['NS5']);
        $idxNs6 = $this->headerIndex($header, ['NS6']);
        $idxJmlNs = $this->headerIndex($header, ['JML NS','Jumlah NS','JML_NS']);
        $idxNa = $this->headerIndex($header, ['NA','Nilai Akhir']);
        $idxHuruf = $this->headerIndex($header, ['HURUF']);
        $idxPredikat = $this->headerIndex($header, ['PREDIKAT']);

        foreach ($dataRows as $row) {
            $no = $row[$idxNo ?? 0] ?? ($row[0] ?? null);
            $nama = $row[$idxNama ?? 1] ?? ($row[1] ?? null);
            $nis = $row[$idxNis ?? 2] ?? ($row[2] ?? null);
            $nisn = $row[$idxNisn ?? 3] ?? ($row[3] ?? null);
            $jurusan = $row[$idxJurusan ?? 4] ?? ($row[4] ?? null);
            $project = $row[$idxProject ?? 5] ?? ($row[5] ?? null);
            $instansi1 = $row[$idxInstansi1 ?? 6] ?? ($row[6] ?? null);
            $kota1 = $row[$idxKota1 ?? 7] ?? ($row[7] ?? null);
            $np = $row[$idxNp ?? 8] ?? ($row[8] ?? null);
            $ns1 = $row[$idxNs1 ?? 9] ?? ($row[9] ?? null);
            $ns2 = $row[$idxNs2 ?? 10] ?? ($row[10] ?? null);
            $ns3 = $row[$idxNs3 ?? 11] ?? ($row[11] ?? null);
            $ns4 = $row[$idxNs4 ?? 12] ?? ($row[12] ?? null);
            $ns5 = $row[$idxNs5 ?? 13] ?? ($row[13] ?? null);
            $ns6 = $row[$idxNs6 ?? 14] ?? ($row[14] ?? null);
            $jml_ns = $row[$idxJmlNs ?? 15] ?? ($row[15] ?? null);
            $na = $row[$idxNa ?? 16] ?? ($row[16] ?? null);
            $huruf = $row[$idxHuruf ?? 17] ?? ($row[17] ?? null);
            $predikat = $row[$idxPredikat ?? 18] ?? ($row[18] ?? null);
            // Prefer matching by NIS (DB has `nis`). Fallback to name (case-insensitive).
            $nisNorm = $this->normalizeNis($nis);
            $nameNorm = $this->normalizeName($nama);
            if (empty($nisNorm) && empty($nameNorm)) { $skipped++; $skippedNoKey++; continue; }

            $siswa = null;
            if (!empty($nisNorm)) {
                $nisTrimZeros = ltrim($nisNorm, '0');
                $siswa = Siswa::whereIn('nis', array_values(array_unique(array_filter([
                    $nisNorm,
                    $nisTrimZeros,
                ]))))->first();
            }
            if (!$siswa && !empty($nameNorm)) {
                $siswa = Siswa::whereRaw('LOWER(TRIM(REPLACE(nama, "  ", " "))) = ?', [$nameNorm])
                    ->orWhere('nama', 'like', '%' . ($nama ?? '') . '%')->first();
            }
            if (!$siswa) { $skipped++; $skippedNotFound++; continue; }

            $nilaiDetail = [
                'format' => 'TA',
                'row' => [
                    'no' => $no,
                    'nama' => $nama,
                    'nis' => $nis,
                    'nisn' => $nisn,
                    'jurusan' => $jurusan,
                    'project' => $project,
                    'instansi1' => $instansi1,
                    'kota1' => $kota1,
                    'np' => $np,
                    'ns1' => $ns1,
                    'ns2' => $ns2,
                    'ns3' => $ns3,
                    'ns4' => $ns4,
                    'ns5' => $ns5,
                    'ns6' => $ns6,
                    'jml_ns' => $jml_ns,
                    'na' => $na,
                    'huruf' => $huruf,
                    'predikat' => $predikat,
                ],
            ];

            Penilaian::updateOrCreate(
                [
                    'siswa_id' => $siswa->id,
                    'template_id' => $template->id,
                ],
                [
                    'jenis_penilaian' => 'TA',
                    'guru_id' => Auth::id(),
                    'nilai_detail' => $nilaiDetail,
                    'visibility' => 'all',
                    'tanggal_input' => now(),
                    'tahun_ajaran_id' => $activeYearId,
                ]
            );
            $success++;
        }

        $msg = "Import selesai: {$success} berhasil, {$skipped} dilewati (tanpa kunci: {$skippedNoKey}, siswa tidak ditemukan: {$skippedNotFound})";
        return redirect()->route('master.nilai.index')->with('success', $msg);
    }

    public function importUKK(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls,csv|max:4096',
        ]);

        // Create or get template for UKK DUDI format
        $template = TemplatePenilaian::firstOrCreate(
            ['nama_template' => 'Template Nilai UKK DUDI (Auto)'],
            [
                'deskripsi' => 'Template otomatis untuk import nilai UKK DUDI',
                'komponen' => [
                    ['nama' => 'Nilai Akhir', 'bobot' => 100],
                ],
                'created_by' => Auth::id(),
                'visibility' => 'all'
            ]
        );

        $collection = Excel::toCollection(null, $request->file('file_excel'));
        $sheet = $this->firstDataSheet($collection);
        if (!$sheet) {
            return back()->with('error', 'File kosong atau hanya berisi header');
        }

        $rows = $sheet;
        $header = $rows->first();
        $dataRows = $rows->slice(1)->filter(function($row) {
            return collect($row)->filter(fn($v) => !is_null($v) && trim((string)$v) !== '')->isNotEmpty();
        });

        $activeYearId = TahunAjaran::where('aktif', true)->value('id') ?? TahunAjaran::latest('id')->value('id');

        $success = 0;
        $skipped = 0;
        $skippedNoKey = 0;
        $skippedNotFound = 0;
        // Map indices from header for UKK Dudi
        $uNo = $this->headerIndex($header, ['no','No']);
        $uNoPeserta = $this->headerIndex($header, ['No Peserta','no peserta','no_peserta']);
        $uNama = $this->headerIndex($header, ['nama siswa','NAMA','Nama']);
        $uNisn = $this->headerIndex($header, ['NISN','nisn']);
        $uPredikat = $this->headerIndex($header, ['predikat']);
        $uPredikatEng = $this->headerIndex($header, ['predikat english','predikat inggris','predikat_en']);
        $uNilaiAkhir = $this->headerIndex($header, ['Nilai Akhir','nilai akhir','NA']);

        foreach ($dataRows as $row) {
            $no = $row[$uNo ?? 0] ?? ($row[0] ?? null);
            $no_peserta = $row[$uNoPeserta ?? 1] ?? ($row[1] ?? null);
            $nama = $row[$uNama ?? 2] ?? ($row[2] ?? null);
            $nisn = $row[$uNisn ?? 3] ?? ($row[3] ?? null);
            $predikat_id = $row[$uPredikat ?? 4] ?? ($row[4] ?? null);
            $predikat_en = $row[$uPredikatEng ?? 5] ?? ($row[5] ?? null);
            $nilai_akhir = $row[$uNilaiAkhir ?? 6] ?? ($row[6] ?? null);

            // Cocokkan dengan NISN (lebih akurat), fallback ke nama jika NISN tidak ada atau tidak ketemu
            $nisnNorm = $this->normalizeNisn($nisn);
            $nameNorm = $this->normalizeName($nama);
            if (empty($nisnNorm) && empty($nameNorm)) { $skipped++; $skippedNoKey++; continue; }

            $siswa = null;
            if (!empty($nisnNorm)) {
                $siswa = Siswa::where('nisn', $nisnNorm)->first();
            }
            if (!$siswa && !empty($nameNorm)) {
                $siswa = Siswa::whereRaw('LOWER(TRIM(REPLACE(nama, "  ", " "))) = ?', [$nameNorm])
                    ->orWhere('nama', 'like', '%' . ($nama ?? '') . '%')->first();
            }
            if (!$siswa) { $skipped++; $skippedNotFound++; continue; }

            $nilaiDetail = [
                'format' => 'Uji DUDI',
                'row' => [
                    'no' => $no,
                    'no_peserta' => $no_peserta,
                    'nama' => $nama,
                    'nisn' => $nisn,
                    'predikat' => $predikat_id,
                    'predikat_english' => $predikat_en,
                    'nilai_akhir' => $nilai_akhir,
                ],
            ];

            Penilaian::updateOrCreate(
                [
                    'siswa_id' => $siswa->id,
                    'template_id' => $template->id,
                ],
                [
                    'jenis_penilaian' => 'Uji DUDI',
                    'guru_id' => Auth::id(),
                    'nilai_detail' => $nilaiDetail,
                    'visibility' => 'all',
                    'tanggal_input' => now(),
                    'tahun_ajaran_id' => $activeYearId,
                ]
            );
            $success++;
        }

        $msg = "Import selesai: {$success} berhasil, {$skipped} dilewati (tanpa kunci: {$skippedNoKey}, siswa tidak ditemukan: {$skippedNotFound})";
        return redirect()->route('master.nilai.index')->with('success', $msg);
    }
}
