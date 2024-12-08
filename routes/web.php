<?php

use App\Http\Controllers\JusoPopupController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('login');
});

Route::get('/main', function () {
    return view('main');
});

Route::get('/test-sheet', function () {
    return view('test-sheet');
});

Route::get('/test-sheet-result', function () {
    return view('test-sheet-result');
});

Route::get('/test-sheet-result-detail', function () {
    return view('test-sheet-result-detail');
});

Route::get('/preview-test-sheet', function () {
    $scale = request('scale', 1);
    return view('preview-test-sheet', compact('scale'));
});


Route::get('/juso-popup', [JusoPopupController::class, 'show']);
Route::post('/juso-popup', [JusoPopupController::class, 'show']);
