<?php

namespace App\Filament\Resources\StudentHistoryResource\Pages;

use App\Filament\Resources\StudentHistoryResource;
use Filament\Resources\Pages\ListRecords;

class ListStudentHistories extends ListRecords
{
    protected static string $resource = StudentHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
