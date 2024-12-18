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

class ReportCardTab1 extends Component implements HasActions, HasForms
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

    return view('livewire.report-card-tab1');
  }

  #[On('reportFormChange')]
  public function onReportFormChange($data)
  {
    $this->dateFrom = $data['date_from'];
    $this->dateUntil = $data['date_until'];
    $this->classroomId = $data['classroom_id'];
    $this->loadReports();
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

      if (!$report) return null;

      $weekDate = Carbon::parse($testSheet->start_date);
      $week_label = sprintf(
        '%d년 %d월 %d주차',
        $weekDate->format('y'),
        $weekDate->format('n'),
        floor(($weekDate->format('d') - 1) / 7) + 1
      );

      $analysisData = [
        'test_sheet_id' => $testSheet->id,
        'date' => $week_label,
        'name' => $weekDate->format('m월 d일') . ' ' . $testSheet->name,
        'class_name' => $report['classroom_name'],
        // 'type' => in_array('숙제', $testSheet->tags ?? []) ? '숙제' : '시험',
        'hierarchy' => $this->transformHierarchyData($report['hierarchical_analysis'] ?? []),
        'personal_level' => $report['level_analysis']['personal'] ?? [],
        'classroom_level' => $report['level_analysis']['classroom'] ?? []
      ];

      return $analysisData;
    })->filter(function ($report) {
      return $report['hierarchy'] && $report['personal_level'] && $report['classroom_level'];
    });
  }

  private function transformHierarchyData($hierarchicalData)
  {
    $result = [];

    foreach ($hierarchicalData as $major => $majorData) {
      foreach ($majorData['sub_categories'] as $middle => $middleData) {
        foreach ($middleData['types'] as $type => $typeData) {
          $levelData = [];
          foreach ($typeData['levels'] as $level => $data) {
            $levelData[$level] = [
              'total' => $data['total'],
              'correct' => $data['correct'],
              'percentage' => $data['percentage']
            ];
          }

          $result[] = [
            'major' => $majorData['name'],
            'middle' => $middleData['name'],
            'type' => $typeData['name'],
            'levels' => $levelData
          ];
        }
      }
    }

    return $result;
  }
}
