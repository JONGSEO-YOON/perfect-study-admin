<?php

namespace App\Filament\Resources\WithdrawnStudentResource\Pages;

use App\Filament\Resources\WithdrawnStudentResource;
use Filament\Resources\Pages\ListRecords;

class ListWithdrawnStudents extends ListRecords
{
    protected static string $resource = WithdrawnStudentResource::class;

    protected static ?string $title = '퇴원생 관리';

    public function getBreadcrumb(): ?string
    {
        return null;
    }
}
