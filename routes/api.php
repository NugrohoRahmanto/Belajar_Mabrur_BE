<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ContentController;
Route::post('users', [UserController::class, 'register']);
Route::post('users/login', [UserController::class, 'login']);
Route::get('users/current', [UserController::class, 'current']);
Route::delete('users/logout', [UserController::class, 'logout']);


Route::get('contents', [ContentController::class, 'index']);
Route::get('contentsCategory', [ContentController::class, 'index1']);