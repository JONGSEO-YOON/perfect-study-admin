<?php

namespace App\Livewire;

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

class ReportCard extends Component implements HasForms
{
  use InteractsWithForms;

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
    return view('livewire.report-card');
  }

  public function form(Form $form): Form
  {
    return $form
      ->statePath('data')
      ->schema([
        Grid::make(4)
          ->schema([
            DatePicker::make('date')
              ->label('날짜'),
            Select::make('classroom_id')
              ->label('반')
              ->disabled()
              ->options([
                0 => 'A반',
              ])
              ->default(0),
          ]),
        Tabs::make('Tabs')
          ->activeTab(1)
          ->columnSpanFull()
          ->schema([
            Tab::make('Tab 4')
              ->label('주간 학습표')
              ->schema([
                ViewField::make('view')
                  ->view('livewire.report-card-tab3'),
              ]),
            Tab::make('Tab 1')
              ->label('오답 유형분석표')
              ->schema([
                ViewField::make('view')
                  ->view('livewire.report-card-tab1'),
              ]),
            Tab::make('Tab 2')
              ->label('오답 유형분석표 (고3)')
              ->schema([
                ViewField::make('view')
                  ->view('livewire.report-card-tab1-type2'),
              ]),
            Tab::make('Tab 3')
              ->label('오답 문풀 문석표')
              ->schema([
                ViewField::make('view')
                  ->view('livewire.report-card-tab2'),
              ]),
          ])
      ]);
  }
}
