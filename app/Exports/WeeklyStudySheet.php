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

    $cleanMathML = function ($html) {
      // DOMDocument 생성
      $dom = new \DOMDocument();

      // HTML 파싱 시 오류 무시
      libxml_use_internal_errors(true);

      // UTF-8 인코딩 명시 및 HTML5 문서로 파싱
      $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
      libxml_clear_errors();

      // xmlns 속성이 올바르게 설정되었는지 확인
      $xpath = new \DOMXPath($dom);
      $mathNodes = $xpath->query('//math');

      foreach ($mathNodes as $mathNode) {
        // 네임스페이스 속성이 올바른지 확인
        if (
          !$mathNode->hasAttribute('xmlns') ||
          $mathNode->getAttribute('xmlns') !== 'http://www.w3.org/1998/Math/MathML'
        ) {
          $mathNode->setAttribute('xmlns', 'http://www.w3.org/1998/Math/MathML');
        }

        // 필요에 따라 추가 정리 작업
      }

      // 결과 반환
      return $dom->saveHTML($dom->documentElement);
    };

    return view('exports.weekly-study-sheet', [
      'weeklyReports' => $weeklyReports,
      'student' => $this->data['student'],
      'cleanMathML' => $cleanMathML,
    ]);
  }
}
