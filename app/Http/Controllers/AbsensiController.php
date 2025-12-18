<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AbsensiController extends Controller
{
    public function index(Request $request): View
    {
        $kelasList = collect();
        $sessions = collect();
        try {
            if (class_exists(\App\Models\Kelas::class)) {
                $kelasList = \App\Models\Kelas::orderBy('nama_kelas')->get();
            }
            if (class_exists(\App\Models\AbsensiSession::class)) {
                $q = \App\Models\AbsensiSession::query();
                if ($request->filled('kelas_id') && $request->kelas_id !== 'all') {
                    $q->where('kelas_id', (int)$request->kelas_id);
                }
                if ($request->filled('tanggal')) {
                    $q->whereDate('tanggal', $request->tanggal);
                }
                $sessions = $q->latest()->paginate(10)->appends($request->query());
            }
        } catch (\Throwable $e) {
            $kelasList = collect();
            $sessions = collect();
        }

        return view('absensi.index', compact('kelasList', 'sessions'));
    }

    public function create(): View
    {
        $kelasList = collect();
        try { if (class_exists(\App\Models\Kelas::class)) { $kelasList = \App\Models\Kelas::orderBy('nama_kelas')->get(); } } catch (\Throwable $e) {}
        return view('absensi.create', compact('kelasList'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kelas_id' => 'required|integer',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string|max:255',
        ]);
        try {
            if (class_exists(\App\Models\AbsensiSession::class)) {
                \App\Models\AbsensiSession::create([
                    'kelas_id' => (int)$validated['kelas_id'],
                    'tanggal' => $validated['tanggal'],
                    'keterangan' => $validated['keterangan'] ?? null,
                    'created_by' => auth()->id(),
                ]);
                return back()->with('success', 'Sesi absensi berhasil dibuat.');
            }
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membuat sesi (migrasi belum dijalankan).');
        }
        return back();
    }
}
