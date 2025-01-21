<?php

use App\Http\Controllers\SignupController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::post('/signup', [SignupController::class, 'signup']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/pdf-conversion-progress/{attachment}', function ($attachment) {
    return Cache::get("pdf_conversion_{$attachment}", [
        'progress' => 0,
        'currentPage' => 0,
        'totalPages' => 0,
        'material_id' => null,
        'is_public' => false
    ]);
});
