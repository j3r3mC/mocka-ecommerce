<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/api/auth/register', [
    AuthController::class,
    'register',
]);

Route::post('/api/auth/login', [
    AuthController::class,
    'login',
]);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/api/auth/logout', [
        AuthController::class,
        'logout',
    ]);

    Route::get('/api/auth/me', [
        AuthController::class,
        'me',
    ]);

    Route::get('/api/profile', [
        ProfileController::class,
        'show',
    ]);

    Route::post('/api/profile', [
        ProfileController::class,
        'store',
    ]);
    Route::patch('/api/profile', [
        ProfileController::class,
        'update',
    ]);
    Route::post('/api/profile/avatar', [
        ProfileController::class,
        'uploadAvatar',
    ]);
});