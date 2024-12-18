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

class ReportCardTab2 extends Component implements HasActions, HasForms
{
  use InteractsWithActions, InteractsWithForms;

  public $student = null;
  public $classroomId = null;
  public $dateFrom = null;
  public $dateUntil = null;
  public $wrongReports = [];

  public function mount()
  {
    $this->loadWrongReports();
  }

  public function render()
  {
    return view('livewire.report-card-tab2');
  }

  #[On('reportFormChange')]
  public function onReportFormChange($data)
  {
    $this->dateFrom = $data['date_from'];
    $this->dateUntil = $data['date_until'];
    $this->classroomId = $data['classroom_id'];
    $this->loadWrongReports();
  }

  public function loadWrongReports()
  {
    if (!$this->student || !$this->dateFrom || !$this->dateUntil || !$this->classroomId) {
      return;
    }

    // 날짜 파싱
    $startDate = explode('/', $this->dateFrom)[0];
    $endDate = explode('/', $this->dateUntil)[1];
    $startCarbon = Carbon::parse($startDate);
    $endCarbon = Carbon::parse($endDate);

    // 1. 원본 테스트 시트 조회 (오답 테스트가 아닌 것들)
    $originalTestSheets = TestSheet::query()
      ->originals()
      ->whereBetween('start_date', [$startCarbon, $endCarbon])
      ->where('status', 'completed')
      ->orderBy('start_date', 'asc')
      ->orderBy('id', 'asc')
      ->get();

    $wrongReports = [];

    foreach ($originalTestSheets as $testSheet) {
      // classroom 체크
      $representativeClassroom = $testSheet->getRepresentativeClassroom($this->student);
      if (!$representativeClassroom || $representativeClassroom->id != $this->classroomId) {
        continue;
      }

      // 2차 오답 테스트 조회
      $secondRetryTest = WrongAnswerTestSheet::where('original_test_sheet_id', $testSheet->id)
        ->where('user_id', $this->student->user->id)
        ->where('retry_count', 2)
        ->with('testSheet')
        ->first();
      // dd($representativeClassroom->id, $this->classroomId);

      if ($secondRetryTest && $secondRetryTest->testSheet && $secondRetryTest->testSheet->report) {
        $wrongReports[] = [
          'date' => $testSheet->start_date->format('n월j일'),
          'name' => $testSheet->name,
          'report' => collect($secondRetryTest->testSheet->report)
            ->sortBy('original_seq')
            ->values()
            ->toArray()
        ];
      }
    }

    $this->wrongReports = $wrongReports;
  }
}
