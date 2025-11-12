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
     * Aturan default: alfa > $threshold => tidak eligible, kecuali ada override granted.
     */
    public function isEligible(Siswa $siswa, ?int $tahunAjaranId = null, int $threshold = 3): bool
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

        $alfaCount = AttendanceRecord::whereIn('session_id', $sessionIds)
            ->where('siswa_id', $siswa->id)
            ->where('status', 'alfa')
            ->count();

        return $alfaCount <= $threshold;
    }
}
