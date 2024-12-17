<?php

namespace App\Livewire;

use App\Models\Student;
use App\Models\WrongAnswerTestSheet;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Attributes\On;
use Livewire\Component;

class TestSheetWrongReportCard extends Component implements HasActions, HasForms
{
  use InteractsWithForms;
  use InteractsWithActions;

  public $testsheet;

  public $student;

  public $report = [];

  public function mount() {}

  public function render()
  {
    return view('livewire.test-sheet-wrong-report-card');
  }

  #[On('studentChanged')]
  public function onStudentChanged($studentId)
  {
    // 2차 오답테스트 조회
    if (!$studentId) {
      return;
    }
    $this->student = Student::findOrFail($studentId);
    $userId = $this->student->user->id;
    $secondRetryTest = WrongAnswerTestSheet::where('original_test_sheet_id', $this->testsheet->id)
      ->where('user_id', $userId)
      ->where('retry_count', 2)
      ->with('testSheet')
      ->first();

    if ($secondRetryTest && $secondRetryTest->testSheet) {
      $this->report = collect($secondRetryTest->testSheet->report)
        ->sortBy('original_seq')
        ->values()
        ->toArray();
    } else {
      $this->report = [];
    }
  }

  public function initAction() {}
}
