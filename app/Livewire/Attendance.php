<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\AttendanceLog;
use App\Services\FcmService;
use Illuminate\Support\Facades\Log;

class Attendance extends Component
{
    public $phone = '010-';
    public $message = '';
    public $messageType = 'info';

    #[Layout("layouts.public")]
    public function render()
    {
        return view('livewire.attendance');
    }

    public function checkAttendance($type = 'in')
    {
        // 출석체크 로직 구현
        $phoneNumbers = preg_replace('/[^0-9]/', '', $this->phone);

        // 1. 휴대전화 형식 검증
        if (strlen($phoneNumbers) !== 11 || substr($phoneNumbers, 0, 3) !== '010') {
            $this->message = '휴대전화번호 형식이 올바르지 않습니다. (010-XXXX-XXXX)';
            $this->messageType = 'error';
            return;
        }

        // 2. DB에 저장된 번호 형식이 다양할 수 있으므로(010-1234-5678, 01012345678 등)
        //    숫자만 추출하여 비교
        $user = User::whereRaw("REPLACE(REPLACE(REPLACE(phone, '-', ''), ' ', ''), '.', '') = ?", [$phoneNumbers])
            ->first();

        // 3. 학생 존재 여부
        if (!$user || !$user->isStudent()) {
            $this->message = '등록된 학생을 찾을 수 없습니다. 휴대전화번호를 확인해주세요.';
            $this->messageType = 'error';
            return;
        }

        // 4. 학원 일치 여부
        $currentAcademy = app()->has('current_academy') ? app('current_academy') : null;
        if ($currentAcademy && $user->academy_id !== $currentAcademy->id) {
            $userAcademyName = $user->academy?->name ?? '본인';
            $this->message = "소속이 다른 학원입니다 ({$userAcademyName}). 본인 학원의 출결 페이지로 접속해주세요.";
            $this->messageType = 'error';
            return;
        }

        // 5. 출석 처리 (위 모든 검증 통과)
        {
            // 정규 수업 여부 확인
            $currentTime = now();
            $currentDay = strtolower($currentTime->format('D')); // mon, tue, wed, thu, fri, sat, sun

            $hasRegularClass = false;
            $classStartTime = null;
            foreach ($user->userable->classrooms as $classroom) {
                $timetable = $classroom->timetable;
                // 오늘 해당 요일에 스케줄이 있는지 확인
                if (isset($timetable[$currentDay])) {
                    $hasRegularClass = true;
                    $startTime = $timetable[$currentDay]['start'] ?? null;
                    // 가장 이른 수업 시작 시간 기준으로 지각 판단
                    if ($startTime && ($classStartTime === null || $startTime < $classStartTime)) {
                        $classStartTime = $startTime;
                    }
                }
            }

            // 보충 수업 여부 결정 (정규 수업이 없으면 보충)
            $is_supplementary = !$hasRegularClass;

            // 지각 여부 판단: 정규 수업 시작 시간보다 늦으면 지각
            $is_late = false;
            if ($hasRegularClass && $classStartTime && $type === 'in') {
                $is_late = $currentTime->format('H:i') > $classStartTime;
            }

            // 오늘 출석 기록 확인 (학생 ID로 조회)
            $today = now()->format('Y-m-d');
            $existingLog = AttendanceLog::where('student_id', $user->userable->id)
                ->whereDate('created_at', $today)
                ->first();

            if ($existingLog) {
                // 기존 기록 업데이트
                $updateData = [];

                if ($type === 'in') {
                    $updateData['check_in_time'] = now();
                    $updateData['is_late'] = $is_late;
                } else {
                    $updateData['check_out_time'] = now();
                }

                $existingLog->update($updateData);
                $attendanceLog = $existingLog;
            } else {
                // 새 기록 생성
                $attendanceData = [
                    'student_id' => $user->userable->id,
                    'is_late' => $is_late,
                    'is_supplementary' => $is_supplementary,
                ];

                // 타입에 따라 check_in_time 또는 check_out_time 설정
                if ($type === 'in') {
                    $attendanceData['check_in_time'] = now();
                } else {
                    $attendanceData['check_out_time'] = now();
                }

                $attendanceLog = AttendanceLog::create($attendanceData);
            }

            $key_word = $type === 'in' ? '등원' : '하원';
            $attendance_type = $is_supplementary ? '보충' : '정규';

            $this->message = '(' . $user->name . ')님의(' . $attendance_type  . $key_word . ')이 확인되었습니다.';
            $this->messageType = $type === 'in' ? 'success-in' : 'success-out';
            $this->phone = '010-';

            // 부모에게 푸시 알림 전송
            $this->sendAttendanceNotificationToParent($user, $key_word, $attendance_type);

            // 3초 후 메시지 초기화
            $this->dispatch('clearMessageAfterDelay');
        }
    }

    /**
     * 부모에게 출석/하원 푸시 알림 전송
     */
    private function sendAttendanceNotificationToParent($user, $keyWord, $attendanceType)
    {
        try {
            $fcmService = new FcmService();
            $student = $user->userable; // Student 모델
            $currentTime = now()->format('H:i');

            // 부모 전화번호 찾기 (아버지, 어머니 전화번호)
            $parentPhones = [];
            if ($student->phone_father !== '010--') {
                $parentPhones[] = $student->phone_father;
            }
            if ($student->phone_mother !== '010--') {
                $parentPhones[] = $student->phone_mother;
            }

            // 중복 제거 및 로깅
            $uniqueParentPhones = array_unique($parentPhones);

            if (empty($uniqueParentPhones)) {
                \Log::info("학생 {$user->name}의 부모 연락처가 없습니다.");
                return;
            }

            // 알림 내용 구성
            $title = "📍 {$user->name} 학생 {$keyWord} 알림";
            $body = "{$user->name} 학생이 {$currentTime}에 {$keyWord}하였습니다. ({$attendanceType})";

            foreach ($uniqueParentPhones as $parentPhone) {
                $fcmService->sendToParent(
                    $parentPhone,
                    $title,
                    $body,
                    [
                        'type' => 'attendance',
                        'title' => $title,
                        'body' => $body,
                    ]
                );
            }
        } catch (\Exception $e) {
            // 알림 전송 실패해도 출석 처리는 계속 진행
            \Log::error("출석 알림 전송 중 오류: " . $e->getMessage(), [
                'student' => $user->name,
                'action' => $keyWord,
                'error' => $e->getMessage()
            ]);
        }
    }
}
