<?php

namespace App\Filament\Resources\ExamSharingRuleResource\Pages;

use App\Filament\Resources\ExamSharingRuleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExamSharingRule extends CreateRecord
{
    protected static string $resource = ExamSharingRuleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // admin은 자기 학원 ID 자동 설정
        if (auth()->user()->role !== 'root_admin') {
            $data['academy_id'] = auth()->user()->academy_id;
        }

        return $data;
    }
}
