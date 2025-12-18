<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Penilaian;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\CertificateTemplate;
use App\Models\CertificateHistory;
use App\Models\TemplatePenilaian;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Filter dari query string
        $userRoleFilter = $request->query('user_role', 'all'); // all, master_admin, admin, guru
        $siswaGenderFilter = $request->query('siswa_gender', 'all'); // all, L, P

        // Total pengguna (bisa difilter per role)
        if ($userRoleFilter !== 'all' && class_exists(User::class)) {
            $totalUsers = User::whereHas('roles', function ($q) use ($userRoleFilter) {
                $q->where('name', $userRoleFilter);
            })->count();
        } else {
            $totalUsers = User::count();
        }

        // Total siswa (bisa difilter per gender)
        if ($siswaGenderFilter === 'L' || $siswaGenderFilter === 'P') {
            $totalSiswa = Siswa::where('jenis_kelamin', $siswaGenderFilter)->count();
        } else {
            $totalSiswa = Siswa::count();
        }
        
        // Jika ada model Certificate/Sertifikat, hitung total sertifikat yang tercatat di history
        $totalSertifikat = 0;
        $sertifikatPerBulan = collect();
        $sertifikatPerTahun = collect();
        $sertifikatRange = $request->query('sertifikat_range', 'all'); // all, week, month, year

        if (class_exists(CertificateHistory::class)) {
            $historyQuery = CertificateHistory::query();

            // Terapkan filter range waktu untuk card total
            switch ($sertifikatRange) {
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

            // Total sertifikat = total siswa yang dicetak, bukan jumlah batch
            $totalSertifikat = (int) $historyQuery->sum('jumlah_siswa');

            // Tetap sediakan agregat per bulan & tahun untuk keperluan lain
            $sertifikatPerBulan = CertificateHistory::select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as ym'), DB::raw('COUNT(*) as c'))
                ->groupBy('ym')
                ->orderBy('ym')
                ->pluck('c', 'ym');

            $sertifikatPerTahun = CertificateHistory::select(DB::raw('YEAR(created_at) as y'), DB::raw('COUNT(*) as c'))
                ->groupBy('y')
                ->orderBy('y')
                ->pluck('c', 'y');
        }
        
        // Gender distribution (tetap global)
        $totalMale = Siswa::where('jenis_kelamin', 'L')->count();
        $totalFemale = Siswa::where('jenis_kelamin', 'P')->count();
        
        // Calculate percentages
        if ($totalSiswa > 0) {
            $malePercentage = ($totalMale / $totalSiswa) * 100;
            $femalePercentage = ($totalFemale / $totalSiswa) * 100;
        } else {
            $malePercentage = 0;
            $femalePercentage = 0;
        }
        
        // Recent users: maksimal 3, usahakan beda role
        $recentUsers = collect();
        if (class_exists(User::class) && method_exists(User::class, 'roles')) {
            $rawUsers = User::with('roles')->orderBy('created_at', 'desc')->get();
            $byRole = [];

            foreach ($rawUsers as $user) {
                $roles = $user->roles ?? collect();
                foreach ($roles as $role) {
                    $rName = $role->name ?? null;
                    if (!$rName) {
                        continue;
                    }
                    if (!array_key_exists($rName, $byRole)) {
                        $byRole[$rName] = $user;
                        break; // simpan user ini untuk role pertama yang belum terpakai
                    }
                }
                if (count($byRole) >= 3) {
                    break;
                }
            }

            $recentUsers = collect(array_values($byRole));
        } else {
            $recentUsers = User::orderBy('created_at', 'desc')->take(3)->get();
        }
        
        // Student status counts
        $siswaAktif = Siswa::where('status', 'aktif')->count();
        $siswaLulus = Siswa::where('status', 'lulus')->count();
        $siswaPindahKeluar = Siswa::whereIn('status', ['pindah', 'keluar'])->count();

        // Extended counts for dashboard overview
        $totalJurusan = class_exists(Jurusan::class) ? Jurusan::count() : 0;
        $totalKelas = class_exists(Kelas::class) ? Kelas::count() : 0;
        $totalTemplateSertifikat = class_exists(CertificateTemplate::class) ? CertificateTemplate::count() : 0;
        $totalTemplatePenilaian = class_exists(TemplatePenilaian::class) ? TemplatePenilaian::count() : 0;
        $totalPenilaian = class_exists(Penilaian::class) ? Penilaian::count() : 0;
        $totalTahunAjaran = class_exists(TahunAjaran::class) ? TahunAjaran::count() : 0;

        // Students per academic year (based on master tahun_ajarans, left join ke siswas)
        $studentsPerYear = DB::table('tahun_ajarans as ta')
            ->leftJoin('siswas as s', 'ta.id', '=', 's.tahun_ajaran_id')
            ->select('ta.tahun_ajaran as label', DB::raw('COUNT(s.id) as c'))
            ->groupBy('ta.tahun_ajaran')
            ->orderBy('ta.tahun_ajaran')
            ->pluck('c', 'label'); // ['2023/2025' => count]

        // Students per academic year by gender
        $studentsPerYearMale = DB::table('tahun_ajarans as ta')
            ->leftJoin('siswas as s', function ($join) {
                $join->on('ta.id', '=', 's.tahun_ajaran_id')
                    ->where('s.jenis_kelamin', 'L');
            })
            ->select('ta.tahun_ajaran as label', DB::raw('COUNT(s.id) as c'))
            ->groupBy('ta.tahun_ajaran')
            ->orderBy('ta.tahun_ajaran')
            ->pluck('c', 'label');

        $studentsPerYearFemale = DB::table('tahun_ajarans as ta')
            ->leftJoin('siswas as s', function ($join) {
                $join->on('ta.id', '=', 's.tahun_ajaran_id')
                    ->where('s.jenis_kelamin', 'P');
            })
            ->select('ta.tahun_ajaran as label', DB::raw('COUNT(s.id) as c'))
            ->groupBy('ta.tahun_ajaran')
            ->orderBy('ta.tahun_ajaran')
            ->pluck('c', 'label');

        // Percent graded: siswa yang punya penilaian / total siswa
        $percentGraded = 0;
        if ($totalSiswa > 0 && class_exists(Penilaian::class)) {
            $siswaWithGrades = Penilaian::distinct('siswa_id')->count('siswa_id');
            $percentGraded = round(($siswaWithGrades / $totalSiswa) * 100, 1);
        }

        // Recent penilaian (join siswa + kelas + jurusan + template jika ada)
        $recentPenilaian = collect();
        if (class_exists(Penilaian::class)) {
            $recentPenilaian = Penilaian::with(['siswa.kelas', 'siswa.jurusan'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        }

        // Daftar kelas terbaru (dengan jurusan)
        $recentKelas = class_exists(Kelas::class)
            ? Kelas::with('jurusan')->orderBy('created_at', 'desc')->take(5)->get()
            : collect();

        // Daftar siswa terbaru (dengan kelas)
        $recentSiswa = class_exists(Siswa::class)
            ? Siswa::with('kelas')->orderBy('created_at', 'desc')->take(5)->get()
            : collect();

        return view('master.dashboard', compact(
            'totalUsers',
            'totalSiswa',
            'totalSertifikat',
            'totalMale',
            'totalFemale',
            'malePercentage',
            'femalePercentage',
            'recentUsers',
            'siswaAktif',
            'siswaLulus',
            'siswaPindahKeluar',
            'totalJurusan',
            'totalKelas',
            'totalTemplateSertifikat',
            'totalTemplatePenilaian',
            'totalPenilaian',
            'totalTahunAjaran',
            'studentsPerYear',
            'studentsPerYearMale',
            'studentsPerYearFemale',
            'percentGraded',
            'sertifikatPerBulan',
            'sertifikatPerTahun',
            'recentPenilaian',
            'recentKelas',
            'recentSiswa',
            'userRoleFilter',
            'siswaGenderFilter'
        ));
    }
}