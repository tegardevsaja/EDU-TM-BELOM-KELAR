@php
    $row = $siswa->nilai_ta_row ?? null;
    $cmp = $siswa->nilai_computed ?? [];
    $naRaw = $row['na'] ?? ($row['NA'] ?? ($row['nilai_akhir'] ?? ($cmp['avg'] ?? null)));
    $huruf = $row['huruf'] ?? ($row['HURUF'] ?? ($row['predikat'] ?? ($cmp['grade'] ?? null)));
    $na = is_numeric($naRaw) ? number_format((float)$naRaw, 2) : null;
    $np = isset($row['np']) && is_numeric($row['np']) ? number_format((float)$row['np'], 2) : null;
    // Helper konversi angka ke huruf. Jika skala tampak 0-20, skala dinaikkan ke 0-100.
    $toLetter = function($v) {
        if ($v === null || $v === '') return '';
        if (!is_numeric($v)) return '';
        $n = (float) $v;
        if ($n <= 20) { $n *= 5; }
        return $n >= 90 ? 'A' : ($n >= 75 ? 'B' : ($n >= 60 ? 'C' : 'D'));
    };
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
    <div style="position:absolute; top: 10mm; left: 12mm; right: 12mm; bottom: 4mm; font-family: Arial, sans-serif; padding: 5mm 6mm 10mm 6mm;">
        
        {{-- Header --}}
        <div style="text-align:center; margin-bottom: 4mm;">
            <div style="font-size:12pt; font-weight:bold; letter-spacing: 0.5px; margin-bottom: 0.5mm;">DAFTAR NILAI TUGAS AKHIR</div>
            <div style="font-size:10pt; font-weight:bold; margin-bottom: 0.5mm;">SEKOLAH MENENGAH KEJURUAN (SMK) TUNAS MEDIA</div>
            <div style="font-size:9pt; font-weight:bold;">TAHUN PELAJARAN {{ now()->format('Y') }}/{{ now()->addYear()->format('Y') }}</div>
        </div>

        {{-- Tabel identitas --}}
        <table style="width:100%; border-collapse: collapse; font-size:9pt; margin-bottom: 3mm;">
            <tr>
                <td style="width:28%; border:1px solid #666; padding:1.5mm 2.5mm; background-color:#e0e0e0;">Nama</td>
                <td style="border:1px solid #666; padding:1.5mm 2.5mm;" colspan="3">{{ $siswa->nama ?? 'ZAIN Afathir' }}</td>
            </tr>
            <tr>
                <td style="border:1px solid #666; padding:1.5mm 2.5mm; background-color:#e0e0e0;">NIS</td>
                <td style="border:1px solid #666; padding:1.5mm 2.5mm;" colspan="3">{{ $siswa->nis ?? '22101115' }}</td>
            </tr>
            <tr>
                <td style="border:1px solid #666; padding:1.5mm 2.5mm; background-color:#e0e0e0;">Kompetensi Keahlian</td>
                <td style="border:1px solid #666; padding:1.5mm 2.5mm;" colspan="3">{{ $siswa->kelas->jurusan->nama_jurusan ?? 'Multimedia' }}</td>
            </tr>
        </table>

        {{-- Tabel komponen nilai --}}
        <table style="width:100%; border-collapse: collapse; font-size:8.5pt;">
            <thead>
                <tr style="background-color:#d0d0d0;">
                    <th style="border:1px solid #000; padding:2mm; width:7%; text-align:center; font-weight:bold;">NO</th>
                    <th style="border:1px solid #000; padding:2mm; font-weight:bold;">KOMPONEN / SUB KOMPONEN</th>
                    <th colspan="2" style="border:1px solid #000; padding:2mm; text-align:center; font-weight:bold;">NILAI KOMPONEN</th>
                </tr>
                <tr style="background-color:#d0d0d0;">
                    <th style="border:1px solid #000; padding:1.5mm; text-align:center; font-weight:bold; font-size:7.5pt;"></th>
                    <th style="border:1px solid #000; padding:1.5mm; font-weight:bold; font-size:7.5pt;"></th>
                    <th style="border:1px solid #000; padding:1.5mm; width:12%; text-align:center; font-weight:bold; font-size:7.5pt;">ANGKA</th>
                    <th style="border:1px solid #000; padding:1.5mm; width:14%; text-align:center; font-weight:bold; font-size:7.5pt;">NILAI HURUF</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="border:1px solid #000; padding:1.5mm; text-align:center;">1</td>
                    <td style="border:1px solid #000; padding:1.5mm;">Nilai Project</td>
                    <td style="border:1px solid #000; padding:1.5mm; text-align:center;">{{ $np ?? '95,00' }}</td>
                    @php
                        // Ambil angka project dari data atau fallback tampilan, lalu konversi ke huruf
                        $npDisplay = $np ?? '95,00';
                        $npNum = isset($row['np']) && is_numeric($row['np'])
                            ? (float)$row['np']
                            : (float)str_replace(',', '.', $npDisplay);
                        $npLetter = $toLetter($npNum);
                    @endphp
                    <td style="border:1px solid #000; padding:1.5mm; text-align:center;">{{ $npLetter }}</td>
                </tr>
                <tr>
                    <td style="border:1px solid #000; padding:1.5mm; text-align:center;">2</td>
                    <td style="border:1px solid #000; padding:1.5mm;">Nilai Sidang Tugas Akhir</td>
                    <td style="border:1px solid #000; padding:1.5mm; text-align:center;">{{ $na ?? '84,00' }}</td>
                    @php
                        // Pastikan huruf untuk No 2 selalu terisi: gunakan data atau fallback tampilan
                        $naDisplay = $na ?? '84,00';
                        $naNum = is_numeric($row['na'] ?? null)
                            ? (float)$row['na']
                            : (float)str_replace(',', '.', $naDisplay);
                        $naLetter = $toLetter($naNum);
                    @endphp
                    <td style="border:1px solid #000; padding:1.5mm; text-align:center;">{{ $naLetter }}</td>
                </tr>
                <tr>
                    <td style="border:1px solid #000; padding:1.5mm; text-align:center;"></td>
                    <td style="border:1px solid #000; padding:1.5mm; padding-left:5mm;">2.1. Kemampuan dalam menjelaskan indikasi</td>
                    <td style="border:1px solid #000; padding:1.5mm; text-align:center;">18</td>
                    <td style="border:1px solid #000; padding:1.5mm; text-align:center;">{{ $toLetter(18) }}</td>
                </tr>
                <tr>
                    <td style="border:1px solid #000; padding:1.5mm; text-align:center;"></td>
                    <td style="border:1px solid #000; padding:1.5mm; padding-left:5mm;">2.2. Kemampuan dalam meneruskan pertanyaan</td>
                    <td style="border:1px solid #000; padding:1.5mm; text-align:center;">17</td>
                    <td style="border:1px solid #000; padding:1.5mm; text-align:center;">{{ $toLetter(17) }}</td>
                </tr>
                <tr>
                    <td style="border:1px solid #000; padding:1.5mm; text-align:center;"></td>
                    <td style="border:1px solid #000; padding:1.5mm; padding-left:5mm;">2.3. Kemampuan memperbaiki produk TA</td>
                    <td style="border:1px solid #000; padding:1.5mm; text-align:center;">17</td>
                    <td style="border:1px solid #000; padding:1.5mm; text-align:center;">{{ $toLetter(17) }}</td>
                </tr>
                <tr>
                    <td style="border:1px solid #000; padding:1.5mm; text-align:center;"></td>
                    <td style="border:1px solid #000; padding:1.5mm; padding-left:5mm;">2.4. Sistematika Laporan</td>
                    <td style="border:1px solid #000; padding:1.5mm; text-align:center;">15</td>
                    <td style="border:1px solid #000; padding:1.5mm; text-align:center;">{{ $toLetter(15) }}</td>
                </tr>
                <tr>
                    <td style="border:1px solid #000; padding:1.5mm; text-align:center;"></td>
                    <td style="border:1px solid #000; padding:1.5mm; padding-left:5mm;">2.5. Keaslian Project</td>
                    <td style="border:1px solid #000; padding:1.5mm; text-align:center;">7</td>
                    <td style="border:1px solid #000; padding:1.5mm; text-align:center;">{{ $toLetter(7) }}</td>
                </tr>
                <tr>
                    <td style="border:1px solid #000; padding:1.5mm; text-align:center;"></td>
                    <td style="border:1px solid #000; padding:1.5mm; padding-left:5mm;">2.6. Sikap dan penampilan sidang</td>
                    <td style="border:1px solid #000; padding:1.5mm; text-align:center;">8</td>
                    <td style="border:1px solid #000; padding:1.5mm; text-align:center;">{{ $toLetter(8) }}</td>
                </tr>
                <tr style="background-color:#e8e8e8; font-weight:bold;">
                    <td style="border:1px solid #000; padding:2mm; text-align:center;" colspan="2">NILAI AKHIR ( 30% Nilai Project + 70% Nilai Sidang Tugas Akhir )</td>
                    <td style="border:1px solid #000; padding:2mm; text-align:center;">{{ $na ?? '87,30' }}</td>
                    <td style="border:1px solid #000; padding:2mm; text-align:center;">{{ $huruf ?? 'B (Memuaskan)' }}</td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top: 4mm; font-size:8pt;">
            <div style="font-weight:bold; margin-bottom:1.5mm;">Keterangan Nilai</div>
            <table style="width:60%; border-collapse:collapse;">
                <tr>
                    <td style="border:1px solid #000; padding:1mm 2mm; width:35%">90 – 100</td>
                    <td style="border:1px solid #000; padding:1mm 2mm;">A (Amat Baik)</td>
                </tr>
                <tr>
                    <td style="border:1px solid #000; padding:1mm 2mm;">75 – 89</td>
                    <td style="border:1px solid #000; padding:1mm 2mm;">B (Baik)</td>
                </tr>
                <tr>
                    <td style="border:1px solid #000; padding:1mm 2mm;">60 – 74</td>
                    <td style="border:1px solid #000; padding:1mm 2mm;">C (Cukup)</td>
                </tr>
                <tr>
                    <td style="border:1px solid #000; padding:1mm 2mm;">&lt; 60</td>
                    <td style="border:1px solid #000; padding:1mm 2mm;">D (Kurang)</td>
                </tr>
            </table>
        </div>

        {{-- Footer penguji --}}
        @php
            // Debug: pastikan $signatures ada
            if (!isset($signatures) || !$signatures) {
                $signatures = \App\Models\GradeSignature::first();
            }
            
            $leftLabel = ($signatures && $signatures->left_label) ? $signatures->left_label : 'Penguji Internal';
            $leftName  = ($signatures && $signatures->left_name) ? $signatures->left_name : 'Agus Lukman Arie Hidayat, S.Kom.';
            $leftOrg   = ($signatures && $signatures->left_org) ? $signatures->left_org : 'SMK Tunas Media';
            $rightLabel = ($signatures && $signatures->right_label) ? $signatures->right_label : 'Penguji Eksternal';
            $rightName  = ($signatures && $signatures->right_name) ? $signatures->right_name : 'Galih Pamungkas';
            $rightOrg   = ($signatures && $signatures->right_org) ? $signatures->right_org : 'Wira Motor1';
            $city       = ($signatures && $signatures->city) ? $signatures->city : 'Kota Depok';
        @endphp

        <table style="width:100%; margin-top: 6mm; font-size:8.5pt; border-collapse: collapse;">
            <tr>
                <td style="width:50%; text-align:center; vertical-align:top;">
                    <div style="margin-bottom:2mm;">{{ $leftLabel }}</div>
                    @if($signatures && $signatures->left_signature_path)
                        @php
                            $leftSigPath = storage_path('app/public/' . $signatures->left_signature_path);
                            $leftSigData = file_exists($leftSigPath) ? base64_encode(file_get_contents($leftSigPath)) : null;
                            $leftSigExt = pathinfo($leftSigPath, PATHINFO_EXTENSION);
                            $leftSigMime = in_array(strtolower($leftSigExt), ['jpg', 'jpeg']) ? 'image/jpeg' : 'image/png';
                        @endphp
                        @if($leftSigData)
                            <img src="data:{{ $leftSigMime }};base64,{{ $leftSigData }}" style="width:60px; height:30px; margin-bottom:2mm;">
                        @endif
                    @else
                        <div style="margin-bottom:10mm;"></div>
                    @endif
                    <div style="text-decoration:underline; font-weight:normal;">{{ $leftName }}</div>
                    <div style="font-size:8pt; margin-top:1mm;">{{ $leftOrg }}</div>
                </td>
                <td style="width:50%; text-align:center; vertical-align:top;">
                    <div style="margin-bottom:1.5mm;">{{ $city }}, {{ now()->locale('id')->isoFormat('D MMMM YYYY') }}</div>
                    <div style="margin-bottom:2mm;">{{ $rightLabel }}</div>
                    @if($signatures && $signatures->right_signature_path)
                        @php
                            $rightSigPath = storage_path('app/public/' . $signatures->right_signature_path);
                            $rightSigData = file_exists($rightSigPath) ? base64_encode(file_get_contents($rightSigPath)) : null;
                            $rightSigExt = pathinfo($rightSigPath, PATHINFO_EXTENSION);
                            $rightSigMime = in_array(strtolower($rightSigExt), ['jpg', 'jpeg']) ? 'image/jpeg' : 'image/png';
                        @endphp
                        @if($rightSigData)
                            <img src="data:{{ $rightSigMime }};base64,{{ $rightSigData }}" style="width:60px; height:30px; margin-bottom:2mm;">
                        @endif
                    @else
                        <div style="margin-bottom:10mm;"></div>
                    @endif
                    <div style="text-decoration:underline; font-weight:normal;">{{ $rightName }}</div>
                    <div style="font-size:8pt; margin-top:1mm;">{{ $rightOrg }}</div>
                </td>
            </tr>
        </table>
    </div>
</div>  