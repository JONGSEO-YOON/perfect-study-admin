<?php

namespace App\Filament\Resources\DailyAttendanceResource\Pages;

use App\Filament\Resources\DailyAttendanceResource;
use Carbon\Carbon;
use Filament\Resources\Pages\ListRecords;

class ListDailyAttendance extends ListRecords
{
    protected static string $resource = DailyAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        $selectedDate = $this->tableFilters['date']['value'] ?? now()->format('Y-m-d');
        $date = Carbon::parse($selectedDate)->locale('ko');
        return '일별출결 현황 - ' . $date->format('Y년 n월 j일') . ' (' . $date->dayName . ')';
    }
}