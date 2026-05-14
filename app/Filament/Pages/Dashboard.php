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

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        // 대시보드는 root_admin, admin(학원 관리자)만 접근 가능
        // 그 외 강사/매니저/상담실은 메뉴에 보이지 않고 접근 시 권한 없음
        return in_array($user->role, ['root_admin', 'admin']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
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
