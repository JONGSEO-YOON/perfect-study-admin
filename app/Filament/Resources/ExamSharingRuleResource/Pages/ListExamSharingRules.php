<?php

namespace App\Filament\Resources\ExamSharingRuleResource\Pages;

use App\Filament\Resources\ExamSharingRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExamSharingRules extends ListRecords
{
    protected static string $resource = ExamSharingRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('공유 규칙 추가')];
    }
}
