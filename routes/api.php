<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Rutas PÚBLICAS
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/users', [UserController::class, 'store']); // Registro público

// Rutas PROTEGIDAS
Route::middleware(['auth:api'])->group(function () {
    // Autenticación
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    
    // Usuarios
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::patch('/users/{id}/restore', [UserController::class, 'restore']);
});