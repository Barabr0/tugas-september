<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KategoriController as kategori;
use App\Http\Controllers\Api\BarangController as Barang;
use App\Http\Controllers\Api\PeminjamanController;
use App\Http\Controllers\Api\PeminjamanUangController;
use App\Http\Controllers\Api\UserController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'profile']);

    Route::get('/users', [UserController::class, 'index']);

       // Kategori (UBAH MENJADI /kategoris)
    Route::get('/kategoris', [kategori::class, 'index']);
    Route::post('/kategoris', [kategori::class, 'store']);
    Route::put('/kategoris/{id}', [kategori::class, 'update']);
    Route::delete('/kategoris/{id}', [kategori::class, 'destroy']);

    // Barang (UBAH MENJADI /barangs)
    Route::get('/barangs', [Barang::class, 'index']);
    Route::post('/barangs', [Barang::class, 'store']);
    Route::put('/barangs/{id}', [Barang::class, 'update']);
    Route::delete('/barangs/{id}', [Barang::class, 'destroy']);

    // Peminjaman Barang
    Route::get('/peminjaman', [PeminjamanController::class, 'index']);
    Route::get('/peminjaman/{id}', [PeminjamanController::class, 'show']);
    Route::post('/peminjaman', [PeminjamanController::class, 'store']);
    Route::patch('/peminjaman/{id}/setujui', [PeminjamanController::class, 'setujui']);
    Route::patch('/peminjaman/{id}/tolak', [PeminjamanController::class, 'tolak']);
    Route::patch('/peminjaman/{id}/batalkan', [PeminjamanController::class, 'batalkan']);
    Route::patch('/peminjaman/{id}/aktifkan', [PeminjamanController::class, 'aktifkan']);
    Route::patch('/peminjaman/{id}/kembalikan/{barangId}', [PeminjamanController::class, 'kembalikan']);

    // Peminjaman Uang
    Route::get('/peminjaman-uang', [PeminjamanUangController::class, 'index']);
    Route::post('/peminjaman-uang', [PeminjamanUangController::class, 'store']);
    Route::patch('/peminjaman-uang/{id}/setujui', [PeminjamanUangController::class, 'setujui']);
    Route::patch('/peminjaman-uang/{id}/tolak', [PeminjamanUangController::class, 'tolak']);
    Route::patch('/peminjaman-uang/{id}/batalkan', [PeminjamanUangController::class, 'batalkan']);
    Route::patch('/peminjaman-uang/{id}/aktifkan', [PeminjamanUangController::class, 'aktifkan']);
    Route::patch('/peminjaman-uang/{id}/lunas', [PeminjamanUangController::class, 'lunas']);
});