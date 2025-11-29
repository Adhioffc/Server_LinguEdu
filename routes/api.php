<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController as ControllersUserController;

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
// Kursus (join paket + bahasa)
Route::get('/kursus', [AuthController::class, 'kursus']);

// Versi API
Route::prefix('/admin')->group(function () {

    Route::get('users', [UserController::class, 'index']);

    Route::get('/status', function () {
        return response()->json(['version' => 'v1', 'status' => 'ok']);
    });
    Route::put('users/{id}', [UserController::class, 'update']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);
    Route::patch('users/{id}/toggle-verify', [UserController::class, 'toggleVerify']);
    Route::post('users', [UserController::class, 'store']);

    // Protected routes (harus login pakai Sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', function (Request $request) {
            return response()->json($request->user());
        });

        Route::post('/logout', [AuthController::class, 'logout']);
    });


});
