<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        :root { --scale: 0.85; }
        body { margin: 0; font-family: Arial, sans-serif; background: #f3f4f6; }
        .wrapper { max-width: 1100px; margin: 24px auto; padding: 12px; }
        .page-frame { position: relative; width: 100%; background: #fff; aspect-ratio: 297 / 210; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 8px 20px rgba(0,0,0,0.08); overflow: hidden; }
        .scale { position: absolute; top: 0; left: 50%; width: 297mm; height: 210mm; transform-origin: top center; transform: translateX(-50%) scale(var(--scale)); background: #fff; }
        .toolbar { display:flex; align-items:center; justify-content:space-between; margin-bottom: 10px; }
        .toolbar .title { font-weight: 700; font-size: 16px; }
        .toolbar .controls { display:flex; gap: 8px; align-items:center; }
        .toolbar input[type=range]{ width: 200px; }
    </style>
</head>
<body>
<div class="wrapper" x-data="{ sc: 0.85 }" x-init="document.documentElement.style.setProperty('--scale', sc)">
    <div class="toolbar">
        <div class="title">Download View — {{ $template->nama_template }}</div>
        <div class="controls">
            <label style="font-size:12px; color:#374151;">Zoom</label>
            <input type="range" min="0.5" max="1" step="0.05" x-model.number="sc" @input="document.documentElement.style.setProperty('--scale', sc)">
        </div>
    </div>

    @php
        // base64 background for consistency
        $imagePath = storage_path('app/public/' . $template->background_image);
        $imageData = '';
        $imageType = '';
        if (file_exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $ext = pathinfo($imagePath, PATHINFO_EXTENSION);
            $imageType = in_array(strtolower($ext), ['png']) ? 'image/png' : 'image/jpeg';
        }
    @endphp

    @foreach($siswas as $siswa)
        @if(empty($only_grades))
        <div class="page-frame" style="margin-bottom:18px;">
            <div class="scale">
                <div class="certificate-page" style="width:297mm;height:210mm;position:relative;">
                    @if($imageData)
                        <img src="data:{{ $imageType }};base64,{{ $imageData }}" style="position:absolute;inset:0;width:297mm;height:210mm;object-fit:cover;" alt="bg">
                    @endif

                    @php $__scale = 1122 / 1000; @endphp
                    @foreach(($elements ?? []) as $el)
                        @php
                            $content = '';
                            $isNameVariable = false;
                            if (($el['type'] ?? '') === 'text') {
                                $content = $el['content'] ?? '';
                            }
                            elseif (($el['type'] ?? '') === 'variable') {
                                $var = (string)($el['variable'] ?? '');
                                $content = match($var) {
                                    '$nama_siswa' => $siswa->nama ?? '',
                                    '$Nama' => $siswa->nama ?? '',
                                    '$kelas' => $siswa->kelas->nama_kelas ?? '',
                                    '$nis' => $siswa->nis ?? '',
                                    '$tanggal' => ($siswa->tanggal_custom ?? null) ? \Carbon\Carbon::parse($siswa->tanggal_custom)->locale('id')->isoFormat('D MMMM YYYY') : now()->locale('id')->isoFormat('D MMMM YYYY'),
                                    '$jurusan' => $siswa->kelas->jurusan->nama_jurusan ?? '',
                                    '$nilai' => $siswa->nilai ?? '-',
                                    '$peringkat' => $siswa->peringkat ?? '-',
                                    '$nomor_sertifikat' => $siswa->cert_number ?? '',
                                    '$no_sertifikat' => $siswa->cert_number ?? '',
                                    '$no_sertif' => $siswa->cert_number ?? '',
                                    '$nomor' => $siswa->cert_number ?? '',
                                    '$ttd' => '(Tanda Tangan)',
                                    default => $el['value'] ?? ($el['variable'] ?? '')
                                };
                                $isNameVariable = in_array($var, ['$', '$nama_siswa', '$Nama'], true) || str_contains(strtolower($var), 'nama');
                            }
                            $x = $el['x'] ?? 50; 
                            $y = $el['y'] ?? 50; 
                            $align = $el['align'] ?? 'center';
                            $fontSize = (($el['fontSize'] ?? 24) * $__scale);
                            $tx = $align === 'left' ? '0' : ($align === 'right' ? '-100%' : '-50%');
                            $extraMarginLeft = ($isNameVariable && is_string($content) && str_contains($content, ' ')) ? '12rem' : '0';
                        @endphp
                        @if(($el['type'] ?? '') === 'image')
                            <img src="{{ $el['src'] ?? '' }}" style="position:absolute; left: {{ $x }}%; top: {{ $y }}%; transform: translate({{ $tx }}, -50%); width: {{ $el['width'] ?? 100 }}px; height: {{ $el['height'] ?? 100 }}px; object-fit: contain;">
                        @else
                            <div style="position:absolute; left: {{ $x }}%; top: {{ $y }}%; transform: translate({{ $tx }}, -50%); font-size: {{ $fontSize }}px; color: {{ $el['color'] ?? '#000' }}; font-weight: {{ ($el['bold'] ?? false) ? '700':'400' }}; text-align: {{ $align }}; font-family: {{ $el['fontFamily'] ?? 'Arial' }}, sans-serif; min-width:100px; margin-left: {{ $extraMarginLeft }};">
                                {{ $content }}
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        @if(empty($only_certificate) && isset($siswa->nilai_detail_map) && is_array($siswa->nilai_detail_map) && !empty($siswa->nilai_detail_map))
        <div class="page-frame" style="margin-bottom:22px;">
            <div class="scale">
                @if(($grade_sheet_format ?? '') === 'prakerin')
                    @include('master.sertifikat.grades.prakerin', ['siswa'=>$siswa, 'signatures' => $signatures ?? null])
                @elseif(($grade_sheet_format ?? '') === 'tugas_akhir')
                    @include('master.sertifikat.grades.tugas_akhir', ['siswa'=>$siswa, 'signatures' => $signatures ?? null])
                @endif
            </div>
        </div>
        @endif
    @endforeach
</div>

<script>
    document.addEventListener('alpine:init', () => {});
</script>
</body>
</html>
