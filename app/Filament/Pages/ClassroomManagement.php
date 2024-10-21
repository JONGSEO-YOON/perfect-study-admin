<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ClassroomManagement extends Page
{
    // protected static ?string $navigationIcon = 'heroicon-m-presentation-chart-line';

    protected static string $view = 'filament.pages.dashboard';

    protected static ?string $navigationLabel = '반 관리';

    protected static ?string $title = '반 관리';

    protected static ?int $navigationSort = 3;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationGroup = '교실 관리';
}
