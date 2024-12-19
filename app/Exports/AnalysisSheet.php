<?php

namespace App\Exports;

use App\Models\TestSheet;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class AnalysisSheet implements FromView, WithTitle
{
  protected array $data;

  public function __construct(array $data)
  {
    $this->data = $data;
  }

  public function title(): string
  {
    return '오답 유형분석표';
  }

  public function view(): View
  {
    $reports = TestSheet::getFormattedAnalysisReport(
      $this->data['student'],
      $this->data['date_from'],
      $this->data['date_until'],
      $this->data['classroom_id']
    );

    return view('exports.analysis-sheet', [
      'reports' => $reports,
    ]);
  }
}
