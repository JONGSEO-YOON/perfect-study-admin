<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\StudentStatsWidget;
use App\Filament\Widgets\DateDisplayWidget;
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

        // 일반 강사(general)는 접근 불가
        if ($user && $user->role === 'general') {
            return false;
        }

        // 관리자, 매니저 등은 접근 가능
        return $user && in_array($user->role, ['root_admin', 'admin', 'manager']);
    }

    public function getWidgets(): array
    {
        return [
            DateDisplayWidget::class,
            StudentStatsWidget::class,
        ];
    }
}
