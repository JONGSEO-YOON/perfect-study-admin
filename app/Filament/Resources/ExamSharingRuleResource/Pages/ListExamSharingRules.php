<?php

namespace App\Filament\Resources\ExamSharingRuleResource\Pages;

use App\Filament\Resources\ExamSharingRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExamSharingRules extends ListRecords
{
    protected static string $resource = ExamSharingRuleResource::class;

    protected static ?string $title = '기출 공유 권한';

    public function getSubheading(): ?string
    {
        return '기본적으로 모든 기출문제는 전체 학원에 공개됩니다. 특정 학원의 접근을 차단하려면 규칙을 추가하세요.';
    }

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('공유 규칙 추가')];
    }
}
