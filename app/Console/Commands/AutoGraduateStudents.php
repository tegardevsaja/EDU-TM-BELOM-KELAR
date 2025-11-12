<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AutoGraduateStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:auto-graduate {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically graduate students to Alumni status based on academic year end dates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        $this->info('🎓 Starting automatic student graduation process...');
        
        if ($isDryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
        }

        // Get all finished academic years (tanggal_selesai < today)
        $finishedAcademicYears = TahunAjaran::where('tanggal_selesai', '<', Carbon::today())
            ->where('aktif', false) // Only process inactive academic years
            ->get();

        if ($finishedAcademicYears->isEmpty()) {
            $this->info('✅ No finished academic years found. Nothing to process.');
            return 0;
        }

        $totalGraduated = 0;

        foreach ($finishedAcademicYears as $tahunAjaran) {
            $this->info("📅 Processing academic year: {$tahunAjaran->tahun_ajaran}");
            
            // Find students who should graduate
            $studentsToGraduate = Siswa::where('tahun_ajaran_id', $tahunAjaran->id)
                ->where('status', 'Aktif')
                ->with(['kelas', 'jurusan'])
                ->get();

            if ($studentsToGraduate->isEmpty()) {
                $this->info("   No active students found for this academic year.");
                continue;
            }

            $this->info("   Found {$studentsToGraduate->count()} students to graduate:");

            foreach ($studentsToGraduate as $siswa) {
                // Additional logic: Graduate students from grade XII (final year)
                $kelasName = $siswa->kelas ? $siswa->kelas->nama_kelas : '';
                $isGradeXII = str_contains(strtoupper($kelasName), 'XII');
                
                if ($isGradeXII) {
                    $kelasDisplay = $siswa->kelas ? $siswa->kelas->nama_kelas : 'N/A';
                    $this->line("   • {$siswa->nama} (NIS: {$siswa->nis}) - {$kelasDisplay}");
                    
                    if (!$isDryRun) {
                        $siswa->update(['status' => 'Alumni']);
                        
                        // Log the graduation
                        Log::info('Student automatically graduated', [
                            'siswa_id' => $siswa->id,
                            'nama' => $siswa->nama,
                            'nis' => $siswa->nis,
                            'kelas' => $kelasDisplay,
                            'tahun_ajaran' => $tahunAjaran->tahun_ajaran,
                            'graduated_at' => now()
                        ]);
                    }
                    
                    $totalGraduated++;
                } else {
                    // For non-XII students, check if they've been in the system for 3+ years
                    $yearsInSystem = Carbon::now()->year - $siswa->tahun_masuk;
                    
                    if ($yearsInSystem >= 3) {
                        $kelasDisplay = $siswa->kelas ? $siswa->kelas->nama_kelas : 'N/A';
                        $this->line("   • {$siswa->nama} (NIS: {$siswa->nis}) - {$kelasDisplay} [3+ years]");
                        
                        if (!$isDryRun) {
                            $siswa->update(['status' => 'Alumni']);
                            
                            Log::info('Student automatically graduated (3+ years)', [
                                'siswa_id' => $siswa->id,
                                'nama' => $siswa->nama,
                                'nis' => $siswa->nis,
                                'tahun_masuk' => $siswa->tahun_masuk,
                                'years_in_system' => $yearsInSystem,
                                'graduated_at' => now()
                            ]);
                        }
                        
                        $totalGraduated++;
                    }
                }
            }
        }

        if ($isDryRun) {
            $this->info("🔍 DRY RUN COMPLETE: {$totalGraduated} students would be graduated to Alumni status.");
        } else {
            $this->info("✅ GRADUATION COMPLETE: {$totalGraduated} students have been graduated to Alumni status.");
        }

        return 0;
    }
}
