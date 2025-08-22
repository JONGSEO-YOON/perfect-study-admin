<?php

namespace App\Livewire\Parent;

use App\Models\Student;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\WeeklyTestReport;

class ReportDetail extends Component
{
    public $weekKey;
    public $student;
    public $classroomId;
    public $weeklyReport;
    public $comments;

    public function mount($weekKey)
    {
        $this->weekKey = urldecode($weekKey);
        
        $parent_phone = session('parent_phone');
        $this->student = Student::where('phone_father', $parent_phone)
            ->orWhere('phone_mother', $parent_phone)
            ->first();
        
        $this->classroomId = $this->student->classrooms->first()?->id ?? null;
        
        $this->loadWeeklyReport();
    }

    protected function loadWeeklyReport()
    {
        if (!$this->classroomId) {
            $this->weeklyReport = null;
            return;
        }

        // week_key 형태: "2024-08-19 ~ 2024-08-25"를 "2024-08-19/2024-08-25" 형태로 변환
        $dates = explode(' ~ ', $this->weekKey);
        if (count($dates) < 2) {
            $this->weeklyReport = null;
            return;
        }
        $formattedWeekKey = $dates[0] . '/' . $dates[1];
        
        $weeklyReports = WeeklyTestReport::getFormattedWeeklyReport(
            $this->student,
            $formattedWeekKey,
            $formattedWeekKey,
            $this->classroomId
        );

        $this->weeklyReport = $weeklyReports->first();

        if ($this->weeklyReport) {
            $key = "{$this->weeklyReport['year']}-{$this->weeklyReport['week']}";
            $commentReport = $this->getWeeklyCommentReport($this->weeklyReport['year'], $this->weeklyReport['week']);
            
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

    #[Layout('layouts.parent')]
    public function render()
    {
        return view('livewire.parent.report-detail');
    }
}