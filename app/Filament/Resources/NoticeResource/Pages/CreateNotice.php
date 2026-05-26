<?php

namespace App\Filament\Resources\NoticeResource\Pages;

use App\Filament\Resources\NoticeResource;
use App\Models\Student;
use App\Services\FcmService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateNotice extends CreateRecord
{
    protected static string $resource = NoticeResource::class;

    protected function afterCreate(): void
    {
        $this->sendNoticePushToParents();
    }

    protected function sendNoticePushToParents(): void
    {
        try {
            $notice = $this->record;
            $targets = $notice->target_groups ?? [];

            if (!in_array('학부모', $targets, true)) {
                return;
            }

            $academyId = $notice->academy_id;

            // 해당 학원 학생들의 부모 연락처 수집
            $studentQuery = Student::query();
            if ($academyId) {
                $studentQuery->where('academy_id', $academyId);
            }

            $parentPhones = [];
            $studentQuery
                ->select('phone_father', 'phone_mother')
                ->cursor()
                ->each(function ($student) use (&$parentPhones) {
                    if ($student->phone_father && $student->phone_father !== '010--') {
                        $parentPhones[] = $student->phone_father;
                    }
                    if ($student->phone_mother && $student->phone_mother !== '010--') {
                        $parentPhones[] = $student->phone_mother;
                    }
                });

            $uniqueParentPhones = array_values(array_unique($parentPhones));
            if (empty($uniqueParentPhones)) {
                return;
            }

            $fcmService = app(FcmService::class);
            $title = "📢 새 공지사항";
            $body = $notice->title;

            foreach ($uniqueParentPhones as $parentPhone) {
                $fcmService->sendToParent(
                    $parentPhone,
                    $title,
                    $body,
                    [
                        'type' => 'notice',
                        'title' => $title,
                        'body' => $body,
                        'notice_id' => (string) $notice->id,
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::error('공지 알림 전송 중 오류: ' . $e->getMessage(), [
                'notice_id' => $this->record->id ?? null,
            ]);
        }
    }
}
