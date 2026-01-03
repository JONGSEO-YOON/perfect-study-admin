<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StudentReportCardsExport implements WithMultipleSheets
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        $student = $this->data['student'];

        // 고3인지 여부에 따라 다른 분석표 시트 사용
        // $analysisSheet = new AnalysisSheet($this->data);
        $analysisSheet = $student->gradeSystem->display_name == '고3'
            ? new HighschoolAnalysisSheet($this->data)
            : new AnalysisSheet($this->data);

        return [
            new WeeklyStudySheet($this->data),
            $analysisSheet,
            new WrongAnswerSheet($this->data)
        ];
    }
}
