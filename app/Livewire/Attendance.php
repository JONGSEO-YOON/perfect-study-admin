<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\AttendanceLog;
use App\Models\WeeklyTestReport;
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

    public function checkAttendance()
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
            // 여기에 실제 출석체크 로직을 구현하세요
            // 예: 데이터베이스에 출석 기록 저장
            $classroom_id = null;
            $attendance_type = '정규';
            $currentTime = now();
            $currentDay = strtolower($currentTime->format('D')); // mon, tue, wed, thu, fri, sat, sun

            foreach ($user->userable->classrooms as $classroom) {
                $timetable = $classroom->timetable;

                // 오늘 해당 요일에 스케줄이 있는지만 확인
                if (isset($timetable[$currentDay])) {
                    $classroom_id = $classroom->id;
                    break;
                }
            }

            if ($classroom_id) {
                $report = WeeklyTestReport::firstOrNew([
                    'student_id' => $user->userable->id,
                    'classroom_id' => $classroom_id,
                    'year' => now()->year,
                    'week' => now()->isoWeek(),
                    'type' => 'attendance'
                ]);
                $reportData = $report->report ?? [];

                $exists = false;
                foreach ($reportData as $key => $attendance) {
                    if ($attendance['date'] === now()->format('Y-m-d')) {

                        $reportData[$key]['check_out_time'] = now()->format('H:i:s');
                        $exists = true;
                        break;
                    }
                }

                if (!$exists) {
                    $reportData[] = [
                        'date' => now()->format('Y-m-d'),
                        'attendance' => '정규',
                        'check_in_time' => now()->format('H:i:s'),
                        'check_out_time' => null,
                        'memo1' => null,
                        'memo2' => null
                    ];
                }

                usort($reportData, fn($a, $b) => strcmp($a['date'], $b['date']));
                $report->report = array_values($reportData);
                $report->save();
                $key_word = $exists ? '하원' : '출석';
            } else {
                $attendance_type = '보충';
                $attendanceLog = AttendanceLog::where('student_id', $user->userable->id)
                    ->where('attendance_date', now()->format('Y-m-d'))
                    ->latest()
                    ->first();
                if ($attendanceLog && $attendanceLog->check_out_time === null) {

                    $attendanceLog->check_out_time = now()->format('H:i:s');
                    $attendanceLog->save();
                    $key_word = '하원';
                } else {
                    AttendanceLog::create([
                        'student_id' => $user->userable->id,
                        'attendance_date' => now()->format('Y-m-d'),
                        'check_in_time' => now()->format('H:i:s')
                    ]);
                    $key_word = '출석';
                }
            }


            $this->message = $user->name . '님의 ' . $key_word . '(' . $attendance_type . ')이 확인되었습니다.';
            $this->messageType = 'success';
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

            if (empty($parentPhones)) {
                \Log::info("학생 {$user->name}의 부모 연락처가 없습니다.");
                return;
            }

            // 알림 내용 구성
            $title = "📍 {$user->name} 학생 {$keyWord} 알림";
            $body = "{$user->name} 학생이 {$currentTime}에 {$keyWord}하였습니다. ({$attendanceType})";

            // 각 부모에게 알림 전송
            Log::info('Parent phones to send FCM: ', $parentPhones);
            foreach ($parentPhones as $parentPhone) {
                Log::info('Sending FCM to: ' . $parentPhone);
                $results = $fcmService->sendToParent(
                    $parentPhone,
                    $title,
                    $body,
                    [
                        'type' => 'attendance',
                        'student_name' => $user->name,
                        'student_id' => (string) $student->id,
                        'action' => $keyWord,
                        'attendance_type' => $attendanceType,
                        'time' => $currentTime,
                        'date' => now()->format('Y-m-d'),
                        'timestamp' => now()->toISOString()
                    ]
                );

                $successCount = count(array_filter($results));
                if ($successCount > 0) {
                    \Log::info("출석 알림 전송 성공: {$parentPhone} - {$user->name} {$keyWord}");
                } else {
                    \Log::warning("출석 알림 전송 실패: {$parentPhone} - {$user->name} {$keyWord}");
                }
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
