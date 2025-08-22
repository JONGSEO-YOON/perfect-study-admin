<?php

namespace App\Livewire\Parent;

use Livewire\Component;
use App\Models\WrongAnswerTestSheet;

class ReportTab4 extends Component
{
    public $weekKey;
    public $student;
    public $classroomId;
    public $dateFrom = null;
    public $dateUntil = null;
    public $wrongReports = [];

    public function mount($weekKey, $student, $classroomId)
    {
        $this->weekKey = $weekKey;
        $this->student = $student;
        $this->classroomId = $classroomId;

        $this->parseDateRange();
        $this->loadWrongReports();
    }

    private function parseDateRange()
    {
        if ($this->weekKey) {
            $dates = explode(' ~ ', $this->weekKey);
            if (count($dates) >= 2) {
                // WrongAnswerTestSheet::getFormattedReport는 "startDate/endDate" 형태를 기대함
                $this->dateFrom = $dates[0] . '/' . $dates[1];
                $this->dateUntil = $dates[0] . '/' . $dates[1];
            }
        }
    }

    public function loadWrongReports()
    {
        if (!$this->classroomId) {
            $this->wrongReports = collect();
            return;
        }
        // dd($this->dateUntil);
        $this->wrongReports = WrongAnswerTestSheet::getFormattedReport(
            $this->student,
            $this->dateFrom,
            $this->dateUntil,
            $this->classroomId
        );
    }

    public function render()
    {
        return view('livewire.parent.report-tab4');
    }
}
