<?php

use App\Http\Controllers\AuthController;
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

Route::middleware('auth:sanctum')->post(
    '/api/auth/logout',
    [AuthController::class, 'logout']
);

Route::middleware('auth:sanctum')->get(
    '/api/auth/me',
    [AuthController::class, 'me']
);
