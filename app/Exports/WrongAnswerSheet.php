<?php


namespace App\Exports;

use App\Models\WrongAnswerTestSheet;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class WrongAnswerSheet implements FromView, WithTitle
{
  protected array $data;

  public function __construct(array $data)
  {
    $this->data = $data;
  }

  public function title(): string
  {
    return '오답 문풀 분석표';
  }

  public function view(): View
  {
    $wrongReports = WrongAnswerTestSheet::getFormattedReport(
      $this->data['student'],
      $this->data['date_from'],
      $this->data['date_until'],
      $this->data['classroom_id']
    );

    return view('exports.wrong-answer-sheet', [
      'wrongReports' => $wrongReports,
    ]);
  }
}
