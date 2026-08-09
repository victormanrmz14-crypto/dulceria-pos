<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductoApiController;
use Illuminate\Support\Facades\Route;

// Autenticación — sin token
Route::post('/login',  [AuthController::class, 'login'])->middleware('throttle:5,1');

// Rutas protegidas con Bearer Token
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // Productos — recurso RESTful completo (GET, POST, PUT, PATCH, DELETE)
    Route::apiResource('productos', ProductoApiController::class);

});
