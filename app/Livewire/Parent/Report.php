<?php

namespace App\Livewire\Parent;

use Carbon\Carbon;
use App\Models\Student;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\WeeklyTestReport;

class Report extends Component
{
    public $dateFrom;
    public $dateUntil;
    public $classroomId;
    public $student;
    public $weekOptions;
    public $weeklyReports;
    public $comments;

    public function mount()
    {
        $parent_phone = session('parent_phone');
        $this->student = Student::where('phone_father', $parent_phone)
            ->orWhere('phone_mother', $parent_phone)
            ->first();
        $now = now();
        $startOfWeek = $now->startOfWeek(1)->format('Y-m-d');
        $endOfWeek = $now->endOfWeek(7)->format('Y-m-d');
        $this->dateFrom = "{$startOfWeek}/{$endOfWeek}";
        $this->dateUntil = "{$startOfWeek}/{$endOfWeek}";
        $this->classroomId = $this->student->classrooms->first()?->id ?? null;
        $this->weekOptions = $this->getWeekOptions();
        $this->weeklyReports = $this->getWeeklyReports();
        $this->initializeComments();
    }


    public function updatedDateFrom($value)
    {

        $this->weeklyReports = $this->getWeeklyReports();
        $this->initializeComments();
    }

    public function updatedDateUntil($value)
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
            $this->comments[$key] = $commentReport->report['comment'] ?? '';
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

    #[Layout('layouts.parent')]
    public function render()
    {
        return view('livewire.parent.report');
    }
}
