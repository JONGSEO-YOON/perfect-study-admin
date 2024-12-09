<?php

use App\Http\Controllers\JusoPopupController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserWebController;
use App\Livewire\TestSheetQuestionResult;
use App\Livewire\TestSheetResult;

Route::get('/login', [UserWebController::class, 'showLoginForm'])->name('login');
Route::post('/login', [UserWebController::class, 'login']);

Route::get('/', [UserWebController::class, 'main'])->name('main');
Route::get('/test-sheet/{id}', [UserWebController::class, 'showTestSheet'])->name('test.sheet');
Route::get('/test-sheet-result/{id}', TestSheetResult::class)->name('test-sheet.result');
// Route::get('/test-sheet-result/{id}', [UserWebController::class, 'showTestSheetResult'])->name('test.result');
Route::get('/test-sheet-result/{id}/{questionNo}', TestSheetQuestionResult::class)->name('test-sheet.question.result');
// Route::get('/test-sheet-result/{id}/{question_id}', [UserWebController::class, 'showTestSheetResultQuestion'])->name('test.result.question');

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/login', function () {
//     return view('login');
// });

// Route::get('/main', function () {
//     return view('main');
// });

// Route::get('/test-sheet', function () {
//     return view('test-sheet');
// });

// Route::get('/test-sheet-result', function () {
//     return view('test-sheet-result');
// });

// Route::get('/test-sheet-result-detail', function () {
//     return view('test-sheet-result-detail');
// });

Route::get('/preview-test-sheet', function () {
    $scale = request('scale', 1);
    return view('preview-test-sheet', compact('scale'));
});


Route::get('/juso-popup', [JusoPopupController::class, 'show']);
Route::post('/juso-popup', [JusoPopupController::class, 'show']);
