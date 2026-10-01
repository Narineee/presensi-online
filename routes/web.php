<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DivisiController;
use App\Http\Controllers\Admin\HariLiburController;
use App\Http\Controllers\Admin\KriteriaPenilaianController;
use App\Http\Controllers\Admin\MagangController;
use App\Http\Controllers\Admin\MonitoringAktivitasController;
use App\Http\Controllers\Admin\MonitoringPengajuanIzinController;
use App\Http\Controllers\Admin\MonitoringPenilaianController;
use App\Http\Controllers\Admin\MonitoringPresensiController;
use App\Http\Controllers\Admin\PembimbingController;
use App\Http\Controllers\Admin\PenempatanMagangController;
use App\Http\Controllers\Admin\PengaturanController;
use App\Http\Controllers\AktivitasController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Magang\PenilaianSayaController;
use App\Http\Controllers\Pembimbing\AktivitasValidasiController;
use App\Http\Controllers\Pembimbing\DashboardController as PembimbingDashboardController;
use App\Http\Controllers\Pembimbing\MonitoringPresensiController as PembimbingMonitoringPresensiController;
use App\Http\Controllers\Pembimbing\PekerjaanController as PembimbingPekerjaanController;
use App\Http\Controllers\Pembimbing\PengajuanIzinValidasiController;
use App\Http\Controllers\Pembimbing\PenilaianMagangController;
use App\Http\Controllers\PengajuanIzinController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WajahController;
use Illuminate\Support\Facades\Route;

Route::get('/rekam-wajah', [WajahController::class, 'create'])->name('wajah.create');
Route::post('/rekam-wajah', [WajahController::class, 'store'])->name('wajah.store');

Route::get('/', function () {
    return redirect()->route('login');
});

// ==============================
// AUTH
// ==============================

Route::get('/login', [
    AuthController::class,
    'showLogin',
])->name('login');

Route::post('/login', [
    AuthController::class,
    'login',
])->name('login.process');

Route::post('/logout', [
    AuthController::class,
    'logout',
])->name('logout');

// ==============================
// ADMIN
// ==============================

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // CRUD Divisi
        Route::resource('divisi', DivisiController::class);

        // CRUD Pembimbing
        Route::resource('pembimbing', PembimbingController::class);

        Route::get('magang/{magang}/foto-wajah', [MagangController::class, 'fotoWajah'])->name('magang.wajah.foto');
        Route::post('magang/{magang}/reset-wajah', [MagangController::class, 'resetWajah'])->name('magang.wajah.reset');

        // Riwayat Penempatan Divisi Peserta Magang
        Route::post('magang/{magang}/penempatan', [PenempatanMagangController::class, 'store'])->name('magang.penempatan.store');
        Route::put('magang/{magang}/penempatan/{penempatan}', [PenempatanMagangController::class, 'update'])->name('magang.penempatan.update');
        Route::delete('magang/{magang}/penempatan/{penempatan}', [PenempatanMagangController::class, 'destroy'])->name('magang.penempatan.destroy');

        // CRUD Magang & Plotting
        Route::resource('magang', MagangController::class);

        // CRUD Master Kriteria Penilaian
        Route::resource('kriteria', KriteriaPenilaianController::class);

        // CRUD & Sinkronisasi Master Hari Libur
        Route::post('hari-libur/sync', [HariLiburController::class, 'sync'])->name('hari-libur.sync');
        Route::resource('hari-libur', HariLiburController::class);

        // Monitoring Presensi Magang
        Route::get('presensi/cetak', [MonitoringPresensiController::class, 'cetak'])->name('presensi.cetak');
        Route::get('presensi', [MonitoringPresensiController::class, 'index'])->name('presensi.index');
        
        // Monitoring Aktivitas Harian Magang
        Route::get('aktivitas/cetak', [MonitoringAktivitasController::class, 'cetak'])->name('aktivitas.cetak');
        Route::get('aktivitas', [MonitoringAktivitasController::class, 'index'])->name('aktivitas.index');

        // Monitoring Pengajuan Izin & Sakit
        Route::get('izin', [MonitoringPengajuanIzinController::class, 'index'])->name('izin.index');

        // Monitoring & Rekap Penilaian Akhir Magang
        Route::get('penilaian/cetak', [MonitoringPenilaianController::class, 'cetak'])->name('penilaian.cetak');
        Route::get('penilaian', [MonitoringPenilaianController::class, 'index'])->name('penilaian.index');
        Route::get('penilaian/{id}', [MonitoringPenilaianController::class, 'show'])->name('penilaian.show');
        Route::delete('penilaian/{id}', [MonitoringPenilaianController::class, 'destroy'])->name('penilaian.destroy');

        // Pengaturan Sistem Aplikasi & Pimpinan Dinas
        Route::get('pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
        Route::put('pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');

    });

// ==============================
// PRESENSI, AKTIVITAS & IZIN (MAGANG)
// ==============================

Route::middleware(['auth', 'role:magang'])->group(function () {
    // Presensi
    Route::get('/presensi/cetak', [PresensiController::class, 'cetak'])->name('presensi.cetak');
    Route::get('/presensi', [PresensiController::class, 'index'])->name('presensi.index');
    Route::post('/presensi/masuk', [PresensiController::class, 'storeMasuk'])->name('presensi.masuk');
    Route::post('/presensi/keluar', [PresensiController::class, 'storeKeluar'])->name('presensi.keluar');
    Route::post('/presensi/verifikasi-wajah', [PresensiController::class, 'verifikasiWajah'])->name('presensi.verifikasi-wajah');

    // CRUD Aktivitas Harian
    Route::get('/aktivitas/cetak', [AktivitasController::class, 'cetak'])->name('aktivitas.cetak');
    Route::resource('aktivitas', AktivitasController::class);

    // CRUD Pengajuan Izin & Sakit
    Route::resource('izin', PengajuanIzinController::class);

    // Profil Peserta Magang
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profil.update');

    // Rekapitulasi & Cetak Dokumen Hub
    Route::get('/rekap', [PresensiController::class, 'rekapHub'])->name('magang.rekap');
});

// ==============================
// PEMBIMBING
// ==============================

Route::middleware(['auth', 'role:pembimbing'])
    ->prefix('pembimbing')
    ->name('pembimbing.')
    ->group(function () {

        Route::get('/dashboard', [PembimbingDashboardController::class, 'index'])->name('dashboard');

        // Monitoring & Riwayat Presensi Peserta Binaan
        Route::get('/presensi/cetak', [PembimbingMonitoringPresensiController::class, 'cetak'])->name('presensi.cetak');
        Route::get('/presensi', [PembimbingMonitoringPresensiController::class, 'index'])->name('presensi.index');

        // Manajemen Pekerjaan & Proyek Binaan
        Route::resource('pekerjaan', PembimbingPekerjaanController::class);

        // Validasi Aktivitas Binaan
        Route::get('/aktivitas', [AktivitasValidasiController::class, 'index'])->name('aktivitas.index');
        Route::post('/aktivitas/{id}/validasi', [AktivitasValidasiController::class, 'validasi'])->name('aktivitas.validasi');

        // Verifikasi Izin & Sakit Binaan
        Route::get('/izin', [PengajuanIzinValidasiController::class, 'index'])->name('izin.index');
        Route::post('/izin/{id}/validasi', [PengajuanIzinValidasiController::class, 'validasi'])->name('izin.validasi');

        // Penilaian Akhir Magang
        Route::resource('penilaian', PenilaianMagangController::class);

    });

// ==============================
// MAGANG
// ==============================

Route::middleware(['auth', 'role:magang'])
    ->prefix('magang')
    ->name('magang.')
    ->group(function () {

        Route::get('/dashboard', function () {
            return redirect()->route('presensi.index');
        })->name('dashboard');

        // Lembar Penilaian Akhir
        Route::get('/penilaian', [PenilaianSayaController::class, 'index'])->name('penilaian.index');

    });
