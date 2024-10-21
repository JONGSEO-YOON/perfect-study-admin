<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class TeacherManagement extends Page
{
    // protected static ?string $navigationIcon = 'heroicon-m-user-group';

    protected static string $view = 'filament.pages.dashboard';

    protected static ?string $navigationLabel = '강사 관리';

    protected static ?string $title = '강사 관리';

    protected static ?int $navigationSort = 4;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationGroup = '교실 관리';
}
