<?php

use App\Http\Controllers\JusoPopupController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserWebController;
use App\Livewire\StudentReportCardPrint;
use App\Livewire\TestSheetQuestionResult;
use App\Livewire\TestSheetResult;
use App\Livewire\TestSheetViewer;


Route::get('/login', [UserWebController::class, 'showLoginForm'])->name('login');
Route::post('/login', [UserWebController::class, 'login']);

Route::get('/', [UserWebController::class, 'main'])->name('main');
// Route::get('/test-sheet/{id}', [UserWebController::class, 'showTestSheet'])->name('test.sheet');
Route::get('/test-sheet/{id}', TestSheetViewer::class)->name('test.sheet');
Route::get('/test-sheet-result/{id}', TestSheetResult::class)->name('test-sheet.result');
Route::get('/test-sheet-result/{id}/{questionNo}', TestSheetQuestionResult::class)->name('test-sheet.question.result');

Route::get('/logout', function () {
    auth()->logout();
    return redirect('/');
});

Route::get('/preview-test-sheet', function () {
    $scale = request('scale', 1);
    return view('preview-test-sheet', compact('scale'));
});


Route::get('/juso-popup', [JusoPopupController::class, 'show']);
Route::post('/juso-popup', [JusoPopupController::class, 'show']);


Route::get('/student/report-card/print', StudentReportCardPrint::class)
    ->name('student.report-card.print');
