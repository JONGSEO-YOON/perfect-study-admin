<?php

namespace App\Filament\Resources\NoticeResource\Pages;

use App\Filament\Resources\NoticeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNotices extends ListRecords
{
    protected static string $resource = NoticeResource::class;

    protected static ?string $title = '공지사항';

    public function getBreadcrumb(): ?string
    {
        return null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->icon('heroicon-m-plus-circle')
                ->label('공지 추가하기')
                ->modalHeading('공지 추가하기')
                ->modalWidth('4xl')
                ->createAnother(false)
                ->modalSubmitActionLabel('저장'),
        ];
    }
}
