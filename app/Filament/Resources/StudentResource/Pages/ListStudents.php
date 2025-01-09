<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected static ?string $title = '학생 관리';

    public function getBreadcrumb(): ?string
    {
        return null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-m-plus-circle')
                ->label('학생 추가하기')
                ->modalHeading('학생 추가하기')
                ->visible(fn() => auth()->user()->isRoleAbove('admin', true))
                ->modalWidth('xl')
                ->createAnother(false)
                ->modalSubmitActionLabel('저장'),
        ];
    }
}
