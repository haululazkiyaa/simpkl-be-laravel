<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\JurusanController;
use App\Http\Controllers\KelompokBimbinganController;
use App\Http\Controllers\JurnalHarianController;
use App\Http\Controllers\NilaiAkhirController;
use App\Http\Controllers\PerusahaanController;
use App\Http\Controllers\TahunAjaranController;
use App\Http\Middleware\CheckToken;
use App\Http\Middleware\CheckUserRole;
use Illuminate\Support\Facades\Route;

// ROLE PENGGUNA
// - "ADMINSEKOLAH"
// - "PEMBIMBING"
// - "SISWA"
// - "INSTRUKTUR"
// - "PERUSAHAAN"

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/refresh-token', [AuthController::class, 'accessToken']);
    Route::get('/profile', [AuthController::class, 'profile'])->middleware([CheckToken::class]);
    Route::delete('/logout', [AuthController::class, 'logout']);
});

Route::prefix('kelompok-bimbingan')->middleware([CheckToken::class])->group(function () {
    Route::get('/all', [KelompokBimbinganController::class, 'getAll'])->middleware([CheckUserRole::class . ':ADMINSEKOLAH']);
    Route::post('/create', [KelompokBimbinganController::class, 'create'])->middleware([CheckUserRole::class . ':ADMINSEKOLAH']);
    Route::put('/update', [KelompokBimbinganController::class, 'update'])->middleware([CheckUserRole::class . ':ADMINSEKOLAH']);
    Route::delete('/delete', [KelompokBimbinganController::class, 'delete'])->middleware([CheckUserRole::class . ':ADMINSEKOLAH']);
});

Route::prefix('jurusan')->middleware([CheckToken::class])->group(function () {
    Route::get('/all', [JurusanController::class, 'index'])->middleware([CheckUserRole::class . ':ADMINSEKOLAH']);
    Route::post('/create', [JurusanController::class, 'store'])->middleware([CheckUserRole::class . ':ADMINSEKOLAH']);
    Route::put('/update', [JurusanController::class, 'update'])->middleware([CheckUserRole::class . ':ADMINSEKOLAH']);
    Route::delete('/delete', [JurusanController::class, 'destroy'])->middleware([CheckUserRole::class . ':ADMINSEKOLAH']);
});

Route::prefix('tahun-ajaran')->middleware([CheckToken::class])->group(function () {
    Route::get('/all', [TahunAjaranController::class, 'index'])->middleware([CheckUserRole::class . ':ADMINSEKOLAH']);
    Route::post('/create', [TahunAjaranController::class, 'store'])->middleware([CheckUserRole::class . ':ADMINSEKOLAH']);
    Route::put('/status', [TahunAjaranController::class, 'updateStatus'])->middleware([CheckUserRole::class . ':ADMINSEKOLAH']);
});

Route::prefix('perusahaan')->middleware([CheckToken::class])->group(function () {
    Route::get('/all', [PerusahaanController::class, 'index'])->middleware([CheckUserRole::class . ':ADMINSEKOLAH']);
    Route::post('/create', [PerusahaanController::class, 'store'])->middleware([CheckUserRole::class . ':ADMINSEKOLAH']);
    Route::put('/update', [PerusahaanController::class, 'update'])->middleware([CheckUserRole::class . ':ADMINSEKOLAH']);
});

Route::prefix('jurnal-harian')->middleware([CheckToken::class])->group(function () {
    Route::get('/bimbingan/get', [JurnalHarianController::class, 'getForPembimbing'])->middleware([CheckUserRole::class . ':PEMBIMBING']);
    Route::post('/bimbingan/postCatatan', [JurnalHarianController::class, 'addCatatanPembimbing'])->middleware([CheckUserRole::class . ':PEMBIMBING']);
});

Route::prefix('nilai-akhir')->middleware([CheckToken::class])->group(function () {
    Route::get('', [NilaiAkhirController::class, 'getPembimbing'])->middleware([CheckUserRole::class . ':PEMBIMBING']);
    Route::post('', [NilaiAkhirController::class, 'gradePembimbing'])->middleware([CheckUserRole::class . ':PEMBIMBING']);
    Route::get('/siswa', [NilaiAkhirController::class, 'getSiswa'])->middleware([CheckUserRole::class . ':SISWA']);
});
