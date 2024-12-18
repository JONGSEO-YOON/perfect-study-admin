<?php

namespace App\Livewire;

use App\Models\Student;
use App\Models\TestSheet;
use App\Models\WrongAnswerTestSheet;
use Carbon\Carbon;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Collection;

class ReportCardTab1Type2 extends Component implements HasActions, HasForms
{
  use InteractsWithActions, InteractsWithForms;

  public $student = null;
  public $classroomId = null;
  public $dateFrom = null;
  public $dateUntil = null;
  public $reports = [];

  public function mount() {}

  public function render()
  {
    if ($this->student && $this->classroomId && $this->dateFrom && $this->dateUntil) {
      $this->loadReports();
    }

    return view('livewire.report-card-tab1-type2');
  }

  #[On('reportFormChange')]
  public function onReportFormChange($data)
  {
    $this->dateFrom = $data['date_from'];
    $this->dateUntil = $data['date_until'];
    $this->classroomId = $data['classroom_id'];
  }

  private function loadReports()
  {
    $startDate = explode('/', $this->dateFrom)[0];
    $endDate = explode('/', $this->dateUntil)[1];
    $startCarbon = Carbon::parse($startDate);
    $endCarbon = Carbon::parse($endDate);

    // 조건에 맞는 테스트 시트 조회
    $testSheets = TestSheet::query()
      ->where('status', 'completed')
      ->originals()
      ->whereHas('answers', function ($query) {
        $query->where('user_id', $this->student->user->id);
      })
      ->whereBetween('start_date', [$startCarbon, $endCarbon])
      ->orderBy('start_date', 'asc')
      ->get()
      ->filter(function ($testSheet) {
        $classroom = $testSheet->getRepresentativeClassroom($this->student);
        return $classroom && $classroom->id == $this->classroomId;
      });

    // 테스트 시트별로 리포트 데이터 구성
    $this->reports = $testSheets->map(function ($testSheet) {
      $report = collect($testSheet->report)
        ->firstWhere('student_id', $this->student->id);

      if (!$report || !isset($report['score_analysis'])) return null;

      $weekDate = Carbon::parse($testSheet->start_date);
      $week_label = sprintf(
        '%d년 %d월 %d주차',
        $weekDate->format('y'),
        $weekDate->format('n'),
        floor(($weekDate->format('d') - 1) / 7) + 1
      );

      return [
        'date' => $week_label,
        'name' => $weekDate->format('m월 d일') . ' ' . $testSheet->name,
        'class_name' => $report['classroom_name'],
        'type' => in_array('숙제', $testSheet->tags ?? []) ? '숙제' : '일일테스트',
        'score_data' => $this->transformScoreData($report['score_analysis'])
      ];
    })->filter()->values();
  }

  private function transformScoreData($scoreAnalysis)
  {
    $result = [];

    foreach ($scoreAnalysis as $major => $majorData) {
      $majorItems = [];

      foreach ($majorData['types'] as $type => $typeData) {
        $scoreItems = [];

        // 모든 점수에 대해 (2,3,4점)
        foreach ($typeData['scores'] as $scoreData) {
          $score = $scoreData['score'];
          $levels = $scoreData['levels'];

          // 각 레벨별 점수를 배열에 저장
          for ($level = 1; $level <= 5; $level++) {
            $scoreItems[$score][$level] = $levels[$level]['total_earned'] ?? 0;
          }
        }

        $majorItems[] = [
          'type' => $type,
          'scores' => $scoreItems
        ];
      }

      $result[] = [
        'major' => $major,
        'items' => $majorItems
      ];
    }

    return $result;
  }
}
