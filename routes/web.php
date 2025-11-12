<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Enums\UserRole;

// Controllers
use  App\Http\Controllers\Master\DashboardController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\JurusanController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\SiswaImportController;
use App\Http\Controllers\TahunAjaranController;
use App\Http\Controllers\TemplatePenilaianController;
use App\Http\Controllers\PenilaianController;
use App\Http\Controllers\CertificateTemplateController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\CertificateGenerationController;

// Livewire auth components
use App\Livewire\Auth\VerifyOtp;
use App\Livewire\Auth\RequestOtp;
use App\Livewire\Auth\ResetWithOtp;


use App\Http\Controllers\Auth\OtpRequestController;

// Livewire settings components
use App\Livewire\Settings\{Appearance, Password, Profile, TwoFactor};

Route::get('/', fn() => view('welcome'))->name('home');

Route::get('/login', fn() => view('auth.login'))->name('login');

Route::middleware('guest')->group(function () {
    Route::get('/request-otp', [OtpRequestController::class, 'showForm'])->name('request.otp');
    Route::post('/request-otp', [OtpRequestController::class, 'sendOtp'])->name('send.otp.post');

    Route::get('/verify-otp/{email}', VerifyOtp::class)->name('verify.otp');

    Route::get('/reset-password/{email}', ResetWithOtp::class)->name('reset.password.form');

});


// Dashboard (authenticated)
Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


// Protected Routes (role-based via Spatie permissions)
Route::middleware(['auth', 'verified'])
    ->prefix('master')
    ->name('master.')
    ->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    // route pengguna 
    Route::get('/pengguna', [PenggunaController::class, 'index'])->middleware('permission:pengguna.view')->name('pengguna');
    Route::get('/pengguna/create', [PenggunaController::class, 'create'])->middleware('permission:pengguna.create')->name('pengguna.create');
    Route::post('/pengguna', [PenggunaController::class, 'store'])->middleware('permission:pengguna.create')->name('pengguna.store');
    Route::get('/pengguna/{id}/edit', [PenggunaController::class, 'edit'])->middleware('permission:pengguna.update')->name('pengguna.edit');
    Route::put('/pengguna/{id}', [PenggunaController::class, 'update'])->middleware('permission:pengguna.update')->name('pengguna.update');
    Route::delete('/pengguna/{id}', [PenggunaController::class, 'destroy'])->middleware('permission:pengguna.delete')->name('pengguna.destroy');
    Route::post('/pengguna/import', [PenggunaController::class, 'import'])->middleware('permission:pengguna.import')->name('pengguna.import');
    Route::get('/pengguna/export', [PenggunaController::class, 'export'])->middleware('permission:pengguna.export')->name('pengguna.export');
    Route::get('/pengguna/template-download', [PenggunaController::class, 'downloadTemplate'])->middleware('permission:pengguna.template')->name('pengguna.template');

    // route user 
    Route::get('/users', [UserController::class, 'index'])->middleware('permission:users.view')->name('users');
    Route::get('/users/create', [UserController::class, 'create'])->middleware('permission:users.create')->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->middleware('permission:users.create')->name('users.store');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->middleware('permission:users.update')->name('users.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])->middleware('permission:users.update')->name('users.update');
    Route::delete('/users', [UserController::class, 'destroy'])->middleware('permission:users.delete')->name('users.destroy');

    // route jurusan 
    Route::get('/jurusan', [JurusanController::class, 'index'])->middleware('permission:jurusan.view')->name('jurusan');
    Route::get('/jurusan/create', [JurusanController::class, 'create'])->middleware('permission:jurusan.create')->name('jurusan.create');
    Route::post('/jurusan', [JurusanController::class, 'store'])->middleware('permission:jurusan.create')->name('jurusan.store');
    Route::get('/jurusan/{id}/edit', [JurusanController::class, 'edit'])->middleware('permission:jurusan.update')->name('jurusan.edit');
    Route::put('/jurusan/{id}', [JurusanController::class, 'update'])->middleware('permission:jurusan.update')->name('jurusan.update');
    Route::delete('/jurusan/{id}', [JurusanController::class, 'destroy'])->middleware('permission:jurusan.delete')->name('jurusan.destroy');

    // route kelas 
    Route::get('/kelas', [KelasController::class, 'index'])->middleware('permission:kelas.view')->name('kelas');
    Route::get('/kelas/create', [KelasController::class, 'create'])->middleware('permission:kelas.create')->name('kelas.create');
    Route::post('/kelas', [KelasController::class, 'store'])->middleware('permission:kelas.create')->name('kelas.store');
    Route::get('/kelas/{id}/edit', [KelasController::class, 'edit'])->middleware('permission:kelas.update')->name('kelas.edit');
    Route::put('/kelas/{id}', [KelasController::class, 'update'])->middleware('permission:kelas.update')->name('kelas.update');
    Route::delete('/kelas/{id}', [KelasController::class, 'destroy'])->middleware('permission:kelas.delete')->name('kelas.destroy');

    // route siswa 
    Route::get('/siswa', [SiswaController::class, 'index'])->middleware('permission:siswa.view')->name('siswa.index');
    Route::get('/siswa/create', [SiswaController::class, 'create'])->middleware('permission:siswa.create')->name('siswa.create');
    Route::post('/siswa', [SiswaController::class, 'store'])->middleware('permission:siswa.create')->name('siswa.store');
    Route::get('/siswa/{id}/edit', [SiswaController::class, 'edit'])->middleware('permission:siswa.update')->name('siswa.edit');
    Route::put('/siswa/{id}', [SiswaController::class, 'update'])->middleware('permission:siswa.update')->name('siswa.update'); 
    Route::delete('/siswa/{id}', [SiswaController::class, 'destroy'])->middleware('permission:siswa.delete')->name('siswa.destroy');

    Route::get('/siswa/template-download', [SiswaController::class, 'downloadTemplate'])->middleware('permission:siswa.template')->name('siswa.template');
    Route::get('siswa/import', [SiswaImportController::class, 'showImportForm'])->middleware('permission:siswa.import')->name('master.siswa.import');
    Route::post('/siswa/import/preview', [SiswaImportController::class, 'previewImport'])->middleware('permission:siswa.import')->name('siswa.import.preview');
    Route::post('/siswa/import/store', [SiswaImportController::class, 'storeImport'])->middleware('permission:siswa.import')->name('siswa.import.store');
    
    // route tahun ajaran 
    Route::get('/tahunajaran', [TahunAjaranController::class, 'index'])->middleware('permission:tahunAjaran.view')->name('tahunAjaran');
    Route::get('/tahun-ajaran/create', [TahunAjaranController::class, 'create'])->middleware('permission:tahunAjaran.create')->name('tahun-ajaran.create');
    Route::post('/tahun-ajaran', [TahunAjaranController::class, 'store'])->middleware('permission:tahunAjaran.create')->name('tahun-ajaran.store');
    Route::get('/tahun-ajaran/{id}/edit', [TahunAjaranController::class, 'edit'])->middleware('permission:tahunAjaran.update')->name('tahun-ajaran.edit');
    Route::put('/tahun-ajaran/{id}', [TahunAjaranController::class, 'update'])->middleware('permission:tahunAjaran.update')->name('tahun-ajaran.update');
    Route::delete('/tahun-ajaran/{id}', [TahunAjaranController::class, 'destroy'])->middleware('permission:tahunAjaran.delete')->name('tahun-ajaran.destroy');

    // route penilaian 
    Route::get('/penilaian', [TemplatePenilaianController::class, 'index'])->middleware('permission:penilaian.view')->name('penilaian');
    Route::get('/penilaian/create', [TemplatePenilaianController::class, 'create'])->middleware('permission:penilaian.create')->name('penilaian.create');
    Route::post('/penilaian', [TemplatePenilaianController::class, 'store'])->middleware('permission:penilaian.create')->name('penilaian.store');
    Route::get('/penilaian/{id}/edit', [TemplatePenilaianController::class, 'edit'])->middleware('permission:penilaian.update')->name('penilaian.edit');
    Route::put('/penilaian/{id}', [TemplatePenilaianController::class, 'update'])->middleware('permission:penilaian.update')->name('penilaian.update'); 
    Route::delete('/penilaian/{id}', [TemplatePenilaianController::class, 'destroy'])->middleware('permission:penilaian.delete')->name('penilaian.destroy');

    Route::get('/nilai', [PenilaianController::class, 'index'])->middleware('permission:nilai.view')->name('nilai.index');
    Route::get('/nilai/create/{templateId}', [PenilaianController::class, 'create'])->middleware('permission:nilai.create')->name('nilai.create');
    Route::post('/nilai/{templateId}', [PenilaianController::class, 'store'])->middleware('permission:nilai.create')->name('penilaian.store');

    // route absensi
    Route::get('/absensi', [AttendanceController::class, 'index'])->middleware('permission:absensi.view')->name('absensi');
    Route::get('/absensi/create', [AttendanceController::class, 'create'])->middleware('permission:absensi.create')->name('absensi.create');
    Route::post('/absensi', [AttendanceController::class, 'store'])->middleware('permission:absensi.create')->name('absensi.store');
    Route::get('/absensi/{id}/edit', [AttendanceController::class, 'edit'])->middleware('permission:absensi.update')->name('absensi.edit');
    Route::put('/absensi/{id}', [AttendanceController::class, 'update'])->middleware('permission:absensi.update')->name('absensi.update');
    Route::post('/absensi/{id}/lock', [AttendanceController::class, 'lock'])->middleware('permission:absensi.lock')->name('absensi.lock');
    Route::delete('/absensi/{id}', [AttendanceController::class, 'destroy'])->middleware('permission:absensi.delete')->name('absensi.destroy');

    Route::get('/sertifikat/template', [CertificateTemplateController::class, 'index'])->middleware('permission:sertifikat_template.view')->name('sertifikat.index');
    Route::post('/sertifikat/template/store', [CertificateTemplateController::class, 'store'])->middleware('permission:sertifikat_template.create')->name('sertifikat.store');
    Route::get('/sertifikat/template/{id}/edit', [CertificateTemplateController::class, 'edit'])->middleware('permission:sertifikat_template.update')->name('sertifikat.edit');
    Route::put('/sertifikat/template/{id}', [CertificateTemplateController::class, 'update'])->middleware('permission:sertifikat_template.update')->name('sertifikat.update');
    Route::delete('/sertifikat/template/{id}', [CertificateTemplateController::class, 'destroy'])->middleware('permission:sertifikat_template.delete')->name('sertifikat.destroy');
    Route::get('/sertifikat/template/create', [CertificateTemplateController::class, 'create'])->middleware('permission:sertifikat_template.create')->name('sertifikat.create');

// Certificate Generation Flow
Route::prefix('sertifikat')->name('sertifikat.')->middleware('permission:menu.sertifikat')->group(function () {
    // STEP 1 – Pilih Template
    Route::get('/select-template', [CertificateGenerationController::class, 'stepSelectTemplate'])
        ->name('select_template');

    // STEP 2 – Pilih Template Nilai (opsional)
    Route::get('/select-grade/{template_id}', [CertificateGenerationController::class, 'stepSelectGrade'])
        ->name('select_grade');

    // STEP 2.5 – Pilih Siswa (opsional)
    Route::get('/select-students/{template_id}', [CertificateGenerationController::class, 'stepSelectStudents'])
        ->name('select_students');

    // STEP 3 – Pilih Kelas (untuk filter siswa)
    Route::get('/generate/select-class/{template_id}', [CertificateGenerationController::class, 'stepSelectClass'])
        ->name('generate.select-class');

    // STEP 3.5 – Customisasi
    Route::get('/generate/customize/{template_id}', [CertificateGenerationController::class, 'customize'])
        ->name('generate.customize');
    // Save grades from customize (uses same controller as penilaian, but allowed via sertifikat permission)
    Route::post('/grade/save/{templateId}', [PenilaianController::class, 'store'])
        ->name('grade.store');
    // Guard: if user hits the POST URL via GET, just redirect back to customize
    Route::get('/grade/save/{templateId}', function() {
        return back()->with('error', 'Akses tidak valid. Silakan simpan nilai melalui tombol Submit.');
    });
    
    // 🔥 ROUTE PENTING INI YANG HILANG - SAVE CUSTOMIZATION
    Route::post('/generate/customize/{template_id}', [CertificateGenerationController::class, 'saveCustomization'])
        ->name('generate.save-customization');

    // STEP 4 – Preview
    Route::get('/generate/preview/{template_id}', [CertificateGenerationController::class, 'preview'])
        ->name('generate.preview');

    // STEP 5 – Generate/Download
    Route::post('/generate/process', [CertificateGenerationController::class, 'generate'])
        ->name('generate.process');

    // Eligibility override
    Route::post('/eligibility/override', [CertificateGenerationController::class, 'override'])
        ->name('eligibility.override');
    
    // Fallback GET route untuk eligibility override (redirect ke preview)
    Route::get('/eligibility/override', function() {
        return redirect()->route('master.sertifikat.select_template')
            ->with('error', 'Akses tidak valid. Silakan gunakan form override di halaman preview.');
    });

    // History
    Route::get('/generate/history', [CertificateGenerationController::class, 'history'])
        ->name('generate.history');
});

    // Role-Menu Permissions Management (Master Admin only)
    Route::middleware('role:master_admin')->group(function () {
        Route::get('/permissions', [\App\Http\Controllers\RolePermissionController::class, 'index'])->name('permissions.index');
        Route::post('/permissions/{role}', [\App\Http\Controllers\RolePermissionController::class, 'update'])->name('permissions.update');
    });
});

// Admin Routes
Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::view('/dashboard', 'admin.dashboard')->name('dashboard');
        
        // Reuse same controllers as master, but with admin prefix
        Route::get('/pengguna', [PenggunaController::class, 'index'])->middleware('permission:pengguna.view')->name('pengguna');
        Route::get('/pengguna/create', [PenggunaController::class, 'create'])->middleware('permission:pengguna.create')->name('pengguna.create');
        Route::post('/pengguna', [PenggunaController::class, 'store'])->middleware('permission:pengguna.create')->name('pengguna.store');
        Route::get('/pengguna/{id}/edit', [PenggunaController::class, 'edit'])->middleware('permission:pengguna.update')->name('pengguna.edit');
        Route::put('/pengguna/{id}', [PenggunaController::class, 'update'])->middleware('permission:pengguna.update')->name('pengguna.update');
        Route::delete('/pengguna/{id}', [PenggunaController::class, 'destroy'])->middleware('permission:pengguna.delete')->name('pengguna.destroy');
        Route::post('/pengguna/import', [PenggunaController::class, 'import'])->middleware('permission:pengguna.import')->name('pengguna.import');
        Route::get('/pengguna/export', [PenggunaController::class, 'export'])->middleware('permission:pengguna.export')->name('pengguna.export');
        Route::get('/pengguna/template-download', [PenggunaController::class, 'downloadTemplate'])->middleware('permission:pengguna.template')->name('pengguna.template');
        
        Route::get('/users', [UserController::class, 'index'])->middleware('permission:users.view')->name('users');
        Route::get('/users/create', [UserController::class, 'create'])->middleware('permission:users.create')->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->middleware('permission:users.create')->name('users.store');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])->middleware('permission:users.update')->name('users.edit');
        Route::put('/users', [UserController::class, 'update'])->middleware('permission:users.update')->name('users.update');
        Route::delete('/users', [UserController::class, 'destroy'])->middleware('permission:users.delete')->name('users.destroy');
        
        Route::get('/jurusan', [JurusanController::class, 'index'])->middleware('permission:jurusan.view')->name('jurusan');
        Route::get('/jurusan/create', [JurusanController::class, 'create'])->middleware('permission:jurusan.create')->name('jurusan.create');
        Route::post('/jurusan', [JurusanController::class, 'store'])->middleware('permission:jurusan.create')->name('jurusan.store');
        Route::get('/jurusan/{id}/edit', [JurusanController::class, 'edit'])->middleware('permission:jurusan.update')->name('jurusan.edit');
        Route::put('/jurusan/{id}', [JurusanController::class, 'update'])->middleware('permission:jurusan.update')->name('jurusan.update');
        Route::delete('/jurusan/{id}', [JurusanController::class, 'destroy'])->middleware('permission:jurusan.delete')->name('jurusan.destroy');
        
        Route::get('/kelas', [KelasController::class, 'index'])->middleware('permission:kelas.view')->name('kelas');
        Route::get('/kelas/create', [KelasController::class, 'create'])->middleware('permission:kelas.create')->name('kelas.create');
        Route::post('/kelas', [KelasController::class, 'store'])->middleware('permission:kelas.create')->name('kelas.store');
        Route::get('/kelas/{id}/edit', [KelasController::class, 'edit'])->middleware('permission:kelas.update')->name('kelas.edit');
        Route::put('/kelas/{id}', [KelasController::class, 'update'])->middleware('permission:kelas.update')->name('kelas.update');
        Route::delete('/kelas/{id}', [KelasController::class, 'destroy'])->middleware('permission:kelas.delete')->name('kelas.destroy');
        
        Route::get('/siswa', [SiswaController::class, 'index'])->middleware('permission:siswa.view')->name('siswa.index');
        Route::get('/siswa/create', [SiswaController::class, 'create'])->middleware('permission:siswa.create')->name('siswa.create');
        Route::post('/siswa', [SiswaController::class, 'store'])->middleware('permission:siswa.create')->name('siswa.store');
        Route::get('/siswa/{id}/edit', [SiswaController::class, 'edit'])->middleware('permission:siswa.update')->name('siswa.edit');
        Route::put('/siswa/{id}', [SiswaController::class, 'update'])->middleware('permission:siswa.update')->name('siswa.update');
        Route::delete('/siswa/{id}', [SiswaController::class, 'destroy'])->middleware('permission:siswa.delete')->name('siswa.destroy');
        
        Route::get('/tahunajaran', [TahunAjaranController::class, 'index'])->middleware('permission:tahunAjaran.view')->name('tahunAjaran');
        Route::get('/tahun-ajaran/create', [TahunAjaranController::class, 'create'])->middleware('permission:tahunAjaran.create')->name('tahun-ajaran.create');
        Route::post('/tahun-ajaran', [TahunAjaranController::class, 'store'])->middleware('permission:tahunAjaran.create')->name('tahun-ajaran.store');
        Route::get('/tahun-ajaran/{id}/edit', [TahunAjaranController::class, 'edit'])->middleware('permission:tahunAjaran.update')->name('tahun-ajaran.edit');
        Route::put('/tahun-ajaran/{id}', [TahunAjaranController::class, 'update'])->middleware('permission:tahunAjaran.update')->name('tahun-ajaran.update');
        Route::delete('/tahun-ajaran/{id}', [TahunAjaranController::class, 'destroy'])->middleware('permission:tahunAjaran.delete')->name('tahun-ajaran.destroy');
        
        Route::get('/penilaian', [TemplatePenilaianController::class, 'index'])->middleware('permission:penilaian.view')->name('penilaian');
        Route::get('/nilai', [PenilaianController::class, 'index'])->middleware('permission:nilai.view')->name('nilai.index');
        
        Route::get('/sertifikat', [CertificateTemplateController::class, 'index'])->middleware('permission:sertifikat.view')->name('sertifikat.index');
    });

// Guru Routes
Route::middleware(['auth', 'verified'])
    ->prefix('guru')
    ->name('guru.')
    ->group(function () {
        Route::view('/dashboard', 'guru.dashboard')->name('dashboard');
        
        // Reuse same controllers as master, but with guru prefix
        Route::get('/pengguna', [PenggunaController::class, 'index'])->middleware('permission:pengguna.view')->name('pengguna');
        Route::get('/pengguna/create', [PenggunaController::class, 'create'])->middleware('permission:pengguna.create')->name('pengguna.create');
        Route::post('/pengguna', [PenggunaController::class, 'store'])->middleware('permission:pengguna.create')->name('pengguna.store');
        Route::get('/pengguna/{id}/edit', [PenggunaController::class, 'edit'])->middleware('permission:pengguna.update')->name('pengguna.edit');
        Route::put('/pengguna/{id}', [PenggunaController::class, 'update'])->middleware('permission:pengguna.update')->name('pengguna.update');
        Route::delete('/pengguna/{id}', [PenggunaController::class, 'destroy'])->middleware('permission:pengguna.delete')->name('pengguna.destroy');
        
        Route::get('/users', [UserController::class, 'index'])->middleware('permission:users.view')->name('users');
        Route::get('/users/create', [UserController::class, 'create'])->middleware('permission:users.create')->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->middleware('permission:users.create')->name('users.store');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])->middleware('permission:users.update')->name('users.edit');
        Route::put('/users', [UserController::class, 'update'])->middleware('permission:users.update')->name('users.update');
        Route::delete('/users', [UserController::class, 'destroy'])->middleware('permission:users.delete')->name('users.destroy');
        
        Route::get('/jurusan', [JurusanController::class, 'index'])->middleware('permission:jurusan.view')->name('jurusan');
        Route::get('/jurusan/create', [JurusanController::class, 'create'])->middleware('permission:jurusan.create')->name('jurusan.create');
        Route::post('/jurusan', [JurusanController::class, 'store'])->middleware('permission:jurusan.create')->name('jurusan.store');
        Route::get('/jurusan/{id}/edit', [JurusanController::class, 'edit'])->middleware('permission:jurusan.update')->name('jurusan.edit');
        Route::put('/jurusan/{id}', [JurusanController::class, 'update'])->middleware('permission:jurusan.update')->name('jurusan.update');
        Route::delete('/jurusan/{id}', [JurusanController::class, 'destroy'])->middleware('permission:jurusan.delete')->name('jurusan.destroy');
        
        Route::get('/kelas', [KelasController::class, 'index'])->middleware('permission:kelas.view')->name('kelas');
        Route::get('/kelas/create', [KelasController::class, 'create'])->middleware('permission:kelas.create')->name('kelas.create');
        Route::post('/kelas', [KelasController::class, 'store'])->middleware('permission:kelas.create')->name('kelas.store');
        Route::get('/kelas/{id}/edit', [KelasController::class, 'edit'])->middleware('permission:kelas.update')->name('kelas.edit');
        Route::put('/kelas/{id}', [KelasController::class, 'update'])->middleware('permission:kelas.update')->name('kelas.update');
        Route::delete('/kelas/{id}', [KelasController::class, 'destroy'])->middleware('permission:kelas.delete')->name('kelas.destroy');
        
        Route::get('/siswa', [SiswaController::class, 'index'])->middleware('permission:siswa.view')->name('siswa.index');
        Route::get('/siswa/create', [SiswaController::class, 'create'])->middleware('permission:siswa.create')->name('siswa.create');
        Route::post('/siswa', [SiswaController::class, 'store'])->middleware('permission:siswa.create')->name('siswa.store');
        Route::get('/siswa/{id}/edit', [SiswaController::class, 'edit'])->middleware('permission:siswa.update')->name('siswa.edit');
        Route::put('/siswa/{id}', [SiswaController::class, 'update'])->middleware('permission:siswa.update')->name('siswa.update');
        Route::delete('/siswa/{id}', [SiswaController::class, 'destroy'])->middleware('permission:siswa.delete')->name('siswa.destroy');
        
        Route::get('/tahunajaran', [TahunAjaranController::class, 'index'])->middleware('permission:tahunAjaran.view')->name('tahunAjaran');
        Route::get('/tahun-ajaran/create', [TahunAjaranController::class, 'create'])->middleware('permission:tahunAjaran.create')->name('tahun-ajaran.create');
        Route::post('/tahun-ajaran', [TahunAjaranController::class, 'store'])->middleware('permission:tahunAjaran.create')->name('tahun-ajaran.store');
        Route::get('/tahun-ajaran/{id}/edit', [TahunAjaranController::class, 'edit'])->middleware('permission:tahunAjaran.update')->name('tahun-ajaran.edit');
        Route::put('/tahun-ajaran/{id}', [TahunAjaranController::class, 'update'])->middleware('permission:tahunAjaran.update')->name('tahun-ajaran.update');
        Route::delete('/tahun-ajaran/{id}', [TahunAjaranController::class, 'destroy'])->middleware('permission:tahunAjaran.delete')->name('tahun-ajaran.destroy');
        
        Route::get('/penilaian', [TemplatePenilaianController::class, 'index'])->middleware('permission:penilaian.view')->name('penilaian');
        Route::get('/nilai', [PenilaianController::class, 'index'])->middleware('permission:nilai.view')->name('nilai.index');
        
        Route::get('/sertifikat', [CertificateTemplateController::class, 'index'])->middleware('permission:sertifikat.view')->name('sertifikat.index');
    });

// Settings (auth)
Route::middleware(['auth'])->prefix('settings')->name('settings.')->group(function () {
    Route::redirect('/', '/settings/profile');

    Route::get('/profile', Profile::class)->name('profile');
    Route::get('/password', Password::class)->name('password');
    Route::get('/appearance', Appearance::class)->name('appearance');

    Route::get('/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor');
});

require __DIR__.'/auth.php';
