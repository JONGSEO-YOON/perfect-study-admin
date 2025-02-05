<?php

namespace App\Filament\Resources\QuestionCategoryResource\RelationManagers;

use App\Models\QuestionCategory;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Mohamedsabil83\FilamentFormsTinyeditor\Components\TinyEditor;

class ChildrenRelationManager extends RelationManager
{
    protected static string $relationship = 'children';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                ToggleButtons::make('type')
                    ->inline()
                    ->options([
                        'scope' => '단원',
                        'question_type' => '문제 유형',
                    ])
                    ->live()
                    ->visible(fn($record) => !$record?->id)
                    ->label('종류')
                    ->grouped()
                    ->required()
                    ->columnSpanFull()
                    ->default('scope'),
                TinyEditor::make('name')
                    ->label('이름')
                    ->required()
                    ->columnSpanFull(),
                Select::make('order')
                    ->label('순서')
                    ->required()
                    ->visible(fn($record) => $record?->id)
                    ->options(function () {
                        return range(1, 50);
                    })
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('order', 'asc')
            ->recordTitleAttribute('name')
            ->heading('하위 문제 유형표')
            ->columns([
                ViewColumn::make('content')
                    ->view('filament.components.columns.question-category')
                    ->label('이름'),
            ])
            ->paginated(false)
            ->recordUrl(fn($record) => $record->type === 'scope' ? '/admin/question-categories/' . $record->getKey() : '')
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalHeading('하위 문제 유형 추가')
                    ->createAnother(false)
                    ->modalSubmitActionLabel('추가')
                    ->modalWidth('md')
                    ->icon('heroicon-m-plus-circle')
                    ->label('유형 추가하기')
                    ->action(function ($data, $record) {
                        $record = $this->getOwnerRecord();
                        QuestionCategory::createWithParent($data, $record);
                        Notification::make()
                            ->success()
                            ->title('하위 문제 유형이 추가되었습니다.')
                            ->send();
                    }),
            ])
            ->emptyStateHeading('하위 문제 유형이 없습니다.')
            ->emptyStateDescription('하위 문제 유형을 추가해보세요.')
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('하위 문제 유형 수정')
                    ->modalWidth('md')
                    ->action(function ($data, $record) {
                        $record->updateWithOrder($data);
                        Notification::make()
                            ->success()
                            ->title('하위 문제 유형이 수정 되었습니다.')
                            ->send();
                    }),
                Tables\Actions\Action::make('move')
                    ->modalHeading('문제 유형 이동')
                    ->modalWidth('md')
                    ->label('이동')
                    ->icon('heroicon-m-folder-arrow-down')
                    ->form([
                        Select::make('parent_id')
                            ->label('단원 선택')
                            ->searchable()
                            ->allowHtml()
                            ->extraAttributes([
                                'class' => 'question-category-select',
                            ])
                            ->options(function ($record) {
                                // 현재 카테고리의 모든 하위 카테고리 ID들을 조회
                                $descendantIds = $record->descendants()
                                    ->pluck('question_categories.id')
                                    ->push($record->id) // 자기 자신도 제외
                                    ->toArray();

                                // 자기 자신과 하위 카테고리를 제외한 scope 타입 카테고리들만 조회
                                return QuestionCategory::query()
                                    ->where('type', 'scope')
                                    ->whereNotIn('id', $descendantIds)
                                    ->get()
                                    ->mapWithKeys(fn($category) => [$category->getKey() => $category->full_path]);
                            })
                            ->required()
                    ])
                    ->action(function ($data, $record) {
                        $parent = QuestionCategory::find($data['parent_id']);
                        $record->updateParent($parent);
                        Notification::make()
                            ->success()
                            ->title('문제 유형이 이동되었습니다.')
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->modalHeading('하위 문제 유형 삭제')
                    ->modalDescription('문제 유형 삭제 시, 하위 문제 유형 및 문제들도 함께 삭제됩니다.')
            ]);
    }
}
