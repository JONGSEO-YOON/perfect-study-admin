<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Exports\StudentsExport;
use App\Filament\Resources\StudentResource;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected static ?string $title = '학생 관리';

    public function getBreadcrumb(): ?string
    {
        return null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-m-plus-circle')
                ->label('학생 추가하기')
                ->modalHeading('학생 추가하기')
                ->visible(fn() => auth()->user()->isRoleAbove('admin', true))
                ->modalWidth('xl')
                ->createAnother(false)
                ->modalSubmitActionLabel('저장'),

            Actions\Action::make('export')
                ->label('엑셀 다운로드')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('success')
                ->modalHeading('학생 목록 엑셀 다운로드')
                ->modalSubmitActionLabel('다운로드')
                ->modalWidth('md')
                ->form([
                    Grid::make(2)->schema([
                        DatePicker::make('start_date')
                            ->label('등록일 시작'),
                        DatePicker::make('end_date')
                            ->label('등록일 종료'),
                    ]),
                ])
                ->action(function (array $data) {
                    $filename = '학생목록_' . now()->format('Ymd_His') . '.xlsx';
                    return Excel::download(
                        new StudentsExport($data['start_date'] ?? null, $data['end_date'] ?? null),
                        $filename
                    );
                }),
        ];
    }
}
