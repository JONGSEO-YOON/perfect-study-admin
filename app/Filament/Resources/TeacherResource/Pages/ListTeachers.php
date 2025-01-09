<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTeachers extends ListRecords
{
    protected static string $resource = TeacherResource::class;

    protected static ?string $title = '강사 관리';

    public function getBreadcrumb(): ?string
    {
        return null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-m-plus-circle')
                ->label('강사 추가하기')
                ->modalHeading('강사 추가하기')
                ->modalWidth('xl')
                ->createAnother(false)
                ->visible(
                    fn() => auth()->user()->isRoleAbove('manager', true)
                        ||  !auth()->user()->userable instanceof \App\Models\Teacher
                )
                ->modalSubmitActionLabel('저장'),
        ];
    }
}
