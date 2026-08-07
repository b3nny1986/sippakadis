<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\KendaraanController as AdminKendaraanController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\OpdController;
use App\Http\Controllers\Admin\PenetapanController;
use App\Http\Controllers\Admin\PerubahanStatusController as AdminPerubahanStatusController;
use App\Http\Controllers\Admin\SinkronisasiController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CronController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\Opd\PengajuanController as OpdPengajuanController;
use App\Http\Controllers\Opd\PerubahanStatusController as OpdPerubahanStatusController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Autentikasi
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'authenticate'])->name('login.attempt');
});

Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Cron internal (dipanggil GitHub Actions / scheduler eksternal)
|--------------------------------------------------------------------------
*/
Route::post('cron/daily', [CronController::class, 'daily'])->name('cron.daily');

/*
|--------------------------------------------------------------------------
| Halaman publik: Dashboard bisa dilihat tanpa login (menu login di header)
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect()->route('dashboard'))->name('home');
Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

// Daftar kendaraan publik (rekap dashboard tanpa login). Detail butuh login.
Route::get('kendaraan', [AdminKendaraanController::class, 'index'])->name('kendaraan.index');

/*
|--------------------------------------------------------------------------
| Area terautentikasi
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'user.active'])->group(function () {
    // Notifikasi
    Route::get('notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::post('notifikasi/{notifikasi}/read', [NotifikasiController::class, 'markRead'])->name('notifikasi.read');

    // Detail kendaraan butuh login (OPD melihat detail, ajukan status & penetapan)
    Route::get('kendaraan/{kendaraan}', [AdminKendaraanController::class, 'show'])->name('kendaraan.show');

    /*
    |--------------------------------------------------------------------------
    | Admin
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::resource('opd', OpdController::class)->except(['show']);

        // Kendaraan CRUD + sinkronisasi on-demand
        Route::get('kendaraan/{kendaraan}/edit', [AdminKendaraanController::class, 'edit'])->name('kendaraan.edit');
        Route::put('kendaraan/{kendaraan}', [AdminKendaraanController::class, 'update'])->name('kendaraan.update');
        Route::post('kendaraan/{kendaraan}/sinkronisasi', [AdminKendaraanController::class, 'sinkronisasi'])->name('kendaraan.sinkronisasi');

        // Sinkronisasi massal Simpator
        Route::get('sinkronisasi', [SinkronisasiController::class, 'index'])->name('sinkronisasi.index');
        Route::post('sinkronisasi/jalankan', [SinkronisasiController::class, 'jalankan'])->name('sinkronisasi.jalankan');

        // Pengajuan penetapan (verifikasi admin)
        Route::get('penetapan', [PenetapanController::class, 'index'])->name('penetapan.index');
        Route::get('penetapan/{pengajuan}', [PenetapanController::class, 'show'])->name('penetapan.show');
        Route::post('penetapan/{pengajuan}/proses', [PenetapanController::class, 'proses'])->name('penetapan.proses');
        Route::post('penetapan/{pengajuan}/approve', [PenetapanController::class, 'approve'])->name('penetapan.approve');
        Route::post('penetapan/{pengajuan}/reject', [PenetapanController::class, 'reject'])->name('penetapan.reject');
        Route::get('penetapan/{pengajuan}/cetak', [PenetapanController::class, 'cetak'])->name('penetapan.cetak');

        // Perubahan status kendaraan (approval admin)
        Route::get('perubahan-status', [AdminPerubahanStatusController::class, 'index'])->name('perubahan-status.index');
        Route::get('perubahan-status/{perubahan}', [AdminPerubahanStatusController::class, 'show'])->name('perubahan-status.show');
        Route::post('perubahan-status/{perubahan}/approve', [AdminPerubahanStatusController::class, 'approve'])->name('perubahan-status.approve');
        Route::post('perubahan-status/{perubahan}/reject', [AdminPerubahanStatusController::class, 'reject'])->name('perubahan-status.reject');

        // Laporan & ekspor
        Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('laporan/{slug}/export/{format}', [LaporanController::class, 'export'])->name('laporan.export');

        // Audit log
        Route::get('audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');

        // Log gabungan (audit + aktivitas pengguna)
        Route::get('log', [LogController::class, 'index'])->name('log.index');
    });

    /*
    |--------------------------------------------------------------------------
    | OPD
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:opd')->prefix('opd')->name('opd.')->group(function () {
        Route::get('pengajuan', [OpdPengajuanController::class, 'index'])->name('pengajuan.index');
        Route::get('pengajuan/create', [OpdPengajuanController::class, 'create'])->name('pengajuan.create');
        Route::post('pengajuan', [OpdPengajuanController::class, 'store'])->name('pengajuan.store');
        Route::get('pengajuan/{pengajuan}', [OpdPengajuanController::class, 'show'])->name('pengajuan.show');

        Route::get('perubahan-status/create', [OpdPerubahanStatusController::class, 'create'])->name('perubahan-status.create');
        Route::post('perubahan-status', [OpdPerubahanStatusController::class, 'store'])->name('perubahan-status.store');
    });
});
