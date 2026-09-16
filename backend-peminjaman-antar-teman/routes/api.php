<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BarangController as Barang; 
use App\Http\Controllers\Api\KategoriController as kategori; 
use App\Http\Controllers\Api\PeminjamanController;
use App\Http\Controllers\Api\PeminjamanUangController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'profile']);

    Route::get('/users', [UserController::class, 'index']); // <--- TAMBAHAN INI
    
        //kategori
    Route::get('/kategori', [kategori::class, 'index']);
    Route::post('/kategori', [kategori::class, 'store']);
    Route::put('/kategori/{id}', [kategori::class, 'update']);     
    Route::delete('/kategori/{id}', [kategori::class, 'destroy']);
        //Barang
    Route::get('/barang/tersedia', [Barang::class, 'getBarangTersedia']);
    Route::get('/barang', [Barang::class, 'index']);
    Route::post('/barang', [Barang::class, 'store']);
    Route::put('/barang/{id}', [Barang::class, 'update']);     
    Route::delete('/barang/{id}', [Barang::class, 'destroy']);
    // Semua user (peminjam & pemilik)
    Route::get('/peminjaman', [PeminjamanController::class, 'index']);
    Route::post('/peminjaman', [PeminjamanController::class, 'store']);

    Route::patch('/peminjaman/{id}/setujui', [PeminjamanController::class, 'setujui']);
    Route::patch('/peminjaman/{id}/tolak', [PeminjamanController::class, 'tolak']);
    Route::patch('/peminjaman/{id}/batalkan', [PeminjamanController::class, 'batalkan']);
    Route::patch('/peminjaman/{id}/aktifkan', [PeminjamanController::class, 'aktifkan']);

    // TAMBAH {barangId} di belakang - karena kembalikan sekarang per-barang, bukan per-transaksi
    Route::patch('/peminjaman/{id}/kembalikan/{barangId}', [PeminjamanController::class, 'kembalikan']);
    Route::get('/peminjaman/{id}', [PeminjamanController::class, 'show']);

    Route::get('/peminjaman-uang', [PeminjamanUangController::class, 'index']);
    Route::post('/peminjaman-uang', [PeminjamanUangController::class, 'store']);
    Route::patch('/peminjaman-uang/{id}/setujui', [PeminjamanUangController::class, 'setujui']);
    Route::patch('/peminjaman-uang/{id}/tolak', [PeminjamanUangController::class, 'tolak']);
    Route::patch('/peminjaman-uang/{id}/batalkan', [PeminjamanUangController::class, 'batalkan']);
    Route::patch('/peminjaman-uang/{id}/aktifkan', [PeminjamanUangController::class, 'aktifkan']);
    Route::patch('/peminjaman-uang/{id}/lunas', [PeminjamanUangController::class, 'lunas']);
});