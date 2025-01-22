<?php

use App\Http\Controllers\JusoPopupController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserWebController;
use App\Livewire\MyReportCard;
use App\Livewire\StudentLectureList;
use App\Livewire\StudentLectureViewer;
use App\Livewire\StudentNoticeList;
use App\Livewire\StudentPasswordChange;
use App\Livewire\StudentReportCard;
use App\Livewire\StudentReportCardPrint;
use App\Livewire\TestSheetPrint;
use App\Livewire\TestSheetQuestionResult;
use App\Livewire\TestSheetResult;
use App\Livewire\TestSheetViewer;


Route::get('/login', [UserWebController::class, 'showLoginForm'])->name('login');
Route::post('/login', [UserWebController::class, 'login']);

Route::get('/', [UserWebController::class, 'main'])->name('main');
Route::get('/test-sheet/{id}', TestSheetViewer::class)->name('test.sheet')->middleware('student.check');
Route::get('/test-sheet-result/{id}', TestSheetResult::class)->name('test-sheet.result')->middleware('student.check');
Route::get('/test-sheet-result/{id}/{questionNo}', TestSheetQuestionResult::class)->name('test-sheet.question.result')->middleware('student.check');

Route::get('/lectures', StudentLectureList::class)->name('student.lectures')->middleware('student.check');
Route::get('/lectures/{id}', StudentLectureViewer::class)->name('student.lectures.view')->middleware('student.check');

Route::get('/report-card', MyReportCard::class)->name('student.report.card')->middleware('student.check');
Route::get('/notices', StudentNoticeList::class)->name('student.notices')->middleware('student.check');

Route::get('/password-change', StudentPasswordChange::class)->name('student.password')->middleware('student.check');

Route::get('/logout', function () {
    auth()->logout();
    return redirect('/');
});

Route::get('/preview-test-sheet', function () {
    $scale = request('scale', 1);
    $readonly = request('readonly', false);
    $layoutMode = request('layoutMode', "default");
    return view('preview-test-sheet', compact('scale', 'readonly', 'layoutMode'));
});


Route::get('/juso-popup', [JusoPopupController::class, 'show']);
Route::post('/juso-popup', [JusoPopupController::class, 'show']);


// 출력용 라우트
Route::get('/student/report-card/print', StudentReportCardPrint::class)
    ->name('student.report-card.print');

Route::get('/admin/test-sheet/print', TestSheetPrint::class)
    ->name('test-sheet.print');
