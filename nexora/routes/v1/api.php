<?php

use App\Http\Controllers\v1\AuthController;
use App\Http\Controllers\v1\OrganizationController;
use App\Http\Controllers\v1\OrganizationUserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    //verification email route
    Route::controller(AuthController::class)->group(function () {
        Route::post('/signup', 'signup');
        Route::post('/login', 'login');
        Route::post('/forgot-password', 'forgotPassword');
        Route::post('/reset-password', 'resetPassword');
        Route::post('/email/verify', 'verifyEmail'); //verify email using token from email link
    });
    Route::middleware('auth:sanctum')->group(function () {
        Route::controller(AuthController::class)->group(function () {
            Route::post('/logout', 'logout');
            Route::delete('/user/{id?}', 'deleteUser');
            Route::delete('/permanent/user/{id?}', 'deleteForeverUser');
            Route::post('/resend-verification-email', 'resendVerificationEmail');

        });
        Route::controller(OrganizationController::class)->prefix('organization')->group(function () {
            Route::post('/', 'createOrganization')->middleware('permission:organization.create');
            Route::get('/', 'getOrganizations');
            Route::get('/{id}', 'getOrganizationDetails');
            Route::patch('/{id}', 'updateOrganization');
            Route::delete('/{id}', 'deleteOrganization');
            Route::delete('/permanent/{id}', 'deleteForeverOrganization');
            Route::patch('/settings/{id}', 'updateOrganizationSettings');
        });
        Route::controller(OrganizationUserController::class)->prefix('organization/user')->group(function () {
            Route::post('/', 'addUserToOrganization')->middleware('permission:organization.user.create');
            Route::get('/{org_id}', 'getOrganizationUsers')->middleware('permission:organization.user.view');
            Route::delete('/{org_id}/{user_id}', 'removeUserFromOrganization')->middleware('permission:organization.user.delete');
            Route::patch('/{org_id}/{user_id}', 'updateUser')->middleware('permission:organization.user.update');
        });
    });
});
