<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class QuestionBank extends Page
{
    // protected static ?string $navigationIcon = 'heroicon-m-clipboard-document-list';

    protected static string $view = 'filament.pages.dashboard';

    protected static ?string $navigationLabel = '문제 은행';

    protected static ?string $title = '문제 은행';

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationGroup = '문제 관리';

    protected static ?int $navigationSort = 1;
}
