<?php

namespace App\Filament\Resources\ClassroomResource\Pages;

use App\Exports\ClassroomsExport;
use App\Filament\Resources\ClassroomResource;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListClassrooms extends ListRecords
{
    protected static string $resource = ClassroomResource::class;

    protected static ?string $title = '반 관리';

    public function getBreadcrumb(): ?string
    {
        return null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-m-plus-circle')
                ->label('반 추가하기')
                ->modalHeading('반 추가하기')
                ->createAnother(false)
                ->modalSubmitActionLabel('저장')
                ->modalWidth('xl'),

            Actions\Action::make('export')
                ->label('엑셀 다운로드')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('success')
                ->modalHeading('반 목록 엑셀 다운로드')
                ->modalSubmitActionLabel('다운로드')
                ->modalWidth('md')
                ->form([
                    Grid::make(2)->schema([
                        DatePicker::make('start_date')
                            ->label('개설일 시작'),
                        DatePicker::make('end_date')
                            ->label('개설일 종료'),
                    ]),
                ])
                ->action(function (array $data) {
                    $filename = '반목록_' . now()->format('Ymd_His') . '.xlsx';
                    return Excel::download(
                        new ClassroomsExport($data['start_date'] ?? null, $data['end_date'] ?? null),
                        $filename
                    );
                }),
        ];
    }
}
