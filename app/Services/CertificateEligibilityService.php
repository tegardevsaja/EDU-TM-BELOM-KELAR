<?php
namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\CertificateOverride;
use App\Models\Siswa;

class CertificateEligibilityService
{
    /**
     * Return true if siswa eligible untuk sertifikat pada tahun ajaran tertentu.
     * Default rules:
     * - alfa must be <= $alfaThreshold (default 3)
     * - (sakit + izin) must be <= $excusedThreshold (default 5)
     * - override granted bypasses these checks
     */
    public function isEligible(Siswa $siswa, ?int $tahunAjaranId = null, int $alfaThreshold = 3, int $excusedThreshold = 5): bool
    {
        // Cek override
        $override = CertificateOverride::where('siswa_id', $siswa->id)
            ->when($tahunAjaranId, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))
            ->first();
        if ($override && $override->granted) {
            return true;
        }

        // Ambil sesi absensi untuk tahun ajaran jika diberikan
        $sessionIds = AttendanceSession::query()
            ->when($tahunAjaranId, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))
            ->pluck('id');

        if ($sessionIds->isEmpty()) {
            // Jika belum ada data absensi, anggap eligible
            return true;
        }

        $baseQuery = AttendanceRecord::whereIn('session_id', $sessionIds)
            ->where('siswa_id', $siswa->id);

        $alfaCount = (clone $baseQuery)->where('status', 'alfa')->count();
        $sakitCount = (clone $baseQuery)->where('status', 'sakit')->count();
        $izinCount  = (clone $baseQuery)->where('status', 'izin')->count();

        if ($alfaCount > $alfaThreshold) {
            return false;
        }

        if (($sakitCount + $izinCount) > $excusedThreshold) {
            return false;
        }

        return true;
    }
}
