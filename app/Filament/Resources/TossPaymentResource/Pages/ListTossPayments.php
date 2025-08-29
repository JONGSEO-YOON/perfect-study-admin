<?php

namespace App\Filament\Resources\TossPaymentResource\Pages;

use App\Filament\Resources\TossPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTossPayments extends ListRecords
{
    protected static string $resource = TossPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}