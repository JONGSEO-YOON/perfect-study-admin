<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use App\Livewire\QuestionTypeField;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQuestions extends ListRecords
{
    protected static string $resource = QuestionResource::class;

    protected static ?string $title = '문제 은행';

    public function getBreadcrumb(): ?string
    {
        return null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-m-plus-circle')
                ->label('문제 등록하기')
                ->modalHeading('문제 등록하기')
                ->modalWidth('2xl')
                ->createAnother(false)
                ->modalSubmitActionLabel('저장'),
        ];
    }
}
