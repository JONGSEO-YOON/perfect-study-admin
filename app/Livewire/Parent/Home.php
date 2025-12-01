<?php

namespace App\Livewire\Parent;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Student;
use App\Models\AttendanceLog;
use App\Models\WeeklyTestReport;
use App\Models\Notice;
use App\Models\StudentNotice;
use Livewire\Attributes\Layout;

class Home extends Component
{
    public $student;
    public $attendances = [];
    public $notices = [];
    public $studentNotices = [];
    public $weeklyReport; // 추가: 오늘 주차 성적표

    public function mount()
    {
        $parent_phone = session('parent_phone');
        $this->student = Student::where('phone_father', $parent_phone)
            ->orWhere('phone_mother', $parent_phone)
            ->first();

        // AttendanceLog 모델의 메서드 사용
        $this->attendances = AttendanceLog::getTodayAttendanceForStudent($this->student->id);

        $this->notices = Notice::whereJsonContains('target_groups', '학부모')
            ->orWhereJsonContains('target_groups', '"학부모"')
            ->orderBy('pinned_at', 'desc')
            ->get();

        $this->loadStudentNotices();

        // 오늘 날짜 기준 주차 성적표 가져오기
        $this->loadWeeklyReport();
    }

    #[On('change-student')]
    public function setStudent($id)
    {
        $this->student = Student::find($id);
        $this->attendances = AttendanceLog::getTodayAttendanceForStudent($this->student->id);

        $this->notices = Notice::whereJsonContains('target_groups', '학부모')
            ->orWhereJsonContains('target_groups', '"학부모"')
            ->orderBy('pinned_at', 'desc')
            ->get();

        $this->loadStudentNotices();

        // 학생 변경 시 성적표도 다시 로드
        $this->loadWeeklyReport();
    }

    protected function loadStudentNotices()
    {
        if (!$this->student) {
            $this->studentNotices = [];
            return;
        }

        $this->studentNotices = StudentNotice::where('student_id', $this->student->id)
            ->orderBy('pinned_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // 오늘 날짜 기준 주차 성적표 로드
    protected function loadWeeklyReport()
    {
        if (!$this->student) {
            $this->weeklyReport = null;
            return;
        }

        $classroomId = $this->student->classrooms->first()?->id ?? null;
        if (!$classroomId) {
            $this->weeklyReport = null;
            return;
        }

        $now = now();
        $startOfWeek = $now->startOfWeek(1)->format('Y-m-d');
        $endOfWeek = $now->endOfWeek(7)->format('Y-m-d');
        $dateRange = "{$startOfWeek}/{$endOfWeek}";

        $this->weeklyReport = WeeklyTestReport::getFormattedWeeklyReport(
            $this->student,
            $dateRange,
            $dateRange,
            $classroomId
        )->first(); // 첫 번째 항목만 가져오기 (오늘 주차)
    }

    public function enableNotifications()
    {
        // JavaScript에서 FCM 초기화를 시작하도록 이벤트 발생
        $this->dispatch('enable-fcm');
    }

    public function fcmEnabled()
    {
        // FCM 활성화 성공 시 호출되는 메서드
        session()->flash('success', '알림이 활성화되었습니다!');
    }

    public function fcmError($message)
    {
        // FCM 활성화 실패 시 호출되는 메서드
        session()->flash('error', '알림 활성화 중 오류가 발생했습니다: ' . $message);
    }

    public function logout()
    {
        // 서버 세션 초기화
        session()->forget('parent_phone');
        session()->save();

        // 로컬스토리지 정리 이벤트 발생
        $this->dispatch('clear-local-storage');

        // 로그인 페이지로 리다이렉트
        return $this->redirect('/parent');
    }

    #[Layout('layouts.parent')]
    public function render()
    {
        return view('livewire.parent.home');
    }
}
