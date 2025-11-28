<?php

namespace App\Filament\Resources\StudentNoticeResource\Pages;

use App\Filament\Resources\StudentNoticeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudentNotices extends ListRecords
{
    protected static string $resource = StudentNoticeResource::class;

    protected static ?string $title = '학생 공지';

    public function getBreadcrumb(): ?string
    {
        return null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-m-plus-circle')
                ->label('공지 추가하기')
                ->modalHeading('공지 추가하기')
                ->visible(
                    fn() => auth()->user()->isRoleAbove('admin', true) || !auth()->user()->userable instanceof \App\Models\Teacher
                )
                ->modalWidth('4xl')
                ->createAnother(false)
                ->modalSubmitActionLabel('저장'),
        ];
    }
}
