<?php

namespace App\Exports;

use App\Models\WeeklyTestReport;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use Maatwebsite\Excel\Concerns\WithTitle;

use PhpOffice\PhpSpreadsheet\Style\Style;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;


class WeeklyStudySheet implements FromView, WithTitle, ShouldAutoSize
{
  protected array $data;

  public function __construct(array $data)
  {
    $this->data = $data;
  }

  public function title(): string
  {
    return '주간 학습표';
  }

  public function view(): View
  {
    $weeklyReports = WeeklyTestReport::getFormattedWeeklyReport(
      $this->data['student'],
      $this->data['date_from'],
      $this->data['date_until'],
      $this->data['classroom_id']
    );

    return view('exports.weekly-study-sheet', [
      'weeklyReports' => $weeklyReports,
      'student' => $this->data['student'],
    ]);
  }
}
