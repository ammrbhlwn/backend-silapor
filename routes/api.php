<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\PengelolaController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


// route auth
Route::post('/user/register', [AuthController::class, 'register_user']);
Route::post('/user/login', [AuthController::class, 'login_user']);
Route::post('/pengelola/register', [AuthController::class, 'register_pengelola']);
Route::post('/pengelola/login', [AuthController::class, 'login_pengelola']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

// route guest
Route::get('/list/lapangan/badminton', [GuestController::class, 'daftar_lapangan_badminton']);
Route::get('/list/lapangan/futsal', [GuestController::class, 'daftar_lapangan_futsal']);
Route::get('/lapangan/{id}', [GuestController::class, 'lihat_detail_lapangan']);
Route::get('/search', [GuestController::class, 'search_lapangan']);

// route pengelola
Route::middleware(['auth:sanctum', 'pengelola'])->group(function () {
    Route::get('/pengelola/booking/list', [PengelolaController::class, 'lihat_daftar_transaksi']);
    Route::get('/pengelola/booking/{id}', [PengelolaController::class, 'lihat_detail_transaksi']);
    Route::post('/pengelola/lapangan', [PengelolaController::class, 'tambah_lapangan']);
    Route::put('/pengelola/booking/{id}', [PengelolaController::class, 'edit_status_transaksi']);
    Route::put('/pengelola/lapangan/{id}', [PengelolaController::class, 'edit_data_lapangan']);
    Route::delete('/pengelola/lapangan/{id}', [PengelolaController::class, 'hapus_lapangan']);
});

// route user
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/profile', [UserController::class, 'lihat_data_profile']);
    Route::get('/user/favorite', [UserController::class, 'lihat_data_favorite']);
    Route::get('/user/booking/list', [UserController::class, 'lihat_daftar_transaksi']);
    Route::get('/user/booking/{id}', [UserController::class, 'lihat_detail_transaksi']);
    Route::post('/user/booking', [UserController::class, 'buat_transaksi']);
    Route::post('/user/favorite', [UserController::class, 'tambah_favorite']);
    Route::delete('/user/favorite/{id}', [UserController::class, 'hapus_favorite']);
});
