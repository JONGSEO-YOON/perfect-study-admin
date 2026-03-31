<?php

namespace App\Filament\Resources\WithdrawnStudentResource\Pages;

use App\Exports\WithdrawnStudentsExport;
use App\Filament\Resources\WithdrawnStudentResource;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListWithdrawnStudents extends ListRecords
{
    protected static string $resource = WithdrawnStudentResource::class;

    protected static ?string $title = '퇴원생 관리';

    public function getBreadcrumb(): ?string
    {
        return null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('엑셀 다운로드')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('success')
                ->modalHeading('퇴원생 목록 엑셀 다운로드')
                ->modalSubmitActionLabel('다운로드')
                ->modalWidth('md')
                ->form([
                    Grid::make(2)->schema([
                        DatePicker::make('start_date')
                            ->label('퇴원일 시작'),
                        DatePicker::make('end_date')
                            ->label('퇴원일 종료'),
                    ]),
                ])
                ->action(function (array $data) {
                    $filename = '퇴원생목록_' . now()->format('Ymd_His') . '.xlsx';
                    return Excel::download(
                        new WithdrawnStudentsExport($data['start_date'] ?? null, $data['end_date'] ?? null),
                        $filename
                    );
                }),
        ];
    }
}
