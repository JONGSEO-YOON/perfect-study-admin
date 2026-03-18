<?php

namespace App\Filament\Resources\AcademyResource\Pages;

use App\Filament\Resources\AcademyResource;
use App\Filament\Resources\AcademyResource\RelationManagers\UsersRelationManager;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAcademy extends EditRecord
{
    protected static string $resource = AcademyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            UsersRelationManager::class,
        ];
    }
}
