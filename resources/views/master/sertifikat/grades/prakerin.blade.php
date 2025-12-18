@php
    $cmp = $siswa->nilai_computed ?? [];
    $map = $siswa->nilai_detail_map ?? [];
@endphp

<style>
    @page {
        size: A4 landscape;
        margin: 0;
    }
    body {
        margin: 0;
        padding: 0;
    }
    .certificate-page {
        width: 297mm;
        height: 210mm;
        position: relative;
        page-break-after: always;
    }
</style>

<div class="certificate-page">
    <div style="position:absolute; top: 8mm; left: 10mm; right: 10mm; bottom: 8mm; font-family: Arial, sans-serif; border: 2px solid #000; padding: 5mm;">
        
        {{-- Header --}}
        <div style="text-align:center; margin-bottom: 3mm;">
            <div style="font-size:10pt; font-weight:bold; text-decoration: underline; margin-bottom: 0.5mm;">
                DAFTAR NILAI PRAKTIK KERJA INDUSTRI (PRAKERIN)
            </div>
            <div style="font-size:9pt; font-weight:bold; margin-bottom: 0.5mm;">
                SEKOLAH MENENGAH KEJURUAN (SMK) TUNAS MEDIA KOTA DEPOK
            </div>
            <div style="font-size:8pt;">
                TAHUN PELAJARAN {{ now()->format('Y') }}/{{ now()->addYear()->format('Y') }}
            </div>
        </div>

        {{-- Tabel Identitas --}}
        <table style="width:100%; border-collapse: collapse; font-size:8pt; margin-bottom:3mm;">
            <tr>
                <td style="width:30%; border:1px solid #666; padding:1mm 2mm; background-color:#e0e0e0;">Nama Peserta Prakerin</td>
                <td style="border:1px solid #666; padding:1mm 2mm;">{{ $siswa->nama ?? 'Adhaf Dewo Wicaksono' }}</td>
            </tr>
            <tr>
                <td style="border:1px solid #666; padding:1mm 2mm; background-color:#e0e0e0;">Nomor Induk Siswa (NIS)</td>
                <td style="border:1px solid #666; padding:1mm 2mm;">{{ $siswa->nis ?? '22-10-001' }}</td>
            </tr>
            <tr>
                <td style="border:1px solid #666; padding:1mm 2mm; background-color:#e0e0e0;">Kelas / Kompetensi Keahlian</td>
                <td style="border:1px solid #666; padding:1mm 2mm;">
                    {{ $siswa->kelas->nama_kelas ?? 'XI' }} / {{ $siswa->kelas->jurusan->nama_jurusan ?? 'Multimedia' }}
                </td>
            </tr>
            <tr>
                <td style="border:1px solid #666; padding:1mm 2mm; background-color:#e0e0e0;">Judul Laporan</td>
                <td style="border:1px solid #666; padding:1mm 2mm;">
                    {{ $siswa->judul_laporan ?? 'Membuat Desain Logo Perusahaan Daerah dan Pembuatan Video' }}
                </td>
            </tr>
        </table>

        {{-- Tabel Penilaian --}}
        @php
            $toLetter = function($v) {
                if ($v === null) return '';
                if (!is_numeric($v)) return '';
                $n = (float) $v;
                return $n >= 90 ? 'A' : ($n >= 75 ? 'B' : ($n >= 60 ? 'C' : 'D'));
            };
        @endphp
        <table style="width:100%; border-collapse: collapse; font-size:7.5pt;">
            <thead>
                <tr style="background-color:#d0d0d0;">
                    <th style="border:1px solid #000; padding:1.5mm; width:18%; text-align:center; font-weight:bold; font-size:7pt;">KOMPONEN<br>PENILAIAN</th>
                    <th style="border:1px solid #000; padding:1.5mm; text-align:center; font-weight:bold; font-size:7pt;">URAIAN</th>
                    <th style="border:1px solid #000; padding:1.5mm; width:9%; text-align:center; font-weight:bold; font-size:7pt;">ANGKA</th>
                    <th style="border:1px solid #000; padding:1.5mm; width:10%; text-align:center; font-weight:bold; font-size:7pt;">NILAI HURUF</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $komponenData = [];
                    if (isset($map) && is_array($map) && !empty($map)) {
                        $komponenData = $map; // gunakan data import
                    } elseif (isset($siswa->nilai_detail_map) && is_array($siswa->nilai_detail_map) && !empty($siswa->nilai_detail_map)) {
                        $komponenData = $siswa->nilai_detail_map;
                    } else {
                        // fallback demo
                        $komponenData = [
                            'KEDISIPLINAN' => [
                                'Kehadiran, Waktu/Disiplin' => 85,
                                'Sikap Kerja/Prosedur Kerja' => 90,
                                'Tanggung Jawab Terhadap Tugas' => 90,
                                'Kehadiran/Absensi' => 90,
                            ],
                            'KOMPETENSI KERJA' => [
                                'Kemampuan Kerja' => 90,
                                'Keterampilan Kerja' => 85,
                                'Kualitas Hasil Kerja' => 85,
                            ],
                            'KEMAMPUAN BERADAPTASI' => [
                                'Kemampuan Berkomunikasi' => 85,
                                'Kerjasama' => 90,
                                'Kreatif/Inisiatif' => 90,
                            ],
                            'LAIN-LAIN' => [
                                'Memiliki rasa percaya diri' => 85,
                                'Mematuhi peraturan dan tata tertib' => 90,
                                'Penampilan / Kerapihan' => 90,
                            ],
                        ];
                    }
                @endphp

                @php
                    // hitung ulang total dan rata-rata dari komponen untuk memastikan akurat
                    $__allNums = [];
                    foreach ($komponenData as $__subs) {
                        if (is_array($__subs)) {
                            foreach ($__subs as $__v) {
                                if (is_numeric($__v)) { $__allNums[] = (float) $__v; }
                            }
                        } elseif (is_numeric($__subs)) {
                            $__allNums[] = (float) $__subs;
                        }
                    }
                    $__total = count($__allNums) ? array_sum($__allNums) : null;
                    $__avg   = count($__allNums) ? round($__total / count($__allNums), 2) : null;
                    $__grade = ($__avg === null) ? '' : ($__avg >= 90 ? 'A' : ($__avg >= 75 ? 'B' : ($__avg >= 60 ? 'C' : 'D')));
                @endphp

                @foreach($komponenData as $komponen => $items)
                    @continue(!is_array($items))
                    @php $isFirst = true; $rowspan = is_array($items) ? count($items) : 0; @endphp
                    @foreach($items as $uraian => $nilai)
                        <tr>
                            @if($isFirst)
                                <td style="border:1px solid #000; padding:1mm; text-align:center; vertical-align:middle;" rowspan="{{ $rowspan }}">
                                    {{ $komponen }}
                                </td>
                                @php $isFirst = false; @endphp
                            @endif
                            <td style="border:1px solid #000; padding:1mm;">{{ $uraian }}</td>
                            <td style="border:1px solid #000; padding:1mm; text-align:center;">{{ is_numeric($nilai) ? number_format((float)$nilai, 2) : '' }}</td>
                            <td style="border:1px solid #000; padding:1mm; text-align:center;">{{ is_numeric($nilai) ? $toLetter($nilai) : '' }}</td>
                        </tr>
                    @endforeach
                @endforeach

                @php
                    // jika dataset menyediakan baris sidang, coba render terpisah
                    $sidangRow = null;
                    if (isset($komponenData['SIDANG LAPORAN PRAKERIN']) && is_array($komponenData['SIDANG LAPORAN PRAKERIN'])) {
                        $sidArr = $komponenData['SIDANG LAPORAN PRAKERIN'];
                        // ambil angka pertama sebagai nilai sidang
                        $sidangRow = collect($sidArr)->first();
                    }
                @endphp
                @if($sidangRow !== null)
                    <tr>
                        <td style="border:1px solid #000; padding:1mm; text-align:center;">SIDANG LAPORAN PRAKERIN</td>
                        <td style="border:1px solid #000; padding:1mm;"></td>
                        <td style="border:1px solid #000; padding:1mm; text-align:center;">{{ $sidangRow }}</td>
                        <td style="border:1px solid #000; padding:1mm; text-align:center;">{{ $toLetter($sidangRow) }}</td>
                    </tr>
                @endif
                <tr style="background-color:#e8e8e8; font-weight:bold;">
                    <td style="border:1px solid #000; padding:1mm; text-align:center;" colspan="2">TOTAL NILAI</td>
                    <td style="border:1px solid #000; padding:1mm; text-align:center;">{{ $__total !== null ? $__total : '' }}</td>
                    <td style="border:1px solid #000; padding:1mm; text-align:center;"></td>
                </tr>
                <tr style="background-color:#e8e8e8; font-weight:bold;">
                    <td style="border:1px solid #000; padding:1mm; text-align:center;" colspan="2">RATA-RATA</td>
                    <td style="border:1px solid #000; padding:1mm; text-align:center;">{{ $__avg !== null ? $__avg : '' }}</td>
                    <td style="border:1px solid #000; padding:1mm; text-align:center;">{{ $__avg !== null ? $toLetter($__avg) : '' }}</td>
                </tr>
                <tr style="background-color:#e8e8e8; font-weight:bold;">
                    <td style="border:1px solid #000; padding:1mm; text-align:center;" colspan="2">PREDIKAT</td>
                    <td style="border:1px solid #000; padding:1mm; text-align:center;" colspan="2">{{ $__grade }}</td>
                </tr>
            </tbody>
        </table>

      {{-- Footer Keterangan Nilai & Tanda Tangan --}}
<table style="width:100%; margin-top:3mm; font-size:7.5pt; border-collapse:collapse;">
    <tr>
        {{-- Keterangan Nilai --}}
        <td style="width:50%; vertical-align:top;">
            <div style="font-weight:bold; margin-bottom:1mm;">Keterangan Nilai</div>
            <table style="width:100%; border-collapse: collapse; font-size:7pt;">
                <tr><td style="border:1px solid #000; padding:1mm;">90 – 100</td><td style="border:1px solid #000; padding:1mm;">A (Amat Baik)</td></tr>
                <tr><td style="border:1px solid #000; padding:1mm;">75 – 89</td><td style="border:1px solid #000; padding:1mm;">B (Baik)</td></tr>
                <tr><td style="border:1px solid #000; padding:1mm;">60 – 74</td><td style="border:1px solid #000; padding:1mm;">C (Cukup)</td></tr>
                <tr><td style="border:1px solid #000; padding:1mm;">< 60</td><td style="border:1px solid #000; padding:1mm;">D (Kurang)</td></tr>
            </table>
        </td>

        {{-- Tanda Tangan --}}
        @php
            // Debug: pastikan $signatures ada
            if (!isset($signatures) || !$signatures) {
                $signatures = \App\Models\GradeSignature::first();
            }
            
            $leftLabel = ($signatures && $signatures->left_label) ? $signatures->left_label : 'Penguji Internal';
            $leftName  = ($signatures && $signatures->left_name) ? $signatures->left_name : 'Agus Lukman AH, S.Kom.';
            $leftOrg   = ($signatures && $signatures->left_org) ? $signatures->left_org : 'SMK Tunas Media';
            $leftNik = '';
            if (!empty($leftOrg) && str_contains($leftOrg, 'NIK:')) { $parts = explode('NIK:', $leftOrg); $leftNik = trim($parts[1] ?? ''); $leftOrg = trim($parts[0]); }
            $rightLabel = ($signatures && $signatures->right_label) ? $signatures->right_label : 'Ketua Panitia';
            $rightName  = ($signatures && $signatures->right_name) ? $signatures->right_name : 'Agus Lukman AH, S.Kom.';
            $rightOrg   = ($signatures && $signatures->right_org) ? $signatures->right_org : '';
            $rightNik = '';
            if (!empty($rightOrg) && str_contains($rightOrg, 'NIK:')) { $parts = explode('NIK:', $rightOrg); $rightNik = trim($parts[1] ?? ''); $rightOrg = trim($parts[0]); }
            $city       = ($signatures && $signatures->city) ? $signatures->city : 'Kota Depok';
        @endphp

        <td style="width:50%; vertical-align:top; text-align:center;">
            <div style="font-weight:bold; margin-bottom:1mm;">Ketua Panitia</div>
            <div style="margin-bottom:0.5mm;">
                {{ $city }}, {{ now()->locale('id')->isoFormat('D MMMM YYYY') }}
            </div>
            <div style="margin-bottom:2mm;">Ketua Panitia,</div>
            @if($signatures && $signatures->right_signature_path)
                @php
                    $rightSigPath = storage_path('app/public/' . $signatures->right_signature_path);
                    $rightSigData = file_exists($rightSigPath) ? base64_encode(file_get_contents($rightSigPath)) : null;
                    $rightSigExt = pathinfo($rightSigPath, PATHINFO_EXTENSION);
                    $rightSigMime = in_array(strtolower($rightSigExt), ['jpg', 'jpeg']) ? 'image/jpeg' : 'image/png';
                @endphp
                @if($rightSigData)
                    <img src="data:{{ $rightSigMime }};base64,{{ $rightSigData }}" style="width:50px; height:25px; margin-bottom:2mm;">
                @endif
            @else
                <div style="margin-bottom:10mm;"></div>
            @endif
            <div style="text-decoration:underline;">{{ $rightName }}</div>
            @if($rightNik !== '')
                <div style="font-size:7pt;">NIK: {{ $rightNik }}</div>
            @endif
            @if(!empty($rightOrg))
                <div style="font-size:7pt;">{{ $rightOrg }}</div>
            @endif
        </td>
    </tr>
</table>

    </div>
</div>
