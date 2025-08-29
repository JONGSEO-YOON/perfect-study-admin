<?php

namespace App\Filament\Resources\TossPaymentResource\Pages;

use App\Filament\Resources\TossPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTossPayment extends ViewRecord
{
    protected static string $resource = TossPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}