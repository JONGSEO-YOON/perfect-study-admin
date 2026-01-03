<?php

namespace App\Livewire;

use App\Models\TestSheet;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Attributes\On;
use Livewire\Component;

class ReportCardTab1 extends Component implements HasActions, HasForms
{
  use InteractsWithActions, InteractsWithForms;

  public $student = null;
  public $classroomId = null;
  public $dateFrom = null;
  public $dateUntil = null;
  public $reports = [];

  public $reportKey = 'hierarchy';

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
    if (!$this->classroomId) {
      $this->reports = collect();
      return;
    }
    $this->reports = TestSheet::getFormattedAnalysisReport(
      $this->student,
      $this->dateFrom,
      $this->dateUntil,
      $this->classroomId
    );
  }
}
