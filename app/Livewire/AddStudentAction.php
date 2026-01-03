<?php

namespace App\Livewire;

use App\Filament\Resources\StudentResource;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
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

class AddStudentAction extends Component implements HasActions, HasForms
{
  use InteractsWithForms;
  use InteractsWithActions;

  public $data = [
    'date' => null,
    'classroom_id' => 0,
  ];

  public function mount()
  {
    $this->data['date'] = now()->format('Y-m-d');
  }

  public function render()
  {
    return view('livewire.add-student-action');
  }

  public function addStudentAction(): Action
  {
    return CreateAction::make('addStudent')
      ->label('신규 학생 등록')
      ->modalHeading('신규 학생 등록')
      ->icon('heroicon-m-user-plus')
      ->size('sm')
      ->link()
      ->model(StudentResource::getModel())
      ->form(StudentResource::_form(true))
      ->modalWidth('xl')
      ->modalCancelActionLabel('닫기')
      ->modalSubmitActionLabel('등록')
      ->createAnother(false)
      ->after(function ($record) {
        $this->dispatch('studentAdded', $record);
      });
  }


  public function initAction() {}
}
