<?php

namespace App\Filament\Resources\ResourceCategoryResource\Pages;

use App\Filament\Resources\ResourceCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResourceCategory extends EditRecord
{
    protected static string $resource = ResourceCategoryResource::class;

    protected static ?string $title = '자료실 관리';

    protected ?string $maxContentWidth = 'xl';

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->modalHeading('자료실 분류 삭제하기'),
        ];
    }
}
