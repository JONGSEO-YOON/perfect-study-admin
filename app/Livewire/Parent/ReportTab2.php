<?php

namespace App\Livewire\Parent;

use App\Models\TestSheet;
use Livewire\Component;

class ReportTab2 extends Component
{
    public $weekKey;
    public $student;
    public $classroomId;
    public $dateFrom = null;
    public $dateUntil = null;
    public $reports = [];
    public $reportKey = 'detailed_hierarchy';

    public function mount($weekKey, $student, $classroomId)
    {
        $this->weekKey = $weekKey;
        $this->student = $student;
        $this->classroomId = $classroomId;

        // weekKey를 dateFrom, dateUntil로 변환
        $this->parseDateRange();
        $this->loadReports();
    }

    private function parseDateRange()
    {
        if ($this->weekKey) {
            $dates = explode(' ~ ', $this->weekKey);
            if (count($dates) >= 2) {
                // TestSheet::getFormattedAnalysisReport는 "startDate/endDate" 형태를 기대함
                $this->dateFrom = $dates[0] . '/' . $dates[1];
                $this->dateUntil = $dates[0] . '/' . $dates[1];
            }
        }
    }

    private function loadReports()
    {
        if (!$this->classroomId || !$this->student || !$this->dateFrom || !$this->dateUntil) {
            $this->reports = collect();
            return;
        }

        $this->reports = TestSheet::getFormattedAnalysisReport(
            $this->student,
            $this->dateFrom,
            $this->dateUntil,
            $this->classroomId
        );
    }

    public function render()
    {
        return view('livewire.parent.report-tab2');
    }
}
