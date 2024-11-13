<?php

namespace App\Filament\Resources\CounselingResource\Pages;

use App\Filament\Resources\CounselingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCounselings extends ListRecords
{
    protected static string $resource = CounselingResource::class;

    protected static ?string $title = '상담 관리';

    public function getBreadcrumb(): ?string
    {
        return null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make('request-counseling')
                ->icon('heroicon-m-plus-circle')
                ->label('상담 요청하기')
                ->modalHeading('상담 요청하기')
                ->createAnother(false)
                ->modalSubmitActionLabel('저장')
                ->fillForm([
                    'request' => true,
                ])
                ->modalWidth('xl'),
            Actions\CreateAction::make('create-counseling')
                ->icon('heroicon-m-pencil-square')
                ->label('상담 기록하기')
                ->modalHeading('상담 기록하기')
                ->createAnother(false)
                ->modalSubmitActionLabel('저장')
                ->modalWidth('xl'),
            Actions\Action::make('manage-counselors')
                ->icon('heroicon-m-cog-8-tooth')
                ->color('gray')
                ->url('/admin/counselors')
                ->label('상담실 계정 관리')
                ->modalHeading('상담실 계정 관리')
                ->modalWidth('xl'),
        ];
    }
}
