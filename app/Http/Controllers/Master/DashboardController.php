<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Siswa;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Total counts
        $totalUsers = User::count();
        $totalSiswa = Siswa::count();
        
        // Jika ada model Certificate/Sertifikat, uncomment baris ini:
        // $totalSertifikat = Certificate::count();
        // Untuk sementara:
        $totalSertifikat = 0;
        
        // Gender distribution
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
        
        // Recent users (last 5)
        $recentUsers = User::orderBy('created_at', 'desc')->take(5)->get();
        
        // Student status counts
        $siswaAktif = Siswa::where('status', 'aktif')->count();
        $siswaLulus = Siswa::where('status', 'lulus')->count();
        $siswaPindahKeluar = Siswa::whereIn('status', ['pindah', 'keluar'])->count();

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
            'siswaPindahKeluar'
        ));
    }
}   