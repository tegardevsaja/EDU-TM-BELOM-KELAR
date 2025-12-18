<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Siswa;
use App\Models\CertificateHistory;
use App\Models\Penilaian;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\DB;

class DashboardKpis extends Component
{
    public string $siswaGenderFilter = 'all'; // all, L, P
    public string $userRoleFilter = 'all';   // all, master_admin, admin, guru
    public string $sertifikatRange = 'all';  // all, week, month, year

    public function setSiswaGenderFilter(string $value): void
    {
        $this->siswaGenderFilter = $value;
    }

    public function setUserRoleFilter(string $value): void
    {
        $this->userRoleFilter = $value;
    }

    public function setSertifikatRange(string $value): void
    {
        $this->sertifikatRange = $value;
    }

    public function render()
    {
        // Total pengguna (bisa difilter per role)
        if ($this->userRoleFilter !== 'all') {
            $totalUsers = User::whereHas('roles', function ($q) {
                $q->where('name', $this->userRoleFilter);
            })->count();
        } else {
            $totalUsers = User::count();
        }

        // Total siswa (bisa difilter per gender)
        if (in_array($this->siswaGenderFilter, ['L', 'P'], true)) {
            $totalSiswa = Siswa::where('jenis_kelamin', $this->siswaGenderFilter)->count();
        } else {
            $totalSiswa = Siswa::count();
        }

        // Total sertifikat dari history, dengan filter range waktu
        $totalSertifikat = 0;
        if (class_exists(CertificateHistory::class)) {
            $historyQuery = CertificateHistory::query();

            switch ($this->sertifikatRange) {
                case 'week':
                    $historyQuery->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $historyQuery->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                    break;
                case 'year':
                    $historyQuery->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]);
                    break;
                case 'all':
                default:
                    // no filter
                    break;
            }

            $totalSertifikat = (int) $historyQuery->sum('jumlah_siswa');
        }

        // Percent graded (tetap global, tidak ikut filter)
        $totalSiswaGlobal = Siswa::count();
        $percentGraded = 0;
        if ($totalSiswaGlobal > 0 && class_exists(Penilaian::class)) {
            $siswaWithGrades = Penilaian::distinct('siswa_id')->count('siswa_id');
            $percentGraded = round(($siswaWithGrades / $totalSiswaGlobal) * 100, 1);
        }

        return view('livewire.dashboard-kpis', [
            'totalUsers' => $totalUsers,
            'totalSiswa' => $totalSiswa,
            'totalSertifikat' => $totalSertifikat,
            'percentGraded' => $percentGraded,
            'userRoleFilter' => $this->userRoleFilter,
            'siswaGenderFilter' => $this->siswaGenderFilter,
            'sertifikatRange' => $this->sertifikatRange,
        ]);
    }
}
