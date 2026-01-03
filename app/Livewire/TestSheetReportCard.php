<?php

namespace App\Livewire;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;

class TestSheetReportCard extends Component implements HasActions, HasForms
{
  use InteractsWithForms;
  use InteractsWithActions;

  public $testsheet;

  public $arguments = [];

  public function mount() {}

  public function render()
  {
    return view('livewire.test-sheet-report-card');
  }

  public function detailAction(): Action
  {
    return Action::make('detail')
      ->action(function ($arguments) {
        $this->arguments = $arguments['student'];
        $this->replaceMountedAction('detailInternal');
      });
  }

  public function detailInternalAction(): Action
  {
    return Action::make('detailInternal')
      ->modalHeading('상세보기')
      ->modalCancelActionLabel('닫기')
      ->modalSubmitAction(false)
      ->modalContent(fn() => view('livewire.test-sheet-report-card-detail', [
        'testsheet' => $this->testsheet,
        'questionTypes' => $this->arguments['question_types'] ?? [],
      ]));
  }


  public function initAction() {}
}
