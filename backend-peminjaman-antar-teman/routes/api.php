<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KategoriController as kategori; 
use App\Http\Controllers\Api\BarangController as Barang; 
use App\Http\Controllers\Api\PeminjamanController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
        //kategori
    Route::get('/kategori', [kategori::class, 'index']);
    Route::post('/kategori', [kategori::class, 'store']);
    Route::put('/kategori/{id}', [kategori::class, 'update']);     
    Route::delete('/kategori/{id}', [kategori::class, 'destroy']);
        //Barang
    Route::get('/barang', [Barang::class, 'index']);
    Route::post('/barang', [Barang::class, 'store']);
    Route::put('/barang/{id}', [Barang::class, 'update']);     
    Route::delete('/barang/{id}', [Barang::class, 'destroy']);
    // Semua user (peminjam & pemilik)
    Route::post('/peminjaman', [PeminjamanController::class, 'store']);
    Route::post('/peminjaman/{peminjaman}/setujui', [PeminjamanController::class, 'setujui']);

    // Khusus admin
    // Route::middleware('role:admin')->group(function () {
    //     Route::get('/admin/users', [\App\Http\Controllers\Admin\UserController::class, 'index']);
    //     Route::delete('/admin/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy']);
    // });
});