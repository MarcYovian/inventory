<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StockMovementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::apiResource('products', ProductController::class);

    Route::apiResource('stock-movements', StockMovementController::class)->only([
        'index',
        'store',
        'show'
    ]);

    Route::get('products/{product}/stock-movements', [StockMovementController::class, 'byProduct']);
});
