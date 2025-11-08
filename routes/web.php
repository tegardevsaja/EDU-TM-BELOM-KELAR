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


// Master Admin Routes
Route::middleware(['auth', 'verified', 'role:master_admin'])
    ->prefix('master')
    ->name('master.')
    ->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    // route pengguna 
    Route::get('/pengguna', [PenggunaController::class, 'index'])->name('pengguna');
    Route::get('/pengguna/create', [PenggunaController::class, 'create'])->name('pengguna.create');
    Route::post('/pengguna', [PenggunaController::class, 'store'])->name('pengguna.store');
    Route::get('/pengguna/{id}/edit', [PenggunaController::class, 'edit'])->name('pengguna.edit');
    Route::put('/pengguna/{id}', [PenggunaController::class, 'update'])->name('pengguna.update');
    Route::delete('/pengguna/{id}', [PenggunaController::class, 'destroy'])->name('pengguna.destroy');
    Route::post('/pengguna/import', [PenggunaController::class, 'import'])->name('pengguna.import');
    Route::get('/pengguna/export', [PenggunaController::class, 'export'])->name('pengguna.export');
    Route::get('/pengguna/template-download', [PenggunaController::class, 'downloadTemplate'])->name('pengguna.template');

    // route user 
    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users', [UserController::class, 'destroy'])->name('users.destroy');

    // route jurusan 
    Route::get('/jurusan', [JurusanController::class, 'index'])->name('jurusan');
    Route::get('/jurusan/create', [JurusanController::class, 'create'])->name('jurusan.create');
    Route::post('/jurusan', [JurusanController::class, 'store'])->name('jurusan.store');
    Route::get('/jurusan/{id}/edit', [JurusanController::class, 'edit'])->name('jurusan.edit');
    Route::put('/jurusan/{id}', [JurusanController::class, 'update'])->name('jurusan.update');
    Route::delete('/jurusan/{id}', [JurusanController::class, 'destroy'])->name('jurusan.destroy');

    // route kelas 
    Route::get('/kelas', [KelasController::class, 'index'])->name('kelas');
    Route::get('/kelas/create', [KelasController::class, 'create'])->name('kelas.create');
    Route::post('/kelas', [KelasController::class, 'store'])->name('kelas.store');
    Route::post('/kelas/{id}/edit', [KelasController::class, 'edit'])->name('kelas.edit');
    Route::put('/kelas/{id}', [KelasController::class, 'update'])->name('kelas.update');
    Route::delete('/kelas/{id}', [KelasController::class, 'destroy'])->name('kelas.destroy');

    // route siswa 
    Route::get('/siswa', [SiswaController::class, 'index'])->name('siswa.index');
    Route::get('/siswa/create', [SiswaController::class, 'create'])->name('siswa.create');
    Route::post('/siswa', [SiswaController::class, 'store'])->name('siswa.store');
    Route::get('/siswa/{id}/edit', [SiswaController::class, 'edit'])->name('siswa.edit');
    Route::put('/siswa/{id}', [SiswaController::class, 'update'])->name('siswa.update'); 
    Route::delete('/siswa/{id}', [SiswaController::class, 'destroy'])->name('siswa.destroy');

    Route::get('/siswa/template-download', [SiswaController::class, 'downloadTemplate'])->name('siswa.template');
    Route::get('siswa/import', [SiswaImportController::class, 'showImportForm'])->name('master.siswa.import');
    Route::post('/siswa/import/preview', [SiswaImportController::class, 'previewImport'])->name('siswa.import.preview');
    Route::post('/siswa/import/store', [SiswaImportController::class, 'storeImport'])->name('siswa.import.store');
    
    // route tahun ajaran 
    Route::get('/tahunajaran', [TahunAjaranController::class, 'index'])->name('tahunAjaran');
    Route::get('/tahun-ajaran/create', [TahunAjaranController::class, 'create'])->name('tahun-ajaran.create');
    Route::post('/tahun-ajaran', [TahunAjaranController::class, 'store'])->name('tahun-ajaran.store');
    Route::get('/tahun-ajaran/{id}/edit', [TahunAjaranController::class, 'edit'])->name('tahun-ajaran.edit');
    Route::put('/tahun-ajaran/{id}', [TahunAjaranController::class, 'update'])->name('tahun-ajaran.update');
    Route::delete('/tahun-ajaran/{id}', [TahunAjaranController::class, 'destroy'])->name('tahun-ajaran.destroy');

    // route penilaian 
    Route::get('/penilaian', [TemplatePenilaianController::class, 'index'])->name('penilaian');
    Route::get('/penilaian/create', [TemplatePenilaianController::class, 'create'])->name('penilaian.create');
    Route::post('/penilaian', [TemplatePenilaianController::class, 'store'])->name('penilaian.store');
    Route::get('/penilaian/{id}/edit', [TemplatePenilaianController::class, 'edit'])->name('penilaian.edit');
    Route::put('/penilaian/{id}', [TemplatePenilaianController::class, 'update'])->name('penilaian.update'); 
    Route::delete('/penilaian/{id}', [TemplatePenilaianController::class, 'destroy'])->name('penilaian.destroy');

    Route::get('/nilai', [PenilaianController::class, 'index'])->name('nilai.index');

    Route::get('/sertifikat/template', [CertificateTemplateController::class, 'index'])->name('sertifikat.index');
    Route::post('/sertifikat/template/store', [CertificateTemplateController::class, 'store'])->name('sertifikat.store');
    Route::get('/sertifikat/template/{id}/edit', [CertificateTemplateController::class, 'edit'])->name('sertifikat.edit');
    Route::put('/sertifikat/template/{id}', [CertificateTemplateController::class, 'update'])->name('sertifikat.update');
    Route::delete('/sertifikat/template/{id}', [CertificateTemplateController::class, 'destroy'])->name('sertifikat.destroy');
    Route::get('/sertifikat/template/create', [CertificateTemplateController::class, 'create'])->name('sertifikat.create');

// Certificate Generation Flow
Route::prefix('sertifikat')->name('sertifikat.')->group(function () {
    // STEP 1 – Pilih Template
    Route::get('/select-template', [CertificateGenerationController::class, 'stepSelectTemplate'])
        ->name('select_template');

    // STEP 2 – Pilih Template Nilai (opsional)
    Route::get('/select-grade/{template_id}', [CertificateGenerationController::class, 'stepSelectGrade'])
        ->name('select_grade');

    // STEP 3 – Pilih Kelas
    Route::get('/generate/select-class/{template_id}', [CertificateGenerationController::class, 'stepSelectClass'])
        ->name('generate.select-class');

    // STEP 3.5 – Customisasi
    Route::get('/generate/customize/{template_id}', [CertificateGenerationController::class, 'customize'])
        ->name('generate.customize');
    
    // 🔥 ROUTE PENTING INI YANG HILANG - SAVE CUSTOMIZATION
    Route::post('/generate/customize/{template_id}', [CertificateGenerationController::class, 'saveCustomization'])
        ->name('generate.save-customization');

    // STEP 4 – Preview
    Route::get('/generate/preview/{template_id}', [CertificateGenerationController::class, 'preview'])
        ->name('generate.preview');

    // STEP 5 – Generate/Download
    Route::post('/generate/process', [CertificateGenerationController::class, 'generate'])
        ->name('generate.process');

    // History
    Route::get('/generate/history', [CertificateGenerationController::class, 'history'])
        ->name('generate.history');
});
});

// Admin Routes
Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::view('/dashboard', 'admin.dashboard')->name('dashboard');
    });

// Guru Routes
Route::middleware(['auth', 'verified', 'role:guru'])
    ->prefix('guru')
    ->name('guru.')
    ->group(function () {
        Route::view('/dashboard', 'guru.dashboard')->name('dashboard');
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
