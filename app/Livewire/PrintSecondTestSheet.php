<?php

namespace App\Livewire;

use App\Exports\WrongAnswerSheet;
use App\Models\Student;
use App\Models\WrongAnswerTestSheet;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Attributes\On;
use Livewire\Component;

class PrintSecondTestSheet extends Component implements HasActions, HasForms, HasTable
{
  use InteractsWithForms;
  use InteractsWithActions;
  use InteractsWithTable;

  public $testsheet;

  public function mount() {}

  public function render()
  {
    return view('livewire.print-second-test-sheet-table');
  }

  public function initAction() {}

  public function table(Table $table): Table
  {
    return $table
      ->query(function () {
        return WrongAnswerTestSheet::where('retry_count', 2)
          ->where('original_test_sheet_id', $this->testsheet->id)
          ->whereHas('user.student.classrooms.teacher.user', function ($query) {
            $query->where('id', auth()->user()->id);
          });
      })
      ->columns([
        TextColumn::make('No')
          ->rowIndex()
          ->sortable(),
        TextColumn::make('testSheet')
          ->formatStateUsing(function ($state, $record) {
            $students = $state->getTargetStudents();
            $student = $students->first();
            return $student?->user?->name;
          })
          ->label('학생'),
        TextColumn::make('testSheetCount')
          ->state(true)
          ->formatStateUsing(function ($record) {
            return count($record->testSheet->questions);
          })
          ->label('출제 문제 수'),
      ])
      ->actions([
        Action::make('print-test-sheet')
          ->label('오답 테스트 출력')
          ->icon('heroicon-m-printer')
          ->url(fn($record) => '/admin/test-sheet/print?test_sheet_id=' . $record->test_sheet_id . '&retry_count=2&student_id=' . $record->testSheet?->getTargetStudents()?->first()?->id)
          ->openUrlInNewTab(),

      ]);
  }
}
