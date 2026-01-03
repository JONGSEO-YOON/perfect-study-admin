<?php

namespace App\Filament\Resources\TestSheetResource\Pages;

use App\Filament\Resources\TestSheetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTestSheet extends EditRecord
{
    protected static string $resource = TestSheetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
