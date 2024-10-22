<?php

use App\Http\Controllers\JusoPopupController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/juso-popup', [JusoPopupController::class, 'show']);
Route::post('/juso-popup', [JusoPopupController::class, 'show']);
