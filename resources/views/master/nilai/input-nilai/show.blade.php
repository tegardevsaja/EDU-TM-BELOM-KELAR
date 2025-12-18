<x-layouts.app :title="__('Preview Nilai')">
    <div class="max-w-5xl mx-auto">
        <div class="mb-5 flex items-center justify-between gap-3">
            <div>
                <div class="text-xs text-zinc-500">Preview</div>
                <h2 class="text-xl font-semibold">Nilai Siswa</h2>
            </div>
            
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div class="lg:col-span-2 space-y-5">
                <div class="bg-white dark:bg-zinc-800 border rounded-lg p-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="text-sm">
                                <div class="text-zinc-500">Siswa</div>
                                <div class="font-semibold">{{ $nilai->siswa->nama ?? '-' }}</div>
                                <div class="text-xs text-zinc-500">NIS: {{ $nilai->siswa->nis ?? '-' }} @if(!empty($nilai->siswa->nisn)) · NISN: {{ $nilai->siswa->nisn }} @endif</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($nilai->jenis_penilaian)
                                <span class="px-2.5 py-1 text-xs rounded-full bg-indigo-100 text-indigo-700">{{ $nilai->jenis_penilaian }}</span>
                            @endif
                            @if(optional($nilai->template)->nama_template)
                                <span class="px-2.5 py-1 text-xs rounded-full bg-emerald-100 text-emerald-700">{{ $nilai->template->nama_template }}</span>
                            @endif
                        </div>
                    </div>
                    @php($dHeader = $nilai->nilai_detail['row'] ?? $nilai->nilai_detail ?? [])
                    <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
                        <div>
                            <div class="text-zinc-500">Tanggal Input</div>
                            <div class="font-medium">{{ $nilai->tanggal_input ? $nilai->tanggal_input->format('d M Y') : (optional($nilai->created_at)->format('d M Y') ?? '-') }}</div>
                        </div>
                        <div>
                            <div class="text-zinc-500">Tahun Ajaran</div>
                            <div class="font-medium">{{ optional($nilai->tahunAjaran)->tahun_ajaran ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-zinc-500">Kelas</div>
                            <div class="font-medium">{{ optional($nilai->siswa->kelas)->nama_kelas ?? (optional($nilai->siswa->jurusan)->nama_jurusan ?? ($dHeader['jurusan'] ?? ($dHeader['JURUSAN'] ?? '-'))) }}</div>
                        </div>
                    </div>
                </div>

                @php($d = $nilai->nilai_detail['row'] ?? $nilai->nilai_detail ?? [])

                @if(($nilai->jenis_penilaian ?? '') === 'TA')
                    <div class="bg-white dark:bg-zinc-800 border rounded-lg p-5">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="font-semibold">Ringkasan Nilai TA</h3>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                            <div class="p-3 rounded-md bg-gray-50 dark:bg-zinc-700/50">
                                <div class="text-xs text-zinc-500">NP</div>
                                <div class="text-lg font-semibold">{{ $d['NP'] ?? ($d['np'] ?? '-') }}</div>
                            </div>
                            @for($i=1;$i<=6;$i++)
                                <div class="p-3 rounded-md bg-gray-50 dark:bg-zinc-700/50">
                                    <div class="text-xs text-zinc-500">NS{{ $i }}</div>
                                    <div class="text-lg font-semibold">{{ $d['NS'.$i] ?? ($d['ns'.$i] ?? '-') }}</div>
                                </div>
                            @endfor
                            <div class="p-3 rounded-md bg-gray-50 dark:bg-zinc-700/50">
                                <div class="text-xs text-zinc-500">JML NS</div>
                                <div class="text-lg font-semibold">{{ $d['JML NS'] ?? ($d['jml_ns'] ?? '-') }}</div>
                            </div>
                            
                            <div class="p-3 rounded-md bg-gray-50 dark:bg-zinc-700/50">
                                <div class="text-xs text-zinc-500">Huruf</div>
                                <div class="text-lg font-semibold">{{ $d['HURUF'] ?? ($d['huruf'] ?? '-') }}</div>
                            </div>
                            <div class="p-3 rounded-md bg-gray-50 dark:bg-zinc-700/50">
                                <div class="text-xs text-zinc-500">Predikat</div>
                                <div class="text-lg font-semibold">{{ $d['PREDIKAT'] ?? ($d['predikat'] ?? '-') }}</div>
                            </div>
                        </div>
                        <div class="mt-5">
                            <div class="text-sm text-zinc-500 mb-2">Detail Proyek</div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                                <div><span class="text-zinc-500">Project</span><div class="font-medium">{{ $d['PROJECT'] ?? ($d['project'] ?? '-') }}</div></div>
                                <div><span class="text-zinc-500">Instansi</span><div class="font-medium">{{ $d['INSTANSI 1'] ?? ($d['instansi1'] ?? '-') }}</div></div>
                                <div><span class="text-zinc-500">Kota</span><div class="font-medium">{{ $d['KOTA 1'] ?? ($d['kota1'] ?? '-') }}</div></div>
                            </div>
                        </div>
                    </div>
                @elseif(($nilai->jenis_penilaian ?? '') === 'Uji DUDI')
                    <div class="bg-white dark:bg-zinc-800 border rounded-lg p-5">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="font-semibold">Ringkasan Nilai Uji DUDI</h3>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div class="p-3 rounded-md bg-gray-50 dark:bg-zinc-700/50">
                                <div class="text-xs text-zinc-500">No Peserta</div>
                                <div class="text-lg font-semibold">{{ $d['No Peserta'] ?? ($d['no_peserta'] ?? '-') }}</div>
                            </div>
                            <div class="p-3 rounded-md bg-gray-50 dark:bg-zinc-700/50">
                                <div class="text-xs text-zinc-500">Predikat</div>
                                <div class="text-lg font-semibold">{{ $d['predikat'] ?? ($d['PREDIKAT'] ?? '-') }}</div>
                            </div>
                            <div class="p-3 rounded-md bg-gray-50 dark:bg-zinc-700/50">
                                <div class="text-xs text-zinc-500">Predikat (EN)</div>
                                <div class="text-lg font-semibold">{{ $d['predikat english'] ?? ($d['PREDIKAT ENGLISH'] ?? '-') }}</div>
                            </div>
                            <div class="p-3 rounded-md bg-gray-50 dark:bg-zinc-700/50">
                                <div class="text-xs text-zinc-500">Nilai Akhir</div>
                                <div class="text-lg font-semibold">{{ $d['Nilai Akhir'] ?? ($d['nilai_akhir'] ?? '-') }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                
            </div>

            <div class="space-y-5">
                <div class="bg-white dark:bg-zinc-800 border rounded-lg p-5">
                    <h4 class="font-semibold mb-3">Informasi Siswa</h4>
                    <div class="text-sm grid grid-cols-2 gap-3">
                        <div>
                            <div class="text-zinc-500">Nama</div>
                            <div class="font-medium">{{ $nilai->siswa->nama ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-zinc-500">NIS / NISN</div>
                            <div class="font-medium">{{ $nilai->siswa->nis ?? '-' }} @if(!empty($nilai->siswa->nisn)) / {{ $nilai->siswa->nisn }} @endif</div>
                        </div>
                        <div>
                            <div class="text-zinc-500">Kelas</div>
                            <div class="font-medium">{{ optional($nilai->siswa->kelas)->nama_kelas ?? (optional($nilai->siswa->jurusan)->nama_jurusan ?? ($d['jurusan'] ?? ($d['JURUSAN'] ?? '-'))) }}</div>
                        </div>
                        <div>
                            <div class="text-zinc-500">Jurusan</div>
                            <div class="font-medium">{{ $d['JURUSAN'] ?? ($d['jurusan'] ?? '-') }}</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 border rounded-lg p-5">
                    <h4 class="font-semibold mb-3">Aksi</h4>
                    <div class="flex flex-wrap gap-2 items-center">
                        <a href="{{ route('master.nilai.index') }}" class="px-3 py-2 rounded-md border hover:bg-gray-50 dark:hover:bg-zinc-700">Kembali</a>
                        <a href="{{ route('master.nilai.edit', $nilai->id) }}" class="px-3 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">Edit</a>
                        <form action="{{ route('master.nilai.destroy', $nilai->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="px-3 py-2 rounded-md bg-rose-600 text-white hover:bg-rose-700"
                                    data-confirm-delete
                                    data-name="{{ $nilai->siswa->nama ?? 'nilai ini' }}"
                                    data-title="Hapus Nilai?"
                                    data-confirm-label="Ya, hapus"
                                    data-cancel-label="Batal">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
