<?php

namespace App\Filament\Resources\LectureResource\Pages;

use App\Filament\Resources\LectureResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLectures extends ListRecords
{
    protected static string $resource = LectureResource::class;

    protected static ?string $title = '강의실';

    public function getBreadcrumb(): ?string
    {
        return null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-m-plus-circle')
                ->label('강의 개설하기')
                ->modalHeading('강의 개설하기')
                ->modalWidth('4xl')
                ->createAnother(false)
                ->modalSubmitActionLabel('저장'),
        ];
    }
}
