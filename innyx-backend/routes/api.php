<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Rota pública para login (onde o Front vai buscar o token)
Route::post('/login', [AuthController::class, 'login']);

// Rotas protegidas (exigem Token Bearer)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('products', ProductController::class);
});
