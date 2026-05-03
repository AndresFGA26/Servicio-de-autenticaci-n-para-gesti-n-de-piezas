<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\AuthController;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('jwt')->group(function () {
        Route::get('/profile', function () {
            return response()->json([
                'message' => 'Acceso Autenticado'
            ]);
        });
    });
});
