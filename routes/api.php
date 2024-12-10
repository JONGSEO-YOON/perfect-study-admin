<?php

use App\Http\Controllers\SignupController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/signup', [SignupController::class, 'signup']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
