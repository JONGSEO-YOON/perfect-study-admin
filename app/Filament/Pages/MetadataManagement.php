<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class MetadataManagement extends Page
{
    // protected static ?string $navigationIcon = 'heroicon-m-circle-stack';

    protected static string $view = 'filament.pages.dashboard';

    protected static ?string $navigationLabel = '메타 데이터 관리';

    protected static ?string $title = '메타 데이터 관리';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationGroup = '문제 관리';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return \App\Models\Academy::isMenuGroupVisibleForCurrentUser('tests');
    }
}
