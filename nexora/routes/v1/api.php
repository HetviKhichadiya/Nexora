<?php

use App\Http\Controllers\v1\AuthController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    //verification email route
    Route::controller(AuthController::class)->group(function () {
        Route::post('/signup', 'signup');
        Route::post('/login', 'login');
        Route::post('/forgot-password', 'forgotPassword');
        Route::post('/reset-password', 'resetPassword');
        Route::post('/email/verification', 'sendVerificationEmail'); //send email verification link to user email
        Route::post('/email/verify', 'verifyEmail'); //verify email using token from email link
    });
    Route::controller(AuthController::class)->middleware('auth:sanctum')->group(function () {
        Route::post('/logout', 'logout');
        Route::delete('/user/{id?}', 'deleteUser');
        Route::delete('/permanent/user/{id?}', 'deleteForeverUser');
    });
});
