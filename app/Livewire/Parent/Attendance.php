<?php

namespace App\Livewire\Parent;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Student;
use App\Models\AttendanceLog;
use Livewire\Attributes\Layout;

class Attendance extends Component
{
    public $student;

    public $startDate;
    public $endDate;

    public $attendances = [];

    public $showMemoModal = false;
    public $selectedMemo = '';
    public $selectedDate = '';

    public function mount()
    {
        $parent_phone = session('parent_phone');
        $this->student = Student::where('phone_father', $parent_phone)
            ->orWhere('phone_mother', $parent_phone)
            ->first();

        // 기본 날짜 설정: 오늘부터 한 달 이후까지
        $this->startDate = now()->format('Y-m-d');
        $this->endDate = now()->addMonth()->format('Y-m-d');

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

    public function showMemo($memo, $date)
    {
        $this->selectedMemo = $memo;
        $this->selectedDate = $date;
        $this->showMemoModal = true;
    }

    public function closeMemoModal()
    {
        $this->showMemoModal = false;
        $this->selectedMemo = '';
        $this->selectedDate = '';
    }

    #[Layout('layouts.parent')]
    public function render()
    {
        return view('livewire.parent.attendance');
    }
}
