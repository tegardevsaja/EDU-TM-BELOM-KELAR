<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class SiswaController extends Controller
{
    
    public function index()
    {
        $siswa = Siswa::with(['kelas', 'jurusan', 'tahunAjaran'])->latest()->paginate(10);
        return view('master.siswa.index', compact('siswa'));
    }

    public function create()
    {
        return view('master.siswa.create', [
            'kelas' => Kelas::all(),
            'jurusan' => Jurusan::all(),
            'tahun_ajaran' => TahunAjaran::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nis' => ['required','max:24','regex:/^[0-9]+$/','unique:siswas'],
            'nama' => 'required|max:100',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'required|max:50',
            'tanggal_lahir' => 'required|date',
            'agama' => 'required|max:50',
            'nama_orang_tua' => 'required|max:100',
            'alamat_orang_tua' => 'required',
            'no_hp_orang_tua' => ['required','max:18','regex:/^[0-9]+$/'],
            'asal_sekolah' => 'required|max:100',
            'kelas_id' => 'required|exists:kelas,id',
            'jurusan_id' => 'required|exists:jurusans,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'tahun_masuk' => 'required',
            'status' => 'required|in:Aktif,Alumni,Nonaktif',
        ]);

        Siswa::create($request->all());
        $prefix = $this->routePrefix();
        return redirect()->route($prefix . '.siswa.index')->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $siswa = Siswa::findOrFail($id);
        return view('master.siswa.edit', [
            'siswa' => $siswa,
            'kelas' => Kelas::all(),
            'jurusan' => Jurusan::all(),
            'tahun_ajaran' => TahunAjaran::all(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $siswa = Siswa::findOrFail($id);

        $request->validate([
            'nis' => ['required','max:24','regex:/^[0-9]+$/','unique:siswas,nis,' . $siswa->id],
            'nama' => 'required|max:100',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'required|max:50',
            'tanggal_lahir' => 'required|date',
            'agama' => 'required|max:50',
            'nama_orang_tua' => 'required|max:100',
            'alamat_orang_tua' => 'required',
            'no_hp_orang_tua' => ['required','max:18','regex:/^[0-9]+$/'],
            'asal_sekolah' => 'required|max:100',
            'kelas_id' => 'required|exists:kelas,id',
            'jurusan_id' => 'required|exists:jurusans,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'tahun_masuk' => 'required',
            'status' => 'required|in:Aktif,Alumni,Nonaktif',
        ]);

        $siswa->update($request->all());
        $prefix = $this->routePrefix();
        return redirect()->route($prefix . '.siswa.index')->with('success', 'Siswa berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Siswa::findOrFail($id)->delete();
        $prefix = $this->routePrefix();
        return redirect()->route($prefix . '.siswa.index')->with('success', 'Siswa berhasil dihapus.');
    }

    public function downloadTemplate()
    {
        $file = public_path('template/format/Format-Data-Siswa.xlsx');

        if (file_exists($file)) {
            return response()->download($file, 'Format-Data-Siswa.xlsx');
        } else {
            return redirect()->back()->with('error', 'Template file tidak ditemukan.');
        }
    }

    private function routePrefix(): string
    {
        $name = request()->route()?->getName();
        if (!$name) {
            return 'master';
        }
        $first = explode('.', $name)[0] ?? 'master';
        return in_array($first, ['master', 'admin', 'guru']) ? $first : 'master';
    }

    // =====================
    // Exporters
    // =====================
    public function exportCsv()
    {
        $filename = 'siswa_' . now()->format('Ymd_His') . '.csv';

        $rows = Siswa::query()
            ->with(['kelas:id,nama_kelas', 'jurusan:id,nama_jurusan', 'tahunAjaran:id,tanggal_mulai,tanggal_selesai'])
            ->when(request('kelas_id'), function ($q, $kelasId) { $q->where('kelas_id', $kelasId); })
            ->when(request('jurusan_id'), function ($q, $jurusanId) { $q->where('jurusan_id', $jurusanId); })
            ->when(request('search'), function ($q, $search) {
                $terms = explode(' ', $search);
                $q->where(function($sub) use ($terms) {
                    foreach ($terms as $term) {
                        $t = trim($term);
                        if ($t !== '') {
                            $sub->where(function($w) use ($t) {
                                $w->where('nama', 'like', "%$t%")
                                  ->orWhere('nis', 'like', "%$t%");
                            });
                        }
                    }
                });
            })
            ->orderBy('nama')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function() use ($rows) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM for Excel compatibility
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['NIS','NISN','Nama','JK','Kelas','Jurusan','Tahun Ajaran','Tahun Masuk','Status','Tempat Lahir','Tanggal Lahir','Agama','Nama Ortu','Alamat Ortu','No HP Ortu','Asal Sekolah']);
            foreach ($rows as $s) {
                $ta = optional($s->tahunAjaran);
                $tahunAj = ($ta->tanggal_mulai ? \Carbon\Carbon::parse($ta->tanggal_mulai)->format('Y') : '')
                    . ' / ' . ($ta->tanggal_selesai ? \Carbon\Carbon::parse($ta->tanggal_selesai)->format('Y') : '');
                fputcsv($out, [
                    $s->nis,
                    $s->nisn,
                    $s->nama,
                    $s->jenis_kelamin,
                    optional($s->kelas)->nama_kelas,
                    optional($s->jurusan)->nama_jurusan,
                    trim($tahunAj, ' /'),
                    $s->tahun_masuk,
                    $s->status,
                    $s->tempat_lahir,
                    $s->tanggal_lahir,
                    $s->agama,
                    $s->nama_orang_tua,
                    $s->alamat_orang_tua,
                    $s->no_hp_orang_tua,
                    $s->asal_sekolah,
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportExcel()
    {
        // Prefer Maatwebsite Excel if installed
        if (class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
            $data = Siswa::query()
                ->with(['kelas:id,nama_kelas', 'jurusan:id,nama_jurusan', 'tahunAjaran:id,tanggal_mulai,tanggal_selesai'])
                ->when(request('kelas_id'), function ($q, $kelasId) { $q->where('kelas_id', $kelasId); })
                ->when(request('jurusan_id'), function ($q, $jurusanId) { $q->where('jurusan_id', $jurusanId); })
                ->when(request('search'), function ($q, $search) {
                    $terms = explode(' ', $search);
                    $q->where(function($sub) use ($terms) {
                        foreach ($terms as $term) {
                            $t = trim($term);
                            if ($t !== '') {
                                $sub->where(function($w) use ($t) {
                                    $w->where('nama', 'like', "%$t%")
                                      ->orWhere('nis', 'like', "%$t%");
                                });
                            }
                        }
                    });
                })
                ->orderBy('nama')
                ->get()
                ->map(function($s){
                    $ta = optional($s->tahunAjaran);
                    $tahunAj = ($ta->tanggal_mulai ? \Carbon\Carbon::parse($ta->tanggal_mulai)->format('Y') : '')
                        . ' / ' . ($ta->tanggal_selesai ? \Carbon\Carbon::parse($ta->tanggal_selesai)->format('Y') : '');
                    return [
                        'NIS' => $s->nis,
                        'NISN' => $s->nisn,
                        'Nama' => $s->nama,
                        'JK' => $s->jenis_kelamin,
                        'Kelas' => optional($s->kelas)->nama_kelas,
                        'Jurusan' => optional($s->jurusan)->nama_jurusan,
                        'Tahun Ajaran' => trim($tahunAj, ' /'),
                        'Tahun Masuk' => $s->tahun_masuk,
                        'Status' => $s->status,
                        'Tempat Lahir' => $s->tempat_lahir,
                        'Tanggal Lahir' => $s->tanggal_lahir,
                        'Agama' => $s->agama,
                        'Nama Ortu' => $s->nama_orang_tua,
                        'Alamat Ortu' => $s->alamat_orang_tua,
                        'No HP Ortu' => $s->no_hp_orang_tua,
                        'Asal Sekolah' => $s->asal_sekolah,
                    ];
                })->toArray();

            $exporter = new class($data) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
                private array $data;
                public function __construct(array $data){ $this->data = $data; }
                public function array(): array { return $this->data; }
                public function headings(): array { return array_keys($this->data[0] ?? []); }
            };

            return \Maatwebsite\Excel\Facades\Excel::download($exporter, 'siswa_'.now()->format('Ymd_His').'.xlsx');
        }

        // Fallback: provide CSV if Excel package not available
        return $this->exportCsv();
    }

}
