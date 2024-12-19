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
    if (!$this->classroomId) {
      $this->wrongReports = collect();
      return;
    }
    $this->wrongReports = WrongAnswerTestSheet::getFormattedReport(
      $this->student,
      $this->dateFrom,
      $this->dateUntil,
      $this->classroomId
    );
  }
}
