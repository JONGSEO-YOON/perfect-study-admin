<?php

namespace App\Filament\Resources\CounselorResource\Pages;

use App\Filament\Resources\CounselorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCounselor extends EditRecord
{
    protected static string $resource = CounselorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
