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
        $user = User::where('phone', $this->phone)->first();

        if (
            strlen($phoneNumbers) === 11
            && substr($phoneNumbers, 0, 3) === '010'
            && $user !== null
            && $user->isStudent()
        ) {
            // 정규 수업 여부 확인
            $currentTime = now();
            $currentDay = strtolower($currentTime->format('D')); // mon, tue, wed, thu, fri, sat, sun

            $hasRegularClass = false;
            foreach ($user->userable->classrooms as $classroom) {
                $timetable = $classroom->timetable;
                // 오늘 해당 요일에 스케줄이 있는지 확인
                if (isset($timetable[$currentDay])) {
                    $hasRegularClass = true;
                    break;
                }
            }

            // 보충 수업 여부 결정 (정규 수업이 없으면 보충)
            $is_supplementary = !$hasRegularClass;

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
                } else {
                    $updateData['check_out_time'] = now();
                }


                $existingLog->update($updateData);
                $attendanceLog = $existingLog;
            } else {
                // 새 기록 생성
                $attendanceData = [
                    'student_id' => $user->userable->id,
                    'is_late' => false,
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
        } else {
            $this->message = '올바른 휴대전화번호를 입력해주세요.';
            $this->messageType = 'error';
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
            \Log::info("Parent phones to send FCM: ", $parentPhones);
            \Log::info("Unique parent phones: ", $uniqueParentPhones);

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
                        // 'student_name' => $user->name,
                        // 'student_id' => (string) $student->id,
                        // 'action' => $keyWord,
                        // 'attendance_type' => $attendanceType,
                        // 'time' => $currentTime,
                        // 'date' => now()->format('Y-m-d'),
                        // 'timestamp' => now()->toISOString()
                    ]
                );
                // $payload = json_encode([
                //     'parent_phone' => $parentPhone,
                //     'title' => $title,
                //     'body' => $body,
                //     'data' => [
                //         'type' => 'attendance',
                //         'title' => $title,
                //         'body' => $body,
                //     ]
                // ]);

                // $ch = curl_init();
                // curl_setopt($ch, CURLOPT_URL, 'http://localhost/api/send-push-notification');
                // curl_setopt($ch, CURLOPT_POST, 1);
                // curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                // curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                // curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                // $result = curl_exec($ch);
                // curl_close($ch);
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
