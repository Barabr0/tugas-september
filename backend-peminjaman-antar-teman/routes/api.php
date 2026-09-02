<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PeminjamanController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Semua user (peminjam & pemilik)
    Route::post('/peminjaman', [PeminjamanController::class, 'store']);
    Route::post('/peminjaman/{peminjaman}/setujui', [PeminjamanController::class, 'setujui']);

    // Khusus admin
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/users', [\App\Http\Controllers\Admin\UserController::class, 'index']);
        Route::delete('/admin/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy']);
    });
});