<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Archive extends Page
{
    // protected static ?string $navigationIcon = 'heroicon-m-user-group';

    protected static string $view = 'filament.pages.dashboard';

    protected static ?string $navigationLabel = '자료실';

    protected static ?string $title = '자료실';

    protected static ?int $navigationSort = 2;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationGroup = '자료실';
}
