<?php

use App\Http\Controllers\JusoPopupController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('login');
});

Route::get('/preview-test-sheet', function () {
    $scale = request('scale', 1);
    return view('preview-test-sheet', compact('scale'));
});


Route::get('/juso-popup', [JusoPopupController::class, 'show']);
Route::post('/juso-popup', [JusoPopupController::class, 'show']);
