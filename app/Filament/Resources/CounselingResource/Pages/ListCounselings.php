<?php

namespace App\Filament\Resources\CounselingResource\Pages;

use App\Filament\Resources\CounselingResource;
use App\Filament\Resources\CounselingResource\Widgets\CounselingStatsWidget;
use App\Models\Notification;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\On;

class ListCounselings extends ListRecords
{
    protected static string $resource = CounselingResource::class;

    protected static ?string $title = '상담 관리';

    protected function getHeaderWidgets(): array
    {
        return [
            CounselingStatsWidget::class,
        ];
    }

    public function getBreadcrumb(): ?string
    {
        return null;
    }


    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make('request-counseling')
                ->icon('heroicon-m-plus-circle')
                ->label('상담 요청하기')
                ->modalHeading('상담 요청하기')
                ->createAnother(false)
                ->modalSubmitActionLabel('저장')
                ->fillForm(function () {
                    return [
                        'student_id' => $this->tableFilters['student_id']['value'] ?? null,
                        'request' => true,
                    ];
                })
                ->after(function ($record) {
                    Notification::create([
                        'user_id' => $record->counselor_id,
                        'type' => Notification::TYPE_COUNSELING_REQUEST,
                        'title' => '상담 신청',
                        'content' =>  $record->student->user->name . '학생의 상담 신청이 있습니다.',
                        'data' => ['user_id' => $record->counselor_id, 'student_id' => $record->student_id],
                    ]);
                })
                ->modalWidth('xl'),
            Actions\CreateAction::make('create-counseling')
                ->icon('heroicon-m-pencil-square')
                ->label('상담 기록하기')
                ->modalHeading('상담 기록하기')
                ->createAnother(false)
                ->modalSubmitActionLabel('저장')
                ->fillForm(function () {
                    return [
                        'student_id' => $this->tableFilters['student_id']['value'] ?? null,
                    ];
                })
                ->modalWidth('xl'),
            Actions\Action::make('manage-counselors')
                ->icon('heroicon-m-cog-8-tooth')
                ->color('gray')
                ->url('/admin/counselors')
                ->label('상담실 계정 관리')
                ->modalHeading('상담실 계정 관리')
                ->visible(fn() => auth()->user()->isRoleAbove('admin', true))
                ->modalWidth('xl'),
        ];
    }

    #[On('studentAdded')]
    public function onStudentAdded($record): void
    {
        if ($this->mountedActionsData[0] ?? false) {
            $this->mountedActionsData[0]['student_id'] = $record['id'];
        }
    }
}
