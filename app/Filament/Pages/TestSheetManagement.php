<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class TestSheetManagement extends Page
{
    // protected static ?string $navigationIcon = 'heroicon-m-newspaper';

    protected static string $view = 'filament.pages.dashboard';

    protected static ?string $navigationLabel = '문제지 관리';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = '문제지 관리';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationGroup = '교실 관리';

    public static function canAccess(): bool
    {
        return \App\Models\Academy::isMenuGroupVisibleForCurrentUser('classroom');
    }
}
