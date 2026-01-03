<?php

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNotifications extends ListRecords
{
    protected static string $resource = NotificationResource::class;

    protected static ?string $title = '내 알림';

    public function getBreadcrumb(): ?string
    {
        return null;
    }

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
