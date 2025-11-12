<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $kelasId = $request->query('kelas_id');
        $tanggal = $request->query('tanggal');

        $sessions = AttendanceSession::with(['kelas', 'creator'])
            ->when($kelasId, fn($q) => $q->where('kelas_id', $kelasId))
            ->when($tanggal, fn($q) => $q->whereDate('tanggal', $tanggal))
            ->latest('tanggal')
            ->paginate(10)
            ->appends($request->only(['kelas_id','tanggal']));

        $kelas = Kelas::orderBy('nama_kelas')->get();

        return view('master.absensi.index', compact('sessions', 'kelas', 'kelasId', 'tanggal'));
    }

    public function create(): View
    {
        return view('master.absensi.create', [
            'kelas' => Kelas::orderBy('nama_kelas')->get(),
            'tahunAjarans' => TahunAjaran::orderBy('id', 'desc')->get(),
            'today' => now()->toDateString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string|max:255',
            'tahun_ajaran_id' => 'nullable|exists:tahun_ajarans,id',
        ]);

        // Cegah duplikasi sesi pada kombinasi kelas+tanggal
        $existing = AttendanceSession::where('kelas_id', $validated['kelas_id'])
            ->whereDate('tanggal', $validated['tanggal'])
            ->first();
        if ($existing) {
            return redirect()->route('master.absensi.edit', $existing->id)
                ->with('info', 'Sesi absensi untuk kelas dan tanggal ini sudah ada. Dialihkan ke halaman kelola.');
        }

        $session = AttendanceSession::create([
            'kelas_id' => $validated['kelas_id'],
            'tanggal' => $validated['tanggal'],
            'keterangan' => $validated['keterangan'] ?? null,
            'tahun_ajaran_id' => $validated['tahun_ajaran_id'] ?? null,
            'created_by' => Auth::id(),
        ]);

        // Seed records for all students in class as hadir by default
        $siswas = Siswa::where('kelas_id', $session->kelas_id)->get(['id']);
        foreach ($siswas as $s) {
            AttendanceRecord::create([
                'session_id' => $session->id,
                'siswa_id' => $s->id,
                'status' => 'hadir',
            ]);
        }

        return redirect()->route('master.absensi.edit', $session->id)->with('success', 'Sesi absensi dibuat.');
    }

    public function edit($id): View
    {
        $session = AttendanceSession::with(['kelas.siswas', 'records'])->findOrFail($id);
        $recordsBySiswa = $session->records->keyBy('siswa_id');

        return view('master.absensi.edit', [
            'session' => $session,
            'siswas' => $session->kelas->siswas()->orderBy('nama')->get(),
            'recordsBySiswa' => $recordsBySiswa,
        ]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $session = AttendanceSession::findOrFail($id);
        if ($session->locked) {
            return back()->with('error', 'Sesi terkunci dan tidak bisa diubah.');
        }

        // Per-student updates (PRIORITAS)
        if ($request->has('records') && is_array($request->records)) {
            foreach ($request->records as $siswaId => $data) {
                $status = $data['status'] ?? 'hadir';
                $notes = $data['notes'] ?? null;
                if (!in_array($status, ['hadir','alfa','sakit','izin'])) { continue; }
                AttendanceRecord::updateOrCreate(
                    ['session_id' => $session->id, 'siswa_id' => (int)$siswaId],
                    ['status' => $status, 'notes' => $notes]
                );
            }
        }

        // Bulk apply if provided (SETELAH per-student, hanya untuk siswa yang tidak terkirim di records)
        if ($request->filled('bulk_ids') && $request->filled('bulk_status')) {
            $ids = array_filter(explode(',', $request->bulk_ids));
            $status = $request->bulk_status;
            if (!in_array($status, ['hadir','alfa','sakit','izin'])) {
                return back()->with('error', 'Status tidak valid.');
            }
            $alreadyUpdated = $request->has('records') && is_array($request->records)
                ? array_map('intval', array_keys($request->records))
                : [];
            foreach ($ids as $siswaId) {
                $sid = (int)$siswaId;
                if (in_array($sid, $alreadyUpdated, true)) { continue; }
                AttendanceRecord::updateOrCreate(
                    ['session_id' => $session->id, 'siswa_id' => $sid],
                    ['status' => $status]
                );
            }
        }

        // Jika diminta mengunci setelah simpan
        if ($request->boolean('lock')) {
            $session->locked = true;
            $session->save();
            return back()->with('success', 'Absensi diperbarui dan sesi telah dikunci.');
        }

        return back()->with('success', 'Absensi diperbarui.');
    }

    public function lock($id): RedirectResponse
    {
        $session = AttendanceSession::findOrFail($id);
        $session->locked = true;
        $session->save();
        return back()->with('success', 'Sesi dikunci.');
    }

    public function destroy($id): RedirectResponse
    {
        $session = AttendanceSession::findOrFail($id);
        $session->delete();
        return redirect()->route('master.absensi')->with('success', 'Sesi dihapus.');
    }
}
