<?php

namespace App\Livewire;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Actions\Action as ActionsAction;
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

class ReportCard extends Component implements HasForms, HasActions
{
  use InteractsWithForms;
  use InteractsWithActions;

  public $student = null;
  public $classroomId = null;

  public $data = [
    'date' => null,
    'date_from' => null,
    'date_until' => null,
    'classroom_id' => 0,
  ];

  public function mount()
  {
    $this->data['date'] = now()->format('Y-m-d');
    $now = now();
    $startOfWeek = $now->startOfWeek(1)->format('Y-m-d');
    $endOfWeek = $now->endOfWeek(7)->format('Y-m-d');
    $this->data['date_from'] = "{$startOfWeek}/{$endOfWeek}";
    $this->data['date_until'] = "{$startOfWeek}/{$endOfWeek}";
    $this->data['classroom_id'] = $this->classroomId ?? $this->student->classrooms->first()?->id ?? null;
  }

  public function render()
  {
    return view('livewire.report-card');
  }

  public static function getWeekOptions()
  {
    $options = [];
    $currentYear = now()->year;

    for ($year = $currentYear - 1; $year <= $currentYear; $year++) {
      $lastWeek = Carbon::parse("{$year}-12-31")->weeksInYear();

      for ($week = 1; $week <= $lastWeek; $week++) {
        $date = Carbon::parse("{$year}-01-01")->setISODate($year, $week);
        $month = $date->format('n');
        $weekOfMonth = $date->weekOfMonth;

        $startDate = $date->format('Y-m-d');
        $endDate = $date->endOfWeek(7)->format('Y-m-d');
        $key = "{$startDate}/{$endDate}";

        $options[$key] = "{$year}년 {$month}월 {$weekOfMonth}주차 ({$startDate} ~ {$endDate})";
      }
    }

    return $options;
  }

  public function form(Form $form): Form
  {
    return $form
      ->statePath('data')
      ->schema([
        Grid::make(4)
          ->schema([
            Select::make('date_from')
              ->label('조회 시작 기간')
              ->live()
              ->options(static::getWeekOptions())
              ->afterStateUpdated(function ($get, $livewire) {
                $livewire->dispatch('reportFormChange', [
                  ...$this->data,
                ]);
              }),

            Select::make('date_until')
              ->label('조회 종료 기간')
              ->live()
              ->options(static::getWeekOptions())
              ->afterStateUpdated(function ($get, $livewire) {
                $livewire->dispatch('reportFormChange', [
                  ...$this->data,
                ]);
              }),

            Select::make('classroom_id')
              ->live()
              ->label('반')
              ->options(function () {
                return $this->student->classrooms->pluck('name', 'id')->toArray();
              })
              ->afterStateUpdated(function ($get, $livewire) {
                $livewire->dispatch('reportFormChange', [
                  ...$this->data,
                ]);
              }),
          ]),
        Tabs::make('Tabs')
          ->activeTab(1)
          ->columnSpanFull()
          ->schema([
            Tab::make('Tab 4')
              ->label('주간 학습표')
              ->schema([
                ViewField::make('tab3')
                  ->viewData([
                    'student' => $this->student,
                    'date_from' => $this->data['date_from'],
                    'date_until' => $this->data['date_until'],
                    'classroom_id' => $this->data['classroom_id'],
                  ])
                  ->view('livewire.report-card-tab3-wrapper'),
              ]),
            Tab::make('Tab 1')
              ->label('오답 유형분석표')
              ->visible(fn() => $this->student->gradeSystem->display_name != '고3')
              ->schema([
                ViewField::make('tab1')
                  ->viewData([
                    'student' => $this->student,
                    'date_from' => $this->data['date_from'],
                    'date_until' => $this->data['date_until'],
                    'classroom_id' => $this->data['classroom_id'],
                  ])
                  ->view('livewire.report-card-tab1-wrapper'),
              ]),
            Tab::make('Tab 2')
              ->label('오답 유형분석표 (고3)')
              ->visible(fn() => $this->student->gradeSystem->display_name == '고3')
              ->schema([
                ViewField::make('tab1-2')
                  ->viewData([
                    'student' => $this->student,
                    'date_from' => $this->data['date_from'],
                    'date_until' => $this->data['date_until'],
                    'classroom_id' => $this->data['classroom_id'],
                  ])
                  ->view('livewire.report-card-tab1-type2-wrapper'),
              ]),
            Tab::make('Tab 3')
              ->label('오답 문풀 분석표')
              ->schema([
                ViewField::make('tab2')
                  ->viewData([
                    'student' => $this->student,
                    'date_from' => $this->data['date_from'],
                    'date_until' => $this->data['date_until'],
                    'classroom_id' => $this->data['classroom_id'],
                  ])
                  ->view('livewire.report-card-tab2-wrapper'),
              ]),
          ])
      ]);
  }


  public function sendAction(): Action
  {
    return Action::make('send')
      ->label('PDF로 출력')
      ->icon('heroicon-m-document')
      ->requiresConfirmation();
    // ->action(fn() => dd('adsf'));
  }
  public function printAction(): Action
  {
    return Action::make('print')
      ->label('엑셀 출력')
      ->icon('heroicon-m-table-cells')
      ->requiresConfirmation();
    // ->action(fn() => dd('adsf'));
  }

  public function initAction() {}
}
