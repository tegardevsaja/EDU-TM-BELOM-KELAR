<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
        }
        
        .certificate-page {
            width: 297mm;
            height: 210mm;
            position: relative;
            page-break-after: always;
            overflow: hidden;
        }
        
        .certificate-page:last-child {
            page-break-after: avoid;
        }
        
        .background {
            position: absolute;
            top: 0;
            left: 0;
            width: 297mm;
            height: 210mm;
            object-fit: cover;
        }
        
        .element {
            position: absolute;
            white-space: pre-wrap;
            word-wrap: break-word;
            transform: translate(-50%, -50%);
            min-width: 100px;
        }
    </style>
</head>
<body>
    @php
        // Convert image to base64
        $imagePath = storage_path('app/public/' . $template->background_image);
        $imageData = '';
        $imageType = '';
        
        if (file_exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
            $imageType = match(strtolower($extension)) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                default => 'image/jpeg'
            };
        }
    @endphp

    @php
        // Scale factor so that font-size from customize (assumed canvas ~1000px width) matches A4 at ~1122px width
        $__canvas_px = 1000; // customize max-width
        $__a4_px = 1122; // A4 landscape width at 96dpi
        $__scale = $__a4_px / $__canvas_px; // ~1.122
    @endphp
    @foreach($siswas as $siswa)
    <div class="certificate-page">
        {{-- Background Image dengan Base64 --}}
        @if($imageData)
            <img src="data:{{ $imageType }};base64,{{ $imageData }}" 
                 class="background" 
                 alt="Background">
        @endif
        
        {{-- Render Elements (gunakan $elements yang dikirim dari controller agar sama dengan customize/preview) --}}
        @if(isset($elements) && is_array($elements))
            @foreach($elements as $element)
                @php
                    // Get content
                    $content = '';
                    if($element['type'] === 'text') {
                        $content = $element['content'];
                    } elseif($element['type'] === 'variable') {
                        $content = match($element['variable']) {
                            '$nama_siswa' => $siswa->nama ?? '',
                            '$kelas' => $siswa->kelas->nama_kelas ?? '',
                            '$nis' => $siswa->nis ?? '',
                            '$tanggal' => ($siswa->tanggal_custom ?? null) ? \Carbon\Carbon::parse($siswa->tanggal_custom)->locale('id')->isoFormat('D MMMM YYYY') : now()->locale('id')->isoFormat('D MMMM YYYY'),
                            '$jurusan' => $siswa->kelas->jurusan->nama_jurusan ?? '',
                            '$nilai' => $siswa->nilai ?? '-',
                            '$peringkat' => $siswa->peringkat ?? '-',
                            '$ttd' => '(Tanda Tangan)',
                            default => ($element['value'] ?? $element['variable'] ?? '')
                        };
                    } elseif($element['type'] === 'image') {
                        $content = ''; // Image will be rendered separately
                    }
                    
                    // Gunakan font size yang sama dengan preview, dengan scaling ke A4 px
                    $fontSize = ($element['fontSize'] ?? 24) * $__scale;
                    
                    // Posisi berdasarkan titik tengah (sama seperti customize/preview)
                    $x = $element['x'] ?? 50;
                    $y = $element['y'] ?? 50;
                    $align = $element['align'] ?? 'center';
                @endphp
                
                @if($element['type'] === 'image')
                    {{-- Image Element --}}
                    <img src="{{ $element['src'] ?? '' }}" 
                         style="position: absolute; left: {{ $x }}%; top: {{ $y }}%; transform: translate(-50%, -50%); width: {{ $element['width'] ?? 100 }}px; height: {{ $element['height'] ?? 100 }}px; object-fit: contain;"
                         alt="Uploaded Image">
                @else
                    {{-- Text/Variable Element --}}
                    <div class="element" style="
                        left: {{ $x }}%;
                        top: {{ $y }}%;
                        text-align: {{ $align }};
                        font-size: {{ $fontSize }}px;
                        font-family: {{ $element['fontFamily'] ?? 'Arial' }}, sans-serif;
                        color: {{ $element['color'] ?? '#000000' }};
                        font-weight: {{ ($element['bold'] ?? false) ? 'bold' : 'normal' }};
                    ">{{ $content }}</div>
                @endif
            @endforeach
        @else
            {{-- Fallback --}}
            <div class="element element-center" style="
                left: 50%;
                top: 50%;
                font-size: 48px;
                font-family: Arial, sans-serif;
                font-weight: bold;
                color: #000000;
                text-align: center;
                min-width: 100px;
            ">
                {{ $custom_names[$siswa->id] ?? $siswa->nama }}
            </div>
        @endif
    </div>
    @if(isset($siswa->nilai_detail_map) && is_array($siswa->nilai_detail_map) && !empty($siswa->nilai_detail_map))
    <div class="certificate-page">
        <div style="position:absolute; left: 5%; top: 5%; right:5%; bottom:5%;">
            <h2 style="text-align:center; margin-bottom: 16px;">Lampiran Nilai - {{ $siswa->nama }}</h2>

            {{-- Ringkasan --}}
            @php $cmp = $siswa->nilai_computed ?? []; @endphp
            <div style="margin-bottom: 14px; font-size: 14px;">
                @if(isset($cmp['total']))<span style="margin-right:16px;">Total: {{ $cmp['total'] }}</span>@endif
                @if(isset($cmp['avg']))<span style="margin-right:16px;">Rata-rata: {{ $cmp['avg'] }}</span>@endif
                @if(isset($cmp['weighted_avg']))<span style="margin-right:16px;">Rata-rata Berbobot: {{ $cmp['weighted_avg'] }}</span>@endif
                @if(isset($cmp['grade']))<span style="margin-right:16px;">Predikat: {{ $cmp['grade'] }}</span>@endif
            </div>

            {{-- Tabel Nilai --}}
            <table style="width:100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr>
                        <th style="border:1px solid #000; padding:6px; width:40px;">No</th>
                        <th style="border:1px solid #000; padding:6px;">Komponen</th>
                        <th style="border:1px solid #000; padding:6px;">Uraian</th>
                        <th style="border:1px solid #000; padding:6px; width:100px;">Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i = 1;
                        $map = $siswa->nilai_detail_map;
                    @endphp
                    @foreach($map as $komponen => $sub)
                        @if(is_array($sub))
                            @foreach($sub as $uraian => $nilai)
                                <tr>
                                    <td style="border:1px solid #000; padding:6px; text-align:center;">{{ $i++ }}</td>
                                    <td style="border:1px solid #000; padding:6px;">{{ $komponen }}</td>
                                    <td style="border:1px solid #000; padding:6px;">{{ $uraian }}</td>
                                    <td style="border:1px solid #000; padding:6px; text-align:center;">{{ is_numeric($nilai) ? number_format($nilai, 2) : $nilai }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td style="border:1px solid #000; padding:6px; text-align:center;">{{ $i++ }}</td>
                                <td style="border:1px solid #000; padding:6px;">-</td>
                                <td style="border:1px solid #000; padding:6px;">{{ $komponen }}</td>
                                <td style="border:1px solid #000; padding:6px; text-align:center;">{{ is_numeric($sub) ? number_format($sub, 2) : $sub }}</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    @endforeach
</body>
</html>