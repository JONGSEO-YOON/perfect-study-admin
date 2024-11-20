<?php

namespace App\Filament\Resources\TestSheetResource\Pages;

use App\Filament\Resources\TestSheetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTestSheets extends ListRecords
{
    protected static string $resource = TestSheetResource::class;

    protected static ?string $title = '문제지 관리';

    public function getBreadcrumb(): ?string
    {
        return null;
    }

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
