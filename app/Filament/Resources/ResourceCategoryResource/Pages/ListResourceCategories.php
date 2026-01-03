<?php

namespace App\Filament\Resources\ResourceCategoryResource\Pages;

use App\Filament\Resources\ResourceCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListResourceCategories extends ListRecords
{
    protected static string $resource = ResourceCategoryResource::class;

    protected static ?string $title = '자료실 관리';

    protected ?string $maxContentWidth = 'xl';

    public function getBreadcrumb(): ?string
    {
        return null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->icon('heroicon-m-plus-circle')
                ->label('자료실 분류 추가하기')
                ->modalHeading('자료실 분류 추가하기')
                ->modalWidth('lg')
                ->createAnother(false)
                ->modalSubmitActionLabel('저장'),
        ];
    }
}
