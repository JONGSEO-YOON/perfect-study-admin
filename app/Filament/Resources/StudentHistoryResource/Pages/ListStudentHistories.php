<?php

namespace App\Filament\Resources\StudentHistoryResource\Pages;

use App\Filament\Resources\StudentHistoryResource;
use Filament\Resources\Pages\ListRecords;

class ListStudentHistories extends ListRecords
{
    protected static string $resource = StudentHistoryResource::class;

    protected static ?string $title = '학생 이력관리';

    public function getBreadcrumb(): ?string
    {
        return null;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
