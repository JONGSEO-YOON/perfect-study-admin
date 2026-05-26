<?php

namespace App\Filament\Resources\StudentNoticeResource\Pages;

use App\Filament\Resources\StudentNoticeResource;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentNotice;
use App\Services\FcmService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Log;

class ListStudentNotices extends ListRecords
{
    protected static string $resource = StudentNoticeResource::class;

    protected static ?string $title = '학생 공지';

    public function getBreadcrumb(): ?string
    {
        return null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-m-plus-circle')
                ->label('공지 추가하기')
                ->modalHeading('공지 추가하기')
                ->visible(
                    fn() => auth()->user()->isRoleAbove('general', true) || !auth()->user()->userable instanceof \App\Models\Teacher
                )
                ->modalWidth('4xl')
                ->createAnother(false)
                ->modalSubmitActionLabel('저장')
                ->after(function (StudentNotice $record) {
                    $this->sendStudentNoticePushToParents($record);
                }),
        ];
    }

    protected function sendStudentNoticePushToParents(StudentNotice $notice): void
    {
        try {
            // 대상 학생 결정
            $studentQuery = Student::query();
            if ($notice->academy_id) {
                $studentQuery->where('academy_id', $notice->academy_id);
            }
            if ($notice->student_id) {
                $studentQuery->where('id', $notice->student_id);
            } elseif ($notice->classroom_id) {
                $studentQuery->whereHas('classrooms', function ($q) use ($notice) {
                    $q->where('classrooms.id', $notice->classroom_id);
                });
            }

            $parentPhones = [];
            $studentQuery
                ->select('id', 'phone_father', 'phone_mother', 'academy_id')
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
            $title = "📢 새 공지";
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
                        'student_notice_id' => (string) $notice->id,
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::error('학생 공지 알림 전송 중 오류: ' . $e->getMessage(), [
                'student_notice_id' => $notice->id ?? null,
            ]);
        }
    }
}
