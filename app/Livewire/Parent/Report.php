<?php

namespace App\Livewire\Parent;

use Carbon\Carbon;
use App\Models\Student;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\WeeklyTestReport;
use Livewire\Attributes\On;

class Report extends Component
{
    public $dateFrom;
    public $dateUntil;
    public $classroomId;
    public $student;
    public $weekOptions;
    public $weeklyReports;
    public $comments;
    public $classroomOptions = [];

    public function mount()
    {
        $parent_phone = session('parent_phone');
        $academyId = session('parent_academy_id');
        $students = Student::where(function ($q) use ($parent_phone) {
            $q->where('phone_father', $parent_phone)
              ->orWhere('phone_mother', $parent_phone);
        })->get();
        $this->student = ($academyId ? $students->firstWhere('academy_id', $academyId) : null)
            ?: $students->first();
        $now = now();
        $startOfWeek = $now->startOfWeek(1)->format('Y-m-d');
        $endOfWeek = $now->endOfWeek(7)->format('Y-m-d');
        $this->dateFrom = "{$startOfWeek}/{$endOfWeek}";
        $this->dateUntil = "{$startOfWeek}/{$endOfWeek}";
        
        // 학생이 등록된 모든 클래스 옵션 생성
        $this->classroomOptions = $this->getClassroomOptions();
        $this->classroomId = $this->student->classrooms->first()?->id ?? null;
        
        $this->weekOptions = $this->getWeekOptions();
        $this->weeklyReports = $this->getWeeklyReports();
        $this->initializeComments();
    }


    public function updatedDateFrom()
    {

        $this->weeklyReports = $this->getWeeklyReports();
        $this->initializeComments();
    }

    public function updatedDateUntil()
    {
        $this->weeklyReports = $this->getWeeklyReports();
        $this->initializeComments();
    }

    public function updatedClassroomId()
    {
        $this->weeklyReports = $this->getWeeklyReports();
        $this->initializeComments();
    }

    public function getWeekOptions()
    {
        $options = [];
        $currentYear = now()->year;

        for ($year = $currentYear - 1; $year <= $currentYear; $year++) {
            $lastWeek = Carbon::parse("{$year}-12-31")->weeksInYear();

            for ($week = 1; $week <= $lastWeek; $week++) {
                $date = Carbon::parse("{$year}-01-01")->setISODate($year, $week);
                $month = $date->format('n');
                $weekOfMonth = $date->weekOfMonth;

                $startDate = $date->format('Y-m-d');
                $endDate = $date->endOfWeek(7)->format('Y-m-d');
                $key = "{$startDate}/{$endDate}";

                $options[$key] = "{$year}년 {$month}월 {$weekOfMonth}주차 ({$startDate} ~ {$endDate})";
            }
        }

        return $options;
    }

    public function getClassroomOptions()
    {
        if (!$this->student) {
            return [];
        }
        
        $options = [];
        foreach ($this->student->classrooms as $classroom) {
            $options[$classroom->id] = $classroom->name;
        }
        
        return $options;
    }

    protected function getWeeklyReports()
    {
        if (!$this->classroomId) {
            return collect();
        }
        return WeeklyTestReport::getFormattedWeeklyReport(
            $this->student,
            $this->dateFrom,
            $this->dateUntil,
            $this->classroomId
        );
    }

    protected function initializeComments()
    {
        foreach ($this->weeklyReports as $report) {
            $key = "{$report['year']}-{$report['week']}";
            $commentReport = $this->getWeeklyCommentReport($report['year'], $report['week']);
            
            // 학부모에게는 status가 'sent'인 코멘트만 표시
            if (isset($commentReport->report['status']) && $commentReport->report['status'] === 'sent') {
                $this->comments[$key] = $commentReport->report['comment'] ?? '';
            } else {
                $this->comments[$key] = '';
            }
        }
    }

    protected function getWeeklyCommentReport(int $year, int $week): WeeklyTestReport
    {
        return WeeklyTestReport::firstOrNew([
            'student_id' => $this->student->id,
            'classroom_id' => $this->classroomId,
            'year' => $year,
            'week' => $week,
            'type' => 'comment'
        ]);
    }

    #[On('change-student')]
    public function setStudent($id)
    {
        $this->student = Student::find($id);
        $this->weeklyReports = $this->getWeeklyReports();
        $this->initializeComments();
    }

    #[Layout('layouts.parent')]
    public function render()
    {
        return view('livewire.parent.report');
    }
}
