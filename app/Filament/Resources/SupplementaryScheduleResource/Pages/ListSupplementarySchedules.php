<?php

namespace App\Filament\Resources\SupplementaryScheduleResource\Pages;

use App\Filament\Resources\SupplementaryScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSupplementarySchedules extends ListRecords
{
    protected static string $resource = SupplementaryScheduleResource::class;

    protected static ?string $title = '보충 달력';

    public function getBreadcrumb(): ?string
    {
        return null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('보충 추가')
                ->icon('heroicon-m-plus-circle')
                ->modalHeading('보충 일정 추가')
                ->modalSubmitActionLabel('저장')
                ->createAnother(false)
                ->modalWidth('xl'),
        ];
    }
}
