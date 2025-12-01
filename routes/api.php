<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserController as ControllersUserController;
use App\Http\Controllers\BahasaController;
use App\Http\Controllers\PaketController;
use App\Http\Controllers\MateriController;
use App\Http\Controllers\KursusController;

// Cek API
Route::get('/check', function () {
    return response()->json(['message' => 'API is working']);
});

// Auth public
Route::post('/register', [AuthController::class, 'register']);
// Registrasi kursus (ini yang dipakai FE)
Route::post('/registrasi', [AuthController::class, 'registrasiKursus']);
Route::post('/login', [AuthController::class, 'login']);
// Data master
Route::get('/paket', [AuthController::class, 'paket']);
Route::get('/bahasa', [AuthController::class, 'bahasa']);




// Versi API
Route::prefix('/admin')->group(function () {
    // Kursus (join paket + bahasa)
    Route::get('/kursus', [AuthController::class, 'kursus']);
    Route::get('kursus', [KursusController::class, 'index']);
    Route::get('materi', [MateriController::class, 'index']);
    Route::post('materi', [MateriController::class, 'store']);
    Route::put('materi/{id_materi}', [MateriController::class, 'update']);
    Route::delete('materi/{id_materi}', [MateriController::class, 'destroy']);
    //user
    Route::get('users', [UserController::class, 'index']);
    Route::get('/status', function () {
        return response()->json(['version' => 'v1', 'status' => 'ok']);
    });
    Route::put('users/{id}', [UserController::class, 'update']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);
    Route::patch('users/{id}/toggle-verify', [UserController::class, 'toggleVerify']);
    Route::post('users', [UserController::class, 'store']);
    // === PAKET CRUD ===
    Route::get('paket', [PaketController::class, 'index']);
    Route::get('paket/{id}', [PaketController::class, 'show']);
    Route::post('paket', [PaketController::class, 'store']);
    Route::put('paket/{id}', [PaketController::class, 'update']);
    Route::delete('paket/{id}', [PaketController::class, 'destroy']);

    // === BAHASA CRUD ===
    Route::get('bahasa', [BahasaController::class, 'index']);
    Route::get('bahasa/{id}', [BahasaController::class, 'show']);
    Route::post('bahasa', [BahasaController::class, 'store']);
    Route::put('bahasa/{id}', [BahasaController::class, 'update']);
    Route::delete('bahasa/{id}', [BahasaController::class, 'destroy']);

    // Protected routes (harus login pakai Sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', function (Request $request) {
            return response()->json($request->user());
        });

        Route::post('/logout', [AuthController::class, 'logout']);
    });


});
