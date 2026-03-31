<?php

use App\Http\Controllers\JusoPopupController;
use App\Http\Middleware\ParentSession;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserWebController;
use App\Http\Controllers\TossPaymentController;
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
use App\Livewire\AttendanceCalendar;
use App\Livewire\Attendance;
use App\Livewire\Payment;
use App\Livewire\PaymentResult;
use App\Livewire\Parent\Login;
use App\Livewire\Parent\Main;
use App\Livewire\Parent\Home;
use App\Livewire\Parent\Report;
use App\Livewire\Parent\ReportDetail;
use App\Livewire\Parent\Attendance as ParentAttendance;
use App\Livewire\Parent\Notice;
use App\Livewire\Parent\StudentNotice as ParentStudentNotice;
use App\Livewire\Parent\Payment as ParentPayment;
use App\Livewire\Parent\RefundPolicy;

// 부모 로그인 그룹
Route::prefix('parent')->group(function () {
    Route::get('/', Login::class)->name('parent.login');
    Route::get('/home', Home::class)->name('parent.home')->middleware(ParentSession::class);
    Route::get('/notice/{id}', Notice::class)->name('parent.notice')->middleware(ParentSession::class);
    Route::get('/student-notice/{id}', ParentStudentNotice::class)->name('parent.student-notice')->middleware(ParentSession::class);
    Route::get('/attendance', ParentAttendance::class)->name('parent.attendance')->middleware(ParentSession::class);
    Route::get('/report', Report::class)->name('parent.report')->middleware(ParentSession::class);
    Route::get('/report-detail/{weekKey}', ReportDetail::class)->name('parent.report-detail')->middleware(ParentSession::class);
    Route::get('/payment', ParentPayment::class)->name('parent.payment')->middleware(ParentSession::class);
    Route::get('/refund-policy', RefundPolicy::class)->name('parent.refund-policy')->middleware(ParentSession::class);
});

Route::get('/attendance', Attendance::class)->name('attendance');
Route::get('/attendance-calendar/{studentData}', AttendanceCalendar::class)->name('attendance-calendar');

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

Route::get('/switch-academy/{userId}', function ($userId) {
    $currentUser = auth()->user();
    if (!$currentUser) return redirect('/login');

    $targetUser = \App\Models\User::find($userId);
    if (!$targetUser) return redirect('/');

    // 같은 전화번호인지 확인 (보안)
    if ($targetUser->phone !== $currentUser->phone) {
        return redirect('/');
    }

    // 학생 계정인지 확인
    if ($targetUser->userable_type !== \App\Models\Student::class) {
        return redirect('/');
    }

    auth()->login($targetUser);
    session()->regenerate();
    return redirect('/');
})->name('switch-academy')->middleware('auth');

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


Route::get('/payment/{paymentId}', Payment::class)->name('payment');
Route::get('/payment/{paymentId}/result', PaymentResult::class)->name('payment.result');
Route::get('/toss-payments/success', [TossPaymentController::class, 'handleSuccess']);
Route::get('/toss-payments/fail', [TossPaymentController::class, 'handleFailure']);

// 학원 경로 기반 접속: /a/{slug} → 해당 학원 컨텍스트로 전환
Route::get('/a/{slug}/{path?}', function ($slug, $path = null) {
    $academy = \App\Models\Academy::where('slug', $slug)->where('is_active', true)->first();
    if (!$academy) {
        abort(404, '학원을 찾을 수 없습니다.');
    }
    session(['academy_slug' => $slug]);
    // 로그아웃 후 해당 학원 로그인 페이지로 리다이렉트
    if (auth()->check()) {
        auth()->logout();
    }
    $redirectPath = $path ? '/' . $path : '/admin/login';
    return redirect($redirectPath);
})->where('path', '.*');
