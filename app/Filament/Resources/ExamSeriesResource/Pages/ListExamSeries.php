<?php

namespace App\Filament\Resources\ExamSeriesResource\Pages;

use App\Filament\Resources\ExamSeriesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExamSeries extends ListRecords
{
    protected static string $resource = ExamSeriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('문제계열 추가'),
        ];
    }
}
