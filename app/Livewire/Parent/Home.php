<?php

namespace App\Livewire\Parent;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Student;
use App\Models\AttendanceLog;
use App\Models\WeeklyTestReport;
use App\Models\Notice;
use Livewire\Attributes\Layout;

class Home extends Component
{
    public $student;

    public $attendances = [];

    public $notices = [];

    public function mount()
    {
        $parent_phone = session('parent_phone');
        $this->student = Student::where('phone_father', $parent_phone)
            ->orWhere('phone_mother', $parent_phone)
            ->first();

        // AttendanceLog와 WeeklyTestReport 모델의 메서드 사용
        $attendanceLogData = AttendanceLog::getTodayAttendanceForStudent($this->student->id);
        $weeklyTestReportData = WeeklyTestReport::getTodayAttendanceReportForStudent($this->student->id);

        // 두 데이터를 합치고 시간순으로 정렬
        $this->attendances = collect(array_merge($attendanceLogData, $weeklyTestReportData))
            ->sortBy('time')
            ->values()
            ->all();

        $this->notices = Notice::whereJsonContains('target_groups', '학부모')
            ->orWhereJsonContains('target_groups', '"학부모"')
            ->orderBy('pinned_at', 'desc')
            ->get();
    }

    #[On('change-student')]
    public function setStudent($id)
    {
        $this->student = Student::find($id);
        $attendanceLogData = AttendanceLog::getTodayAttendanceForStudent($this->student->id);
        $weeklyTestReportData = WeeklyTestReport::getTodayAttendanceReportForStudent($this->student->id);

        // 두 데이터를 합치고 시간순으로 정렬
        $this->attendances = collect(array_merge($attendanceLogData, $weeklyTestReportData))
            ->sortBy('time')
            ->values()
            ->all();

        $this->notices = Notice::whereJsonContains('target_groups', '학부모')
            ->orWhereJsonContains('target_groups', '"학부모"')
            ->orderBy('pinned_at', 'desc')
            ->get();
    }

    #[Layout('layouts.parent')]
    public function render()
    {
        return view('livewire.parent.home');
    }
}
