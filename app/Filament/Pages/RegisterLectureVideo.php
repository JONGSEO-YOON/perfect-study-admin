<?php

namespace App\Filament\Pages;

use App\Filament\Resources\LectureResource;
use App\Models\Lecture;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Pages\Page;

class RegisterLectureVideo extends Page
{
  protected static ?string $slug = '/lectures/{id}/register-video';

  protected static string $view = 'filament.pages.lecture-register-video';

  protected static bool $shouldRegisterNavigation = false;

  public $id = null;

  public $arguments = [];

  public function mount($id)
  {
    $this->id = $id;
  }


  public function viewHistoryAction(): Action
  {
    return Action::make('viewHistory')
      ->action(function ($arguments) {
        $this->arguments = $arguments;
        $this->replaceMountedAction('viewHistoryInternal');
      });
  }

  public function viewHistoryInternalAction(): Action
  {
    return Action::make('viewHistoryInternal')
      ->modalHeading($this->arguments['name'] . ' 시청 내역')
      ->modalWidth('xl')
      ->modalCancelActionLabel('닫기')
      ->modalContent(fn() => view('filament.components.modals.lecture-view-history', [
        'lectureVideoId' => $this->arguments['id'],
        'lecture' => Lecture::find($this->id),
      ]))
      ->modalSubmitAction(false);
  }

  public function addNewItemAction(): Action
  {
    return Action::make('addNewItem')
      ->action(function ($arguments) {
        $this->arguments = $arguments;
        $this->replaceMountedAction('addNewItemInternal');
      });
  }

  public function addNewItemInternalAction(): Action
  {
    return Action::make('addNewItemInternal')
      ->label('추가')
      ->modalWidth('lg')
      ->modalSubmitActionLabel('추가')
      ->fillForm([
        'type' => $this->arguments['type'],
      ])
      ->form([
        Grid::make(1)
          ->schema([
            Hidden::make('type'),
            TextInput::make('name')
              ->label('이름'),
            FileUpload::make('video')
              ->visible(fn(Get $get) => $get('type') === 'video')
              ->label('강의 영상')
              // ->acceptedFileTypes(['video/*, video/mp4'])
              ->placeholder('클릭하거나 파일을 드래그하여 업로드')
              ->previewable(true)
              ->downloadable(true)
              ->columnSpanFull(),
            FileUpload::make('attachments')
              ->visible(fn(Get $get) => $get('type') === 'video')
              ->label('강의 추가 자료')
              ->multiple(true)
              ->placeholder('클릭하거나 파일을 드래그하여 업로드')
              ->preserveFilenames()
              ->previewable(false)
              ->downloadable(true)
              ->columnSpanFull()
          ]),
      ])
      ->action(function ($arguments, $data) {
        $this->dispatch('add-new-item', array_merge($arguments, $data));
      });
  }



  public function editItemAction(): Action
  {
    return Action::make('editItem')
      ->action(function ($arguments) {
        $this->arguments = $arguments;
        $this->replaceMountedAction('editItemInternal');
      });
  }

  public function editItemInternalAction(): Action
  {
    return Action::make('editItemInternal')
      ->label('수정')
      ->modalWidth('md')
      ->modalSubmitActionLabel('수정')
      ->fillForm([
        'name' => $this->arguments['name'],
        'order' => $this->arguments['order'],
        'type' => $this->arguments['type'],
        'video' => $this->arguments['video'] ?? null,
        'attachments' => $this->arguments['attachments'] ?? [],
      ])
      ->form([
        Grid::make(3)
          ->schema([
            Hidden::make('type'),
            Select::make('order')
              ->label('순서')
              //options is 1 to 30
              ->options(fn() => collect(range(1, 30))->mapWithKeys(fn($value) => [$value => $value])),
            TextInput::make('name')
              ->label('이름')
              ->columnSpan(2),
            FileUpload::make('video')
              ->visible(fn(Get $get) => $get('type') === 'video')
              ->label('강의 영상')
              // ->acceptedFileTypes(['video/mp4', 'video/*',  'video/aac', 'video/ogg', 'video/webm'])
              ->placeholder('클릭하거나 파일을 드래그하여 업로드')
              ->previewable(true)
              ->downloadable(true)
              ->columnSpanFull(),
            FileUpload::make('attachments')
              ->visible(fn(Get $get) => $get('type') === 'video')
              ->label('강의 추가 자료')
              ->multiple(true)
              ->placeholder('클릭하거나 파일을 드래그하여 업로드')
              ->preserveFilenames()
              ->previewable(false)
              ->downloadable(true)
              ->columnSpanFull(),

          ]),
      ])
      ->extraModalFooterActions(fn(Action $action): array => [
        $action->makeModalSubmitAction('deleteItem', arguments: ['delete' => true])
          ->label('삭제')
          ->color('danger'),
      ])
      ->action(function ($arguments, $data) {
        if ($arguments['delete'] ?? false) {
          $this->dispatch('edit-item', [
            'delete' => true,
          ]);
        } else {
          $this->dispatch('edit-item', array_merge($data));
        }
      });
  }
}
