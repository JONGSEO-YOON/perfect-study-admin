<?php

namespace App\Filament\Resources\CounselorResource\Pages;

use App\Filament\Resources\CounselorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\On;

class ListCounselors extends ListRecords
{
    protected static string $resource = CounselorResource::class;

    protected static ?string $title = '상담실 계정 관리';

    protected ?string $maxContentWidth = '5xl';

    public function getBreadcrumb(): ?string
    {
        return null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-m-plus-circle')
                ->label('상담실 계정 추가하기')
                ->modalHeading('상담실 계정 추가하기')
                ->modalWidth('lg')
                ->createAnother(false)
                ->modalSubmitActionLabel('저장'),
        ];
    }
}
