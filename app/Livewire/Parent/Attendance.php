<?php

namespace App\Livewire\Parent;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Student;
use App\Models\AttendanceLog;
use App\Models\WeeklyTestReport;
use Carbon\Carbon;
use Livewire\Attributes\Layout;

class Attendance extends Component
{
    public $student;

    public $startDate;
    public $endDate;

    public $attendances = [];

    public function mount()
    {
        $this->student = Student::find(session('students')->first());

        // 기본 날짜 설정: 오늘로부터 한 달 이전까지
        $this->endDate = now()->format('Y-m-d');
        $this->startDate = now()->subMonth()->format('Y-m-d');

        $this->loadAttendances();
    }

    #[On('change-student')]
    public function setStudent($id)
    {
        $this->student = Student::find($id);
        $this->loadAttendances();
    }

    public function updatedStartDate()
    {
        $this->loadAttendances();
    }

    public function updatedEndDate()
    {
        $this->loadAttendances();
    }

    public function loadAttendances()
    {
        if (!$this->student || !$this->startDate || !$this->endDate) {
            $this->attendances = [];
            return;
        }

        // AttendanceLog 모델의 메서드 사용
        $this->attendances = AttendanceLog::getAttendancesByDateRange(
            $this->student->id,
            $this->startDate,
            $this->endDate
        );
    }

    #[Layout('layouts.parent')]
    public function render()
    {
        return view('livewire.parent.attendance');
    }
}
