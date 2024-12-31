<?php

namespace App\Filament\Resources\QuestionCategoryResource\Pages;

use App\Filament\Resources\QuestionCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQuestionCategory extends EditRecord
{
    protected static string $resource = QuestionCategoryResource::class;

    protected ?string $maxContentWidth = '3xl';

    protected static ?string $title = '문제 유형표 수정';

    public function getBreadcrumb(): string
    {
        return '수정';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
