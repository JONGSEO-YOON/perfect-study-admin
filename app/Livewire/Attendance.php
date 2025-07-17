<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\AttendanceLog;
use App\Models\WeeklyTestReport;

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
            // dd($user->userable->classrooms);
            $classroom_id = null;
            $attendance_type = '정규';
            $currentTime = now();
            $currentDay = strtolower($currentTime->format('D')); // mon, tue, wed, thu, fri, sat, sun
            $currentTimeStr = $currentTime->format('H:i');

            foreach ($user->userable->classrooms as $classroom) {
                $timetable = $classroom->timetable;

                // 현재 요일의 수업시간이 있는지 확인
                if (isset($timetable[$currentDay])) {
                    $schedule = $timetable[$currentDay];
                    $startTime = $schedule['start'];
                    $endTime = $schedule['end'];

                    // 수업시간 전 30분부터 후 30분까지 유효
                    $validStartTime = $this->subtractMinutes($startTime, 30);
                    $validEndTime = $this->addMinutes($endTime, 30);

                    // 현재 시간이 유효한 수업시간 범위에 있는지 확인
                    if ($this->isTimeBetween($currentTimeStr, $validStartTime, $validEndTime)) {
                        $classroom_id = $classroom->id;
                        break;
                    }
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

            // 3초 후 메시지 초기화
            $this->dispatch('clearMessageAfterDelay');
        } else {
            $this->message = '올바른 휴대전화번호를 입력해주세요.';
            $this->messageType = 'error';
        }
    }

    /**
     * 시간에서 분을 빼는 메서드
     */
    private function subtractMinutes($time, $minutes)
    {
        $timeObj = \DateTime::createFromFormat('H:i', $time);
        $timeObj->sub(new \DateInterval("PT{$minutes}M"));
        return $timeObj->format('H:i');
    }

    /**
     * 시간에 분을 더하는 메서드
     */
    private function addMinutes($time, $minutes)
    {
        $timeObj = \DateTime::createFromFormat('H:i', $time);
        $timeObj->add(new \DateInterval("PT{$minutes}M"));
        return $timeObj->format('H:i');
    }

    /**
     * 주어진 시간이 두 시간 사이에 있는지 확인하는 메서드
     */
    private function isTimeBetween($time, $start, $end)
    {
        $timeObj = \DateTime::createFromFormat('H:i', $time);
        $startObj = \DateTime::createFromFormat('H:i', $start);
        $endObj = \DateTime::createFromFormat('H:i', $end);

        return $timeObj >= $startObj && $timeObj <= $endObj;
    }
}
