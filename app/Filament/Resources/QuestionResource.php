<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Filament\Resources\QuestionResource\RelationManagers;
use App\Models\Question;
use App\Models\QuestionCategory;
use Faker\Provider\ar_EG\Text;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\View;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Mohamedsabil83\FilamentFormsTinyeditor\Components\TinyEditor;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static ?string $navigationLabel = '문제 은행';

    protected static ?string $title = '문제 은행';

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationGroup = '문제 관리';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return auth()->user()->userable instanceof \App\Models\Teacher;
    }

    public static function handleUpdate($data)
    {
        $choices = $data['choices'] ?? [];
        $tags = $data['tags'] ?? [];
        if (is_string($tags)) {
            $tags = explode(',', $tags);
        }
        unset($data['questionCategory']);
        unset($data['choices_count']);
        unset($data['choices']);
        $question = Question::find($data['id']);
        $question->update([
            ...$data,
            'tags' => $tags,
        ]);
        if (
            $data['answer_type'] === 'multiple_choice'
            && $data['choices_display_type'] === 'seperate'
        ) {
            $question->choices()->delete();
            $i = 1;
            foreach ($choices as $choice) {
                $question->choices()->create([
                    'number' => $i++,
                    'content' => $choice['content'] ?? null,
                    'display_type' => $choice['display_type'],
                    'image_path' => $choice['image_path'] ?? null
                ]);
            }
        }
        return $question;
    }

    public static function handleCreate($data)
    {
        $choices = $data['choices'] ?? [];
        $tags = $data['tags'] ?? [];
        if (is_string($tags)) {
            $tags = explode(',', $tags);
        }
        unset($data['questionCategory']);
        unset($data['choices_count']);
        unset($data['choices']);
        $question = Question::create([
            ...$data,
            'tags' => $tags,
        ]);
        if (
            $data['answer_type'] === 'multiple_choice'
            && $data['choices_display_type'] === 'seperate'
        ) {
            $i = 1;
            foreach ($choices as $choice) {
                $question->choices()->create([
                    'number' => $i++,
                    'content' => $choice['content'] ?? null,
                    'display_type' => $choice['display_type'],
                    'image_path' => $choice['image_path'] ?? null
                ]);
            }
        }
        return $question;
    }

    public static function getBreadcrumb(): string
    {
        return '';
    }

    public static function _form()
    {
        return [
            Section::make()
                ->columns(4)
                ->columnSpanFull()
                ->heading('1. 문제 정보')
                ->collapsible(true)
                ->schema([
                    Hidden::make('question_type_id')
                        ->hidden(fn(Get $get) => !$get('is_sub_question'))
                        ->required(),
                    ViewField::make('question_type_id')
                        ->label('문제 유형')
                        ->view('filament.components.forms.question-type')
                        ->live()
                        ->hidden(fn(Get $get) => $get('is_sub_question'))
                        ->required()
                        ->columnSpanFull(),
                    ToggleButtons::make('question_display_type')
                        ->label('문제 표기 방식')
                        ->required()
                        ->inline()
                        ->options([
                            'image' => '이미지',
                            'content' => '직접 입력',
                        ])
                        ->columnSpan(2)
                        ->live()
                        ->default('image'),
                    FileUpload::make('image_path')
                        ->visible(fn(Get $get) => $get('question_display_type') === 'image')
                        ->label('문제 이미지')
                        ->required()
                        ->image()
                        ->placeholder('문제 이미지 업로드')
                        ->previewable(true)
                        ->downloadable(true)
                        ->columnSpanFull(),
                    TinyEditor::make('content')
                        ->visible(fn(Get $get) => $get('question_display_type') === 'content')
                        ->label('문제 내용')
                        ->required()
                        ->placeholder('문제 내용을 입력하세요.')
                        ->columnSpanFull()
                        ->dehydrateStateUsing(fn($state) => fix_mathtype_mfenced($state)),
                    Select::make('level')
                        ->label('레벨')
                        ->required()
                        ->options([
                            1 => '1',
                            2 => '2',
                            3 => '3',
                            4 => '4',
                            5 => '5',
                        ])
                        ->default(1),
                ]),
            Section::make()
                ->columns(4)
                ->columnSpanFull()
                ->heading('2. 정답 정보')
                ->collapsible(true)
                ->schema([
                    ToggleButtons::make('answer_type')
                        ->label('정답 유형')
                        ->required()
                        ->inline()
                        ->options([
                            'multiple_choice' => '객관식',
                            'integer' => '정수형 주관식',
                        ])
                        ->live()
                        ->default('multiple_choice')
                        ->columnSpanFull(),
                    ToggleButtons::make('choices_display_type')
                        ->label('선택지 표기 방식')
                        ->visible(fn(Get $get) => $get('answer_type') === 'multiple_choice')
                        ->required()
                        ->inline()
                        ->options([
                            'in_question' => '문제 안에 포함',
                            'seperate' => '직접 입력',
                        ])
                        ->columnSpan(2)
                        ->live()
                        ->default('in_question')
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            if ($get('choices_display_type') === 'in_question') {
                                $set('choices_count', 0);
                            } else {
                                $set('choices_count', 6);
                            }
                        })
                        ->columnSpanFull(),
                    Select::make('choices_count')
                        ->visible(fn(Get $get) => $get('answer_type') === 'multiple_choice' && $get('choices_display_type') === 'seperate')
                        ->required()
                        ->default(6)
                        ->live()
                        ->reactive()
                        ->options([
                            2 => '2',
                            3 => '3',
                            4 => '4',
                            5 => '5',
                            6 => '6',
                        ])->label('선택지 개수')
                        ->afterStateUpdated(function (Get $get, Set $set, $livewire) {
                            if (isset($livewire->mountedTableActionsData[0]['choices'])) {
                                $choicesCount = $get('choices_count');
                                $currentCount = count($livewire->mountedTableActionsData[0]['choices']);
                                if ($choicesCount > $currentCount) {
                                    for ($i = $currentCount; $i < $choicesCount; $i++) {
                                        $livewire->mountedTableActionsData[0]['choices'][] = [
                                            'content' => '',
                                        ];
                                    }
                                }
                                // if less remove
                                if ($choicesCount < $currentCount) {
                                    $livewire->mountedTableActionsData[0]['choices'] = array_slice($livewire->mountedTableActionsData[0]['choices'], 0, $choicesCount);
                                }
                            }
                        }),
                    Tabs::make('choices')
                        ->visible(fn(Get $get) => $get('answer_type') === 'multiple_choice' && $get('choices_display_type') === 'seperate')
                        ->live()
                        ->reactive()
                        ->schema(function (Get $get) {
                            $choicesCount = $get('choices_count');
                            $schema = [];
                            for ($i = 0; $i < $choicesCount; $i++) {
                                $uuid = Str::uuid();
                                $prefix = 'choices.' . $i;
                                array_push(
                                    $schema,
                                    Tab::make($prefix)
                                        ->label('선택지 ' . ($i + 1))
                                        ->schema([
                                            ToggleButtons::make($prefix . '.display_type')
                                                ->label('표기 방식')
                                                ->required()
                                                ->inline()
                                                ->options([
                                                    'image' => '이미지',
                                                    'content' => '직접 입력',
                                                ])
                                                ->columnSpan(2)
                                                ->live()
                                                ->default('image'),
                                            FileUpload::make($prefix . '.image_path')
                                                ->label('해설 이미지')
                                                ->image()
                                                ->required()
                                                ->placeholder('해설 이미지 업로드')
                                                ->previewable(true)
                                                ->downloadable(true)
                                                ->columnSpanFull()
                                                ->visible(fn(Get $get) => $get($prefix . '.display_type') === 'image'),
                                            TinyEditor::make($prefix . '.content')
                                                // ->required()
                                                ->label('내용')
                                                ->placeholder('선택지를 입력하세요.')
                                                ->visible(fn(Get $get) => $get($prefix . '.display_type') === 'content')
                                                ->columnSpanFull()
                                                ->dehydrateStateUsing(fn($state) => fix_mathtype_mfenced($state)),
                                        ])
                                );
                            }
                            return $schema;
                        })
                        ->columnSpanFull(),
                    TextInput::make('answer')
                        ->label('정답')
                        ->required(),
                ]),
            Section::make()
                ->columns(4)
                ->columnSpanFull()
                ->heading('3. 해설 정보')
                ->collapsible(true)
                ->schema([
                    ToggleButtons::make('explanation_display_type')
                        ->label('해설 표기 방식')
                        ->required()
                        ->inline()
                        ->options([
                            'image' => '이미지',
                            'content' => '직접 입력',
                        ])
                        ->columnSpanFull()
                        ->live()
                        ->default('image'),
                    FileUpload::make('explanation_image_path')
                        ->label('해설 이미지')
                        ->image()
                        ->required()
                        ->visible(fn(Get $get) => $get('explanation_display_type') === 'image')
                        ->placeholder('해설 이미지 업로드')
                        ->previewable(true)
                        ->downloadable(true)
                        ->columnSpanFull(),
                    TinyEditor::make('explanation')
                        ->visible(fn(Get $get) => $get('explanation_display_type') === 'content')
                        ->label('해설 내용')
                        ->required()
                        ->placeholder('해설 내용을 입력하세요.')
                        ->columnSpanFull()
                        ->dehydrateStateUsing(fn($state) => fix_mathtype_mfenced($state)),
                    FileUpload::make('explanation_video_url')
                        ->label('해설 영상')
                        ->placeholder('해설 영상 업로드')
                        ->previewable(true)
                        ->downloadable(true)
                        ->columnSpanFull(),
                ]),
            Select::make('material_id')
                ->label('교재')
                ->searchable()
                ->live()
                ->options(
                    \App\Models\Material::where('type', 'book')
                        ->editable()
                        ->get()->pluck('name', 'id')
                )
                ->visible(fn(Get $get) => !$get('is_sub_question'))
                ->placeholder('교재를 선택하세요.'),
            TextInput::make('seq')
                ->label('문제번호')
                ->required()
                ->numeric()
                ->visible(fn(Get $get) => $get('material_id'))
                ->placeholder('교재를 선택하세요.'),

            Toggle::make('is_public')
                ->columnSpanFull()
                ->inline(false)
                ->visible(fn(Get $get) => !$get('is_sub_question') && !$get('material_id'))
                ->label('문제 공개 (타 강사 공유)'),
            TagsInput::make('tags')
                ->label('태그')
                ->separator(',')
                ->default([])
                ->placeholder('태그를 입력하세요.')
                ->columnSpanFull(),
            Hidden::make('is_sub_question')
                ->dehydrated(false)
                ->default(false),
            Hidden::make('seq')
                ->nullable(),
            Hidden::make('metadata')
                ->nullable()
            // Toggle::make('is_wrong_note')
            //     ->label('오답용')
            //     ->columnSpanFull(),

        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(self::_form());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                return $query->where('parent_question_id', null);
            })
            ->emptyStateHeading('문제가 없습니다.')
            ->columns([
                //
                TextColumn::make('id')
                    ->label('No')
                    ->rowIndex(),
                TextColumn::make('seq')
                    ->label('문제 번호')
                    ->sortable()
                    ->visible(function ($livewire) {
                        return ($livewire->tableFilters['material_id']['material_id'] ?? false);
                    }),
                TextColumn::make('id')
                    ->label('No')
                    ->rowIndex()
                    ->visible(function ($livewire) {
                        return !($livewire->tableFilters['material_id']['material_id'] ?? false);
                    }),
                // TextColumn::make('questionType.name')
                //     ->searchable()
                //     ->sortable()
                //     ->html(),
                ViewColumn::make('questionType.name')
                    ->view('filament.components.columns.question-category-render')
                    ->label('이름')
                    ->sortable(true, function ($query, $direction) {
                        return $query->orderBy('question_type_id', $direction);
                    })
                    ->label('문제 유형'),
                TextColumn::make('level')
                    ->label('레벨')
                    ->sortable(),
                ViewColumn::make('content')
                    ->view('filament.components.columns.question')
                    ->label('문제'),
                // TextColumn::make('content2')
                //     ->state(true)
                //     ->html()
                //     ->formatStateUsing(function ($record) {
                //         if ($record->question_display_type === 'image') {
                //             return "<img src='/storage/{$record->image_path}' alt='문제 이미지' style='max-width: 300px; max-height: 300px;'>";
                //         } else {
                //             return new HtmlString($record->content);
                //         }
                //     })
                //     ->label('문제'),
                TextColumn::make('created_at')
                    ->date('Y-m-d')
                    ->sortable()
                    ->label('생성일')
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Filter::make('material_id')
                    ->form([
                        Hidden::make('material_id'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['material_id'], function ($query, $materialId) {
                                return $query->where('material_id', $materialId);
                            });
                    })
                    ->columnSpanFull(),
                Filter::make('questionType')
                    ->form([
                        Select::make('question_type_id')
                            ->label('유형 선택')
                            ->searchable()
                            ->allowHtml()
                            ->preload(false)
                            ->extraAttributes([
                                'class' => 'question-category-select',
                            ])
                            ->getSearchResultsUsing(function ($search) {
                                return QuestionCategory::query()
                                    ->where('name', 'like', "%{$search}%")
                                    ->get()
                                    ->mapWithKeys(fn($category) => [$category->getKey() => $category->full_path]);
                            })
                            ->options(function () {
                                return QuestionCategory::query()
                                    ->take(10)
                                    ->get()
                                    ->mapWithKeys(fn($category) => [$category->getKey() => $category->full_path]);
                            })
                            ->columnSpanFull()
                    ])
                    ->columnSpan(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['question_type_id'] ?? null,
                            function (Builder $query, $questionTypeId) {
                                $category = QuestionCategory::find($questionTypeId);
                                $questionTypeIds = [$questionTypeId, ...$category->getFlattenedDescendantIds()];
                                // dd($questionTypeIds);
                                return $query->whereIn('question_type_id', $questionTypeIds);
                            }
                        );
                    }),
                Filter::make('seq')
                    ->visible(function ($livewire) {
                        return ($livewire->tableFilters['material_id']['material_id'] ?? false);
                    })
                    ->form([
                        TextInput::make('seq_from')
                            ->numeric()
                            ->label('문제 번호 (시작)'),
                        TextInput::make('seq_to')
                            ->numeric()
                            ->label('문제 번호 (끝)'),

                    ])
                    ->columns(2)
                    ->columnSpan(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['seq_from'] ?? null,
                            function (Builder $query, $seq_from) {
                                return $query->where('seq', '>=', $seq_from);
                            }
                        )->when(
                            $data['seq_to'] ?? null,
                            function (Builder $query, $seq_to) {
                                return $query->where('seq', '<=', $seq_to);
                            }
                        );
                    })

            ], FiltersLayout::AboveContent)
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->fillForm(function ($record) {
                            return [
                                'choices' => $record->choices->map(function ($choice) {
                                    return [
                                        'content' => $choice->content,
                                        'display_type' => $choice->display_type,
                                        'image_path' => $choice->image_path,
                                    ];
                                })->toArray(),
                                'choices_count' => $record->choices->count() > 0 ? $record->choices->count() : 6,
                                ...$record->toArray(),
                            ];
                        })
                        ->using(function ($data, $record) {
                            $data['id'] = $record->id;
                            return self::handleUpdate($data);
                        })
                        ->label(function ($record) {
                            if ($record->is_editable) {
                                return '수정';
                            }
                            return '조회';
                        })
                        ->icon(function ($record) {
                            if ($record->is_editable) {
                                return 'heroicon-m-pencil-square';
                            }
                            return 'heroicon-m-eye';
                        })
                        ->modalSubmitAction(function ($record) {
                            if (!$record->is_editable) {
                                return false;
                            }
                        })
                        ->modalHeading('문제')
                        ->modalWidth('2xl'),
                    Tables\Actions\Action::make('유사 문제 1')
                        ->label('유사 문제 1')
                        ->icon('heroicon-m-pencil-square')
                        ->icon(function ($record) {
                            if ($record->is_editable) {
                                return 'heroicon-m-pencil-square';
                            }
                            return 'heroicon-m-eye';
                        })
                        ->modalSubmitAction(function ($record) {
                            if (!$record->is_editable) {
                                return false;
                            }
                        })
                        ->fillForm(function ($record) {
                            $subQuestion = $record->childQuestions()
                                ->orderBy('id', 'asc')
                                ->first();
                            if ($subQuestion) {
                                return [
                                    'is_sub_question' => true,
                                    'choices' => $subQuestion->choices->map(function ($choice) {
                                        return [
                                            'content' => $choice->content,
                                            'display_type' => $choice->display_type,
                                            'image_path' => $choice->image_path,
                                        ];
                                    })->toArray(),
                                    'choices_count' => $subQuestion->choices->count() > 0  ?  $subQuestion->choices->count() : 6,
                                    ...$subQuestion->toArray(),
                                ];
                            }
                            return [
                                'question_type_id' => $record->question_type_id,
                                'choices' => [],
                                'choices_count' => 6,
                                'question_display_type' => 'image',
                                'answer_type' => 'multiple_choice',
                                'choices_display_type' => 'in_question',
                                'is_sub_question' => true,
                            ];
                        })
                        ->form(self::_form())
                        ->action(function ($record, $data) {
                            $subQuestion = $record->childQuestions()
                                ->orderBy('id', 'asc')
                                ->first();
                            if ($subQuestion) {
                                $data['id'] = $subQuestion->id;
                                self::handleUpdate($data);
                            } else {
                                $data['parent_question_id'] = $record->id;
                                self::handleCreate($data);
                            }
                            Notification::make()
                                ->title('유사 문제가 저장되었습니다')
                                ->success()
                                ->send();
                        })
                        ->modalHeading('유사 문제 1')
                        ->modalWidth('2xl'),
                    Tables\Actions\Action::make('유사 문제 2')
                        ->label('유사 문제 2')
                        ->icon(function ($record) {
                            if ($record->is_editable) {
                                return 'heroicon-m-pencil-square';
                            }
                            return 'heroicon-m-eye';
                        })
                        ->modalSubmitAction(function ($record) {
                            if (!$record->is_editable) {
                                return false;
                            }
                        })
                        ->fillForm(function ($record) {
                            $subQuestion = $record->childQuestions()
                                ->orderBy('id', 'asc')
                                ->skip(1)
                                ->first();
                            if ($subQuestion) {
                                return [
                                    'is_sub_question' => true,
                                    'choices' => $subQuestion->choices->map(function ($choice) {
                                        return [
                                            'content' => $choice->content,
                                            'display_type' => $choice->display_type,
                                            'image_path' => $choice->image_path,
                                        ];
                                    })->toArray(),
                                    'choices_count' => $subQuestion->choices->count() > 0  ?  $subQuestion->choices->count() : 6,
                                    ...$subQuestion->toArray(),
                                ];
                            }
                            return [
                                'question_type_id' => $record->question_type_id,
                                'question_display_type' => 'image',
                                'answer_type' => 'multiple_choice',
                                'choices_display_type' => 'in_question',
                                'choices' => [],
                                'choices_count' => 6,
                                'is_sub_question' => true,
                            ];
                        })
                        ->form(self::_form())
                        ->action(function ($record, $data) {
                            $subQuestion = $record->childQuestions()
                                ->orderBy('id', 'asc')
                                ->skip(1)
                                ->first();
                            if ($subQuestion) {
                                $data['id'] = $subQuestion->id;
                                self::handleUpdate($data);
                            } else {
                                $data['parent_question_id'] = $record->id;
                                self::handleCreate($data);
                            }
                            Notification::make()
                                ->title('유사 문제가 저장되었습니다')
                                ->success()
                                ->send();
                        })
                        ->modalHeading('유사 문제 2')
                        ->modalWidth('2xl'),
                    Tables\Actions\DeleteAction::make()
                        ->visible(fn($record) => $record->is_editable)
                        ->modalHeading('문제 삭제')

                ]),
                // Tables\Actions\EditAction::make()
                //     ->modalHeading('문제 수정')
                //     ->modalWidth('2xl'),
                // Tables\Actions\EditAction::make('유사 문제 1')
                //     ->label('유사 문제 1')
                //     ->modalHeading('문제 수정')
                //     ->modalWidth('2xl'),
                // Tables\Actions\EditAction::make('유사 문제 2')
                //     ->label('유사 문제 2')
                //     ->modalHeading('문제 수정')
                //     ->modalWidth('2xl'),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestions::route('/'),
            // 'create' => Pages\CreateQuestion::route('/create'),
            // 'edit' => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }
}
