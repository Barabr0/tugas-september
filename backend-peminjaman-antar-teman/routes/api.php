<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BankController;
use App\Http\Controllers\Api\BantuanController;
use App\Http\Controllers\Api\BarangController as Barang;
use App\Http\Controllers\Api\FollowController;
use App\Http\Controllers\Api\KategoriController as kategori;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\PeminjamanController;
use App\Http\Controllers\Api\PeminjamanUangController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'profile']);

    Route::get('/users', [UserController::class, 'index']);

    // Barang
    Route::get('/barang/tersedia', [Barang::class, 'getBarangTersedia']);
    Route::get('/barang', [Barang::class, 'index']);
    Route::post('/barang', [Barang::class, 'store']);
    Route::put('/barang/{id}', [Barang::class, 'update']);
    Route::delete('/barang/{id}', [Barang::class, 'destroy']);

    // Peminjaman barang (peminjam & pemilik)
    Route::get('/peminjaman', [PeminjamanController::class, 'index']);
    Route::post('/peminjaman', [PeminjamanController::class, 'store']);
    Route::patch('/peminjaman/{id}/setujui', [PeminjamanController::class, 'setujui']);
    Route::patch('/peminjaman/{id}/tolak', [PeminjamanController::class, 'tolak']);
    Route::patch('/peminjaman/{id}/batalkan', [PeminjamanController::class, 'batalkan']);
    Route::patch('/peminjaman/{id}/aktifkan', [PeminjamanController::class, 'aktifkan']);
    Route::patch('/peminjaman/{id}/kembalikan/{barangId}', [PeminjamanController::class, 'kembalikan']);
    Route::get('/peminjaman/{id}', [PeminjamanController::class, 'show']);

    // Peminjaman uang
    Route::get('/peminjaman-uang', [PeminjamanUangController::class, 'index']);
    Route::post('/peminjaman-uang', [PeminjamanUangController::class, 'store']);
    Route::patch('/peminjaman-uang/{id}/setujui', [PeminjamanUangController::class, 'setujui']);
    Route::patch('/peminjaman-uang/{id}/tolak', [PeminjamanUangController::class, 'tolak']);
    Route::patch('/peminjaman-uang/{id}/batalkan', [PeminjamanUangController::class, 'batalkan']);
    Route::patch('/peminjaman-uang/{id}/aktifkan', [PeminjamanUangController::class, 'aktifkan']);
    Route::patch('/peminjaman-uang/{id}/lunas', [PeminjamanUangController::class, 'lunas']);

    // Bantuan (user)
    Route::get('/bantuan', [BantuanController::class, 'index']);
    Route::post('/bantuan', [BantuanController::class, 'store']);
    Route::patch('/bantuan/{id}/respond', [BantuanController::class, 'respond']);
    Route::delete('/bantuan/{id}', [BantuanController::class, 'destroy']);

    // Fitur Teman (Follow)
    Route::get('/users/browse', [FollowController::class, 'index']);
    Route::get('/users/{id}/detail', [FollowController::class, 'show']);
    Route::get('/friends', [FollowController::class, 'myFriends']);
    Route::post('/users/{id}/follow', [FollowController::class, 'follow']);
    Route::delete('/users/{id}/unfollow', [FollowController::class, 'unfollow']);

    // Bank User
    Route::get('/banks', [BankController::class, 'index']);
    Route::post('/banks', [BankController::class, 'store']);
    Route::post('/banks/{id}/topup', [BankController::class, 'topUp']);
    Route::delete('/banks/{id}', [BankController::class, 'destroy']);

    // Laporan antar user (user -> user)
    Route::get('/laporan', [LaporanController::class, 'index']);
    Route::get('/laporan/my-reports', [LaporanController::class, 'index']);
    Route::post('/laporan', [LaporanController::class, 'store']);
    Route::patch('/laporan/{id}/respond', [LaporanController::class, 'respond']);
    Route::delete('/laporan/{id}', [LaporanController::class, 'destroy']);

    Route::get('/kategori', [kategori::class, 'index']);

    // ==========================
    // ADMIN
    // ==========================
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // Kelola user
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);

        Route::post('/kategori', [kategori::class, 'store']);
        Route::put('/kategori/{id}', [kategori::class, 'update']);
        Route::delete('/kategori/{id}', [kategori::class, 'destroy']);

        Route::delete('/barangs/{id}/force-delete', [Barang::class, 'forceDelete']);
        Route::patch('/peminjaman/{id}/batalkan', [PeminjamanController::class, 'adminBatalkan']);

        Route::get('/laporan', [LaporanController::class, 'indexAdmin']);

        // Bantuan (admin)
        Route::get('/bantuan', [BantuanController::class, 'indexAdmin']);
        Route::patch('/bantuan/{id}/respond', [BantuanController::class, 'adminRespond']);
    });
});