<?php

namespace App\Http\Controllers;

use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class TahunAjaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tahunAjaran = TahunAjaran::latest()->paginate(10);
        return view('master.tahun-ajaran.index', compact('tahunAjaran'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('master.tahun-ajaran.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tahun_ajaran' => 'required|string|max:15|unique:tahun_ajarans',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'aktif' => 'nullable|boolean',
        ]);

        // Multiple tahun ajaran aktif diperbolehkan
        TahunAjaran::create($request->only('tahun_ajaran', 'tanggal_mulai', 'tanggal_selesai', 'aktif'));

        return redirect()->route('master.tahunAjaran')
                         ->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $tahunAjaran = TahunAjaran::findOrFail($id);
        return view('master.tahun-ajaran.edit', compact('tahunAjaran'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $tahunAjaran = TahunAjaran::findOrFail($id);

        $request->validate([
            'tahun_ajaran' => 'required|string|max:15|unique:tahun_ajarans,tahun_ajaran,' . $tahunAjaran->id,
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'aktif' => 'nullable|boolean',
        ]);

        // Multiple tahun ajaran aktif diperbolehkan - tidak perlu deactivate yang lain
        $tahunAjaran->update($request->only('tahun_ajaran', 'tanggal_mulai', 'tanggal_selesai', 'aktif'));

        return redirect()->route('master.tahunAjaran')
                         ->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        TahunAjaran::findOrFail($id)->delete();

        return redirect()->route('master.tahunAjaran')
                         ->with('success', 'Tahun ajaran berhasil dihapus.');
    }
}
