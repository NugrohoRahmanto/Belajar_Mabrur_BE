<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ContentController;

Route::prefix('users')->group(function () {
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    Route::get('/current', [UserController::class, 'current']);
    Route::delete('/logout', [UserController::class, 'logout']);
});

Route::get('contents', [ContentController::class, 'getContent']);
Route::get('contents/category', [ContentController::class, 'getContentByCategory']);
