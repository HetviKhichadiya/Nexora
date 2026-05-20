<?php

use App\Http\Controllers\v1\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('/signup', 'signup');
        Route::post('/login', 'login');
    });
    Route::controller(AuthController::class)->middleware('auth:sanctum')->group(function () {
        Route::post('/logout', 'logout');
        Route::delete('/user/{id}', 'deleteUser');
    });
});
