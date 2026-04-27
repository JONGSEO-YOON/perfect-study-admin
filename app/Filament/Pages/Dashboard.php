<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\DateDisplayWidget;
use App\Filament\Widgets\DailyAttendanceWidget;
use App\Filament\Widgets\DailyPaymentWidget;
use App\Filament\Widgets\StudentEnrollmentWidget;
use App\Filament\Widgets\CounselingWidget;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = '대시보드';

    //title
    protected static ?string $title = '대시보드';

    //shouldRegisterNavigation
    protected static bool $shouldRegisterNavigation = true;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        // Filament 패널에 로그인 가능한 모든 강사(Teacher) 역할 접근 허용
        // (students 미들웨어에서 Student는 이미 차단됨)
        // 역할별 위젯 노출은 위젯 단에서 별도 제어
        return $user->userable_type === \App\Models\Teacher::class;
    }

    public function getWidgets(): array
    {
        return [
            DateDisplayWidget::class,
            DailyAttendanceWidget::class,
            DailyPaymentWidget::class,
            StudentEnrollmentWidget::class,
            CounselingWidget::class,
        ];
    }
}
