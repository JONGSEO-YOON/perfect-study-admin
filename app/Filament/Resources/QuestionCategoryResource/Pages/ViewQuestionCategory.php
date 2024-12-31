<?php

namespace App\Filament\Resources\QuestionCategoryResource\Pages;

use App\Filament\Resources\QuestionCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewQuestionCategory extends ViewRecord
{
    protected static string $resource = QuestionCategoryResource::class;

    protected ?string $maxContentWidth = '3xl';

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function getBreadcrumb(): string
    {
        return '조회';
    }
}
