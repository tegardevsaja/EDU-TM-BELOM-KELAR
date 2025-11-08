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
            'nis' => 'required|max:20|unique:siswas',
            'nama' => 'required|max:100',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'required|max:50',
            'tanggal_lahir' => 'required|date',
            'agama' => 'required|max:50',
            'nama_orang_tua' => 'required|max:100',
            'alamat_orang_tua' => 'required',
            'no_hp_orang_tua' => 'required|max:20',
            'asal_sekolah' => 'required|max:100',
            'kelas_id' => 'required|exists:kelas,id',
            'jurusan_id' => 'required|exists:jurusans,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'tahun_masuk' => 'required',
            'status' => 'required|in:Aktif,Alumni,Nonaktif',
        ]);

        Siswa::create($request->all());
        return redirect()->route('siswa.index')->with('success', 'Siswa berhasil ditambahkan.');
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
            'nis' => 'required|max:20|unique:siswas,nis,' . $siswa->id,
            'nama' => 'required|max:100',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'required|max:50',
            'tanggal_lahir' => 'required|date',
            'agama' => 'required|max:50',
            'nama_orang_tua' => 'required|max:100',
            'alamat_orang_tua' => 'required',
            'no_hp_orang_tua' => 'required|max:20',
            'asal_sekolah' => 'required|max:100',
            'kelas_id' => 'required|exists:kelas,id',
            'jurusan_id' => 'required|exists:jurusans,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'tahun_masuk' => 'required',
            'status' => 'required|in:Aktif,Alumni,Nonaktif',
        ]);

        $siswa->update($request->all());
        return redirect()->route('siswa.index')->with('success', 'Siswa berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Siswa::findOrFail($id)->delete();
        return redirect()->route('siswa.index')->with('success', 'Siswa berhasil dihapus.');
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

}
