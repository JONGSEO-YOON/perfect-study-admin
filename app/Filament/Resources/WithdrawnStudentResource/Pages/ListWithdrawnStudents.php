<?php

namespace App\Filament\Resources\WithdrawnStudentResource\Pages;

use App\Filament\Resources\WithdrawnStudentResource;
use Filament\Resources\Pages\ListRecords;

class ListWithdrawnStudents extends ListRecords
{
    protected static string $resource = WithdrawnStudentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
