<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Notices extends Page
{
    // protected static ?string $navigationIcon = 'heroicon-m-user-group';

    protected static string $view = 'filament.pages.dashboard';

    protected static ?string $navigationLabel = '공지사항';

    protected static ?string $title = '공지사항';

    protected static ?int $navigationSort = 1;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationGroup = '자료실';
}
