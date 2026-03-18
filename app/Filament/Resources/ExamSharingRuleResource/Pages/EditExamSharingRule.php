<?php

namespace App\Filament\Resources\ExamSharingRuleResource\Pages;

use App\Filament\Resources\ExamSharingRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExamSharingRule extends EditRecord
{
    protected static string $resource = ExamSharingRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
