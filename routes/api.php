<?php

use App\Http\Controllers\JurusanController;
use App\Http\Controllers\KelompokBimbinganController;
use Illuminate\Support\Facades\Route;

Route::prefix('kelompok-bimbingan')->group(function () {
    Route::get('/', [KelompokBimbinganController::class, 'index']);
    Route::post('/', [KelompokBimbinganController::class, 'store']);
    Route::get('/{id}', [KelompokBimbinganController::class, 'show']);
    Route::put('/{id}', [KelompokBimbinganController::class, 'update']);
    Route::delete('/{id}', [KelompokBimbinganController::class, 'destroy']);
});

Route::prefix('jurusan')->group(function () {
    Route::get('/all', [JurusanController::class, 'index']);
    Route::post('/create', [JurusanController::class, 'store']);
    Route::put('/update', [JurusanController::class, 'update']);
    Route::delete('/delete', [JurusanController::class, 'destroy']);
});
