<?php

namespace App\Livewire;

use App\Models\Lecture;
use App\Models\LectureVideoViewHistory;
use App\Models\Student;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class LectureViewHistoryTable extends Component implements HasForms, HasTable
{
  use InteractsWithForms;
  use InteractsWithTable;

  public $data = [
    // 'id' => null,
  ];

  public $lectureVideoId = null;
  public $lecture = null;

  public function mount()
  {
    // sample code to create a record in the lecture_video_view_histories table
    $histories = LectureVideoViewHistory::where('video_id', $this->lectureVideoId)
      ->get();

    if ($histories->count() > 0) {
      return;
    }
  }

  public function render()
  {
    return view('livewire.lecture-view-history-table');
  }



  public function table(Table $table): Table
  {
    return $table
      ->query(LectureVideoViewHistory::query()
        ->where('video_id', $this->lectureVideoId))
      ->columns([
        TextColumn::make('id')
          ->label('No')
          ->rowIndex(),
        TextColumn::make('user.name')
          ->label('학생 이름')
          ->searchable(),
        TextColumn::make('created_at')
          ->date('Y-m-d H:i:s')
          ->label('시청일시'),

      ])
      ->filters([
        // ...
      ])
      ->actions([
        // ...
      ])
      ->bulkActions([
        // ...
      ]);
  }
}
