<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\AttendanceLog;

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
        // dd($user);
        if (
            strlen($phoneNumbers) === 11
            && substr($phoneNumbers, 0, 3) === '010'
            && $user !== null
            && $user->isStudent()
        ) {
            // 여기에 실제 출석체크 로직을 구현하세요
            // 예: 데이터베이스에 출석 기록 저장
            $attendanceLog = AttendanceLog::where('student_id', $user->userable->id)
                ->where('attendance_date', now()->format('Y-m-d'))
                ->first();
            if ($attendanceLog) {
                $attendanceLog->check_out_time = now()->format('H:i:s');
                $attendanceLog->save();
            } else {
                AttendanceLog::create([
                    'student_id' => $user->userable->id,
                    'attendance_date' => now()->format('Y-m-d'),
                    'check_in_time' => now()->format('H:i:s')
                ]);
            }

            $key_word = $attendanceLog ? '하원' : '출석';
            $this->message = $user->name . '님의 ' . $key_word . '이 확인되었습니다.';
            $this->messageType = 'success';

            // 3초 후 메시지 초기화
            $this->dispatch('clearMessageAfterDelay');
        } else {
            $this->message = '올바른 휴대전화번호를 입력해주세요.';
            $this->messageType = 'error';
        }
    }
}
