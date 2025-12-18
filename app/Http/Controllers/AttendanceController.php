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
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceMonthlyExport;
use App\Exports\AttendanceWeeklyExport;
use App\Exports\AttendanceYearlyExport;
use App\Exports\AttendanceSchoolExport;
use App\Exports\AttendanceDetailExport;

class AttendanceController extends Controller
{
    private function routeBase(): string
    {
        $r = request();
        if ($r->routeIs('admin.*')) return 'admin';
        if ($r->routeIs('guru.*')) return 'guru';
        return 'master';
    }

    public function index(Request $request): View
    {
        $kelasId = $request->query('kelas_id');
        $tanggal = $request->query('tanggal');

        $query = AttendanceSession::with(['kelas', 'creator']);

        // Filter per role: guru hanya lihat sesi yang dia buat atau kelas yang menjadi wali-nya
        $user = Auth::user();
        if ($user && $user->hasRole('guru')) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhereHas('kelas', function ($q2) use ($user) {
                      $q2->where('wali_kelas_id', $user->id);
                  });
            });
        }

        $sessions = $query
            ->when($kelasId, fn($q) => $q->where('kelas_id', $kelasId))
            ->when($tanggal, fn($q) => $q->whereDate('tanggal', $tanggal))
            ->latest('tanggal')
            ->paginate(10)
            ->appends($request->only(['kelas_id','tanggal']));

        $kelas = Kelas::orderBy('nama_kelas')->get();

        return view('absensi.index', compact('sessions', 'kelas', 'kelasId', 'tanggal'));
    }

    public function exportMonthly(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'month' => 'required|date_format:Y-m',
            'format' => 'sometimes|in:xlsx,csv',
        ]);

        $kelasId = (int) $validated['kelas_id'];
        $month = $validated['month'];

        // Hanya admin/master yang boleh melihat semua; guru tetap dibatasi oleh filter yang sama
        $user = Auth::user();
        if ($user && $user->hasRole('guru')) {
            $allowed = AttendanceSession::where('kelas_id', $kelasId)
                ->whereYear('tanggal', substr($month, 0, 4))
                ->whereMonth('tanggal', substr($month, 5, 2))
                ->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhereHas('kelas', function ($q2) use ($user) {
                          $q2->where('wali_kelas_id', $user->id);
                      });
                })
                ->exists();

            if (! $allowed) {
                abort(403, 'Anda tidak memiliki akses ke rekap kelas ini.');
            }
        }

        $kelas = Kelas::with('jurusan')->findOrFail($kelasId);
        $ext = $validated['format'] ?? 'xlsx';
        $fileName = sprintf('rekap-absensi-%s-%s.%s',
            str_replace(' ', '-', strtolower($kelas->nama_kelas)),
            $month,
            $ext
        );

        if ($request->boolean('detail')) {
            $detailName = str_replace('.' . $ext, '-detail.' . $ext, $fileName);
            return Excel::download(new AttendanceDetailExport($kelasId, 'month', $month), $detailName);
        }
        return Excel::download(new AttendanceMonthlyExport($kelasId, $month), $fileName);
    }

    public function exportWeekly(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'sometimes|in:xlsx,csv',
        ]);

        $kelasId = (int) $validated['kelas_id'];
        $start = $validated['start_date'];
        $end = $validated['end_date'];

        $user = Auth::user();
        if ($user && $user->hasRole('guru')) {
            $allowed = AttendanceSession::where('kelas_id', $kelasId)
                ->whereBetween('tanggal', [$start, $end])
                ->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhereHas('kelas', function ($q2) use ($user) {
                          $q2->where('wali_kelas_id', $user->id);
                      });
                })
                ->exists();
            if (! $allowed) abort(403, 'Anda tidak memiliki akses ke rekap kelas ini.');
        }

        $kelas = Kelas::with('jurusan')->findOrFail($kelasId);
        $ext = $validated['format'] ?? 'xlsx';
        $fileName = sprintf('rekap-absensi-mingguan-%s-%s_sampai_%s.%s',
            str_replace(' ', '-', strtolower($kelas->nama_kelas)),
            $start,
            $end,
            $ext
        );

        if ($request->boolean('detail')) {
            $detailName = str_replace('.' . $ext, '-detail.' . $ext, $fileName);
            return Excel::download(new AttendanceDetailExport($kelasId, 'week', null, $start, $end), $detailName);
        }
        return Excel::download(new AttendanceWeeklyExport($kelasId, $start, $end), $fileName);
    }

    public function exportYearly(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'year' => 'required|integer|min:2000|max:2100',
            'format' => 'sometimes|in:xlsx,csv',
        ]);

        $kelasId = (int) $validated['kelas_id'];
        $year = (int) $validated['year'];

        $user = Auth::user();
        if ($user && $user->hasRole('guru')) {
            $allowed = AttendanceSession::where('kelas_id', $kelasId)
                ->whereYear('tanggal', $year)
                ->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhereHas('kelas', function ($q2) use ($user) {
                          $q2->where('wali_kelas_id', $user->id);
                      });
                })
                ->exists();
            if (! $allowed) abort(403, 'Anda tidak memiliki akses ke rekap kelas ini.');
        }

        $kelas = Kelas::with('jurusan')->findOrFail($kelasId);
        $ext = $validated['format'] ?? 'xlsx';
        $fileName = sprintf('rekap-absensi-tahunan-%s-%d.%s',
            str_replace(' ', '-', strtolower($kelas->nama_kelas)),
            $year,
            $ext
        );

        if ($request->boolean('detail')) {
            $detailName = str_replace('.' . $ext, '-detail.' . $ext, $fileName);
            return Excel::download(new AttendanceDetailExport($kelasId, 'year', (string)$year), $detailName);
        }
        return Excel::download(new AttendanceYearlyExport($kelasId, $year), $fileName);
    }

    public function exportSchool(Request $request)
    {
        $validated = $request->validate([
            'period_type' => 'required|in:week,month,year,range',
            'period_value' => 'nullable|string', // Y-m for month, Y for year
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'format' => 'sometimes|in:xlsx,csv',
        ]);

        $user = Auth::user();
        if ($user && $user->hasRole('guru')) {
            abort(403, 'Export seluruh sekolah hanya untuk admin.');
        }

        $type = $validated['period_type'];
        $value = $validated['period_value'] ?? null;
        $start = $validated['start_date'] ?? null;
        $end = $validated['end_date'] ?? null;

        if (in_array($type, ['week','range']) && (! $start || ! $end)) {
            abort(422, 'Tanggal mulai dan akhir diperlukan untuk periode mingguan/range.');
        }
        if ($type === 'month' && (! $value || ! preg_match('/^\\d{4}-\\d{2}$/', $value))) {
            abort(422, 'Format bulan harus Y-m.');
        }
        if ($type === 'year' && (! $value || ! preg_match('/^\\d{4}$/', $value))) {
            abort(422, 'Format tahun harus YYYY.');
        }

        $ext = $validated['format'] ?? 'xlsx';
        $label = match ($type) {
            'month' => $value,
            'year' => $value,
            'week', 'range' => ($start . '_sampai_' . $end),
        };
        $fileName = sprintf('rekap-absensi-sekolah-%s.%s', $label, $ext);

        if ($request->boolean('detail')) {
            $detailName = str_replace('.' . $ext, '-detail.' . $ext, $fileName);
            return Excel::download(new AttendanceDetailExport(null, $type, $value, $start, $end), $detailName);
        }
        return Excel::download(new AttendanceSchoolExport($type, $value, $start, $end), $fileName);
    }

    public function create(): View
    {
        return view('absensi.create', [
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
            $base = $this->routeBase();
            return redirect()->route($base.'.absensi.edit', $existing->id)
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

        $base = $this->routeBase();
        return redirect()->route($base.'.absensi.edit', $session->id)->with('success', 'Sesi absensi dibuat.');
    }

    public function edit($id): View
    {
        $session = AttendanceSession::with(['kelas.siswas', 'records'])->findOrFail($id);
        $recordsBySiswa = $session->records->keyBy('siswa_id');

        return view('absensi.edit', [
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
        $base = $this->routeBase();
        return redirect()->route($base.'.absensi')->with('success', 'Sesi dihapus.');
    }
}
