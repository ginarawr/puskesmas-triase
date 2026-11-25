<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Dokter\DashboardController as DokterDashboard;
use App\Http\Controllers\Dokter\ExaminationController;
use App\Http\Controllers\Perawat\DashboardController as PerawatDashboard;
use App\Http\Controllers\Perawat\PatientController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check()
    ? redirect(auth()->user()->homeUrl())
    : redirect()->route('login'));

// Login & daftar akun
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/masuk', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/daftar', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/keluar', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Alur perawat
Route::middleware(['auth', EnsureRole::class . ':perawat'])
    ->prefix('perawat')->name('perawat.')->group(function () {
        Route::get('/', PerawatDashboard::class)->name('dashboard');
        Route::get('/pasien/baru', [PatientController::class, 'create'])->name('pasien.create');
        Route::post('/pasien/evaluasi', [PatientController::class, 'evaluate'])->name('pasien.evaluate');
        Route::post('/pasien', [PatientController::class, 'store'])->name('pasien.store');
        Route::post('/pasien/{patient}/panggil', [PatientController::class, 'call'])->name('pasien.call');
        Route::post('/pasien/{patient}/hubungi-dokter', [PatientController::class, 'escalate'])->name('pasien.escalate');
    });

// Alur dokter
Route::middleware(['auth', EnsureRole::class . ':dokter'])
    ->prefix('dokter')->name('dokter.')->group(function () {
        Route::get('/', DokterDashboard::class)->name('dashboard');
        Route::post('/pasien/{patient}/mulai', [ExaminationController::class, 'start'])->name('pasien.start');
        Route::get('/pasien/{patient}', [ExaminationController::class, 'show'])->name('pasien.show');
        Route::put('/pasien/{patient}', [ExaminationController::class, 'update'])->name('pasien.update');
    });
