<?php

namespace App\Filament\Resources\TestSheetResource\Pages;

use App\Filament\Resources\TestSheetResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTestSheet extends CreateRecord
{
    protected static string $resource = TestSheetResource::class;
}
