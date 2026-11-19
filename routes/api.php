<?php

use App\Http\Controllers\Api\MahasiswaController;
use App\Http\Controllers\Api\SuratController;
use App\Models\JenisSurat;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('jenis-surat', fn () => ['data' => JenisSurat::orderBy('nama')->get()]);

    // 5 route sekaligus: index, store, show, update, destroy
    Route::apiResource('mahasiswa', MahasiswaController::class);

    Route::apiResource('surat', SuratController::class)->except('update');
    Route::patch('surat/{surat}/status', [SuratController::class, 'ubahStatus']);
});