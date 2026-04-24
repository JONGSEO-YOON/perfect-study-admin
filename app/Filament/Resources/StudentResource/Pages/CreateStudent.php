<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use App\Models\StudentStatusHistory;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected function afterCreate(): void
    {
        // 학생 등록 시 승인대기 상태로 설정
        $this->record->update(['status' => 'pending']);

        // 이력 기록: 신규 등록
        StudentStatusHistory::create([
            'student_id' => $this->record->id,
            'changed_by' => auth()->id(),
            'event_type' => 'created',
            'from_status' => null,
            'to_status' => 'pending',
            'reason' => '신규 등록',
            'memo' => $this->record->user?->name ? "학생명: {$this->record->user->name}" : null,
        ]);
    }
}
