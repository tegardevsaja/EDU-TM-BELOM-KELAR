<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengguna;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PenggunaImport;
use App\Exports\PenggunaExport;

class PenggunaController extends Controller
{
    /**
     * Tampilkan daftar pengguna
     */
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $pengguna = Pengguna::query()
            ->when($q !== '', function ($query) use ($q) {
                $terms = preg_split('/\s+/', $q);
                $query->where(function ($outer) use ($terms) {
                    foreach ($terms as $term) {
                        if ($term === '') continue;
                        $outer->where(function ($sub) use ($term) {
                            $sub->where('nama', 'like', "%{$term}%")
                                ->orWhere('email', 'like', "%{$term}%")
                                ->orWhere('nik', 'like', "%{$term}%");
                        });
                    }
                });
            })
            ->latest()
            ->paginate(10)
            ->appends(['q' => $q]);

        return view(role_view('pengguna.index'), compact('pengguna', 'q'));
    }

    /**
     * Tampilkan form tambah pengguna
     */
    public function create(): View
    {
        return view(role_view('pengguna.create'));
    }

    /**
     * Simpan data pengguna baru
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama'  => 'required|string|max:100',
            'email' => 'required|email|unique:penggunas,email',
            'nik'   => 'nullable|string|max:30',
        ]);

        Pengguna::create($validated);

        return redirect()
            ->route('pengguna.index')
            ->with('success', 'Data pengguna berhasil disimpan!');
    }

    /**
     * Tampilkan form edit pengguna
     */
    public function edit($id): View
    {
        $pengguna = Pengguna::findOrFail($id);
        return view(role_view('pengguna.edit'), compact('pengguna'));
    }

    /**
     * Update data pengguna
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $pengguna = Pengguna::findOrFail($id);

        $validated = $request->validate([
            'nama'  => 'required|string|max:100',
            'email' => 'required|email|unique:penggunas,email,' . $pengguna->id,
            'nik'   => 'nullable|string|max:30',
        ]);

        $pengguna->update($validated);

        return redirect()
            ->route('pengguna.index')
            ->with('success', 'Data pengguna berhasil diperbarui!');
    }

    /**
     * Hapus data pengguna
     */
    public function destroy($id): RedirectResponse
    {
        $pengguna = Pengguna::findOrFail($id);
        $pengguna->delete();

        return redirect()
            ->route('pengguna.index')
            ->with('success', 'Data pengguna berhasil dihapus!');
    }

    /**
     * Import data pengguna dari file Excel
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        Excel::import(new PenggunaImport, $request->file('file'));

        return redirect()
            ->route('pengguna.index')
            ->with('success', 'Data pengguna berhasil diimport!');
    }

    /**
     * Export data pengguna ke file Excel
     */
    public function export()
    {
        $fileName = 'data_pengguna_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new PenggunaExport, $fileName);
    }

    /**
     * Unduh template format Excel
     */
    public function downloadTemplate()
    {
        $file = public_path('template/format/Format-Data-Pengguna.xlsx');

        if (file_exists($file)) {
            return response()->download($file, 'Format-Data-Pengguna.xlsx');
        }

        return redirect()
            ->back()
            ->with('error', 'Template file tidak ditemukan.');
    }
}
