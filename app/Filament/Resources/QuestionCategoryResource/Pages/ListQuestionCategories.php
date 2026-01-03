<?php

namespace App\Filament\Resources\QuestionCategoryResource\Pages;

use App\Filament\Resources\QuestionCategoryResource;
use App\Filament\Resources\QuestionCategoryResource\Widgets\QuestionCategoryOverview;
use App\Filament\Resources\QuestionResource\Widgets\BookOverview;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQuestionCategories extends ListRecords
{
    protected static string $resource = QuestionCategoryResource::class;

    protected static ?string $title = '문제 유형표 관리';

    protected static string $view = 'filament.pages.list-question-categories';

    public function getBreadcrumb(): ?string
    {
        return null;
    }

    protected ?string $maxContentWidth = '2xl';

    protected function getHeaderWidgets(): array
    {
        return [
            QuestionCategoryOverview::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
