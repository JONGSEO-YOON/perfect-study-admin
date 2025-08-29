<?php

namespace App\Filament\Resources\TossPaymentResource\Pages;

use App\Filament\Resources\TossPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTossPayment extends EditRecord
{
    protected static string $resource = TossPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}