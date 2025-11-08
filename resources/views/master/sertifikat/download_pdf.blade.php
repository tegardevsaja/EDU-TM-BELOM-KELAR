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

    @foreach($siswas as $siswa)
    <div class="certificate-page">
        {{-- Background Image dengan Base64 --}}
        @if($imageData)
            <img src="data:{{ $imageType }};base64,{{ $imageData }}" 
                 class="background" 
                 alt="Background">
        @endif
        
        {{-- Render Elements --}}
        @if(isset($template->default_elements) && is_array($template->default_elements))
            @foreach($template->default_elements as $element)
                @php
                    // Konversi persentase ke mm
                    $leftMm = ($element['x'] / 100) * 297;
                    $topMm = ($element['y'] / 100) * 210;
                    
                    // Get content
                    $content = '';
                    if($element['type'] === 'text') {
                        $content = $element['content'];
                    } elseif($element['type'] === 'variable') {
                        $content = match($element['variable']) {
                            '$nama_siswa' => $custom_names[$siswa->id] ?? $siswa->nama,
                            '$kelas' => $siswa->kelas->nama_kelas ?? '',
                            '$nis' => $siswa->nis ?? '',
                            '$tanggal' => now()->locale('id')->isoFormat('D MMMM YYYY'),
                            '$jurusan' => $siswa->kelas->jurusan->nama_jurusan ?? '',
                            '$nilai' => $siswa->nilai ?? '-',
                            '$peringkat' => $siswa->peringkat ?? '-',
                            '$ttd' => '(Tanda Tangan)',
                            default => $element['variable']
                        };
                    }
                @endphp
                
                <div class="element" style="
                    left: {{ $leftMm }}mm;
                    top: {{ $topMm }}mm;
                    font-size: {{ $element['fontSize'] ?? 24 }}px;
                    font-family: {{ $element['fontFamily'] ?? 'Arial' }};
                    color: {{ $element['color'] ?? '#000000' }};
                    font-weight: {{ ($element['bold'] ?? false) ? 'bold' : 'normal' }};
                    text-align: {{ $element['align'] ?? 'center' }};
                    max-width: 80%;
                ">
                    {{ $content }}
                </div>
            @endforeach
        @else
            {{-- Fallback --}}
            <div class="element" style="
                left: 148.5mm;
                top: 105mm;
                font-size: 48px;
                font-family: Arial;
                font-weight: bold;
                color: #000000;
                text-align: center;
            ">
                {{ $custom_names[$siswa->id] ?? $siswa->nama }}
            </div>
        @endif
    </div>
    @endforeach
</body>
</html>