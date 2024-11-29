<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Filament\Resources\QuestionResource\RelationManagers;
use App\Models\Question;
use Faker\Provider\ar_EG\Text;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
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
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
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

    public static function handleCreate($data)
    {
        $choices = $data['choices'] ?? [];
        unset($data['questionCategory']);
        unset($data['choices_count']);
        unset($data['choices']);
        $question = Question::create([
            ...$data
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
                    ViewField::make('question_type_id')
                        ->label('문제 유형')
                        ->view('filament.components.forms.question-type')
                        ->live()
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
                        ->columnSpanFull(),
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
                                $set('choices_count', 4);
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
                        ])->label('선택지 개수'),
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
                                                ->required()
                                                ->label('내용')
                                                ->placeholder('선택지를 입력하세요.')
                                                ->visible(fn(Get $get) => $get($prefix . '.display_type') === 'content')
                                                ->columnSpanFull(),
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
                        ->columnSpanFull(),
                    FileUpload::make('explanation_video_url')
                        ->label('해설 영상')
                        ->placeholder('해설 영상 업로드')
                        ->previewable(true)
                        ->downloadable(true)
                        ->columnSpanFull(),
                ]),
            TagsInput::make('tags')
                ->label('태그')
                ->separator(',')
                ->default([])
                ->placeholder('태그를 입력하세요.')
                ->columnSpanFull(),
            Toggle::make('is_wrong_note')
                ->label('오답용')
                ->columnSpanFull(),

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
            ->emptyStateHeading('문제가 없습니다.')
            ->columns([
                //
                TextColumn::make('id')
                    ->label('No')
                    ->rowIndex(),
                TextColumn::make('questionType.name')
                    ->searchable()
                    ->sortable()
                    ->label('문제 유형'),
                TextColumn::make('level')
                    ->label('레벨')
                    ->sortable(),
                TextColumn::make('content2')
                    ->state(true)
                    ->html()
                    ->formatStateUsing(function ($record) {
                        if ($record->question_display_type === 'image') {
                            return "<img src='/storage/{$record->image_path}' alt='문제 이미지' style='max-width: 300px; max-height: 300px;'>";
                        } else {
                            return $record->content;
                        }
                    })
                    ->label('문제'),
                TextColumn::make('created_at')
                    ->date('Y-m-d')
                    ->sortable()
                    ->label('생성일')
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Filter::make('questionType')
                    ->form([
                        // ViewField::make('question_type_ids')
                        //     ->label('문제 유형')
                        //     ->view('filament.components.forms.question-type', [
                        //         'multiple' => true,
                        //     ])
                        //     ->reactive()
                        //     ->live()
                        //     ->columnSpanFull(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['question_type_ids'] ?? null,
                            fn(Builder $query, $questionTypeIds) => $query->whereIn('question_type_id', $questionTypeIds)
                        );
                    })

                    ->columnSpanFull()
            ], FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('문제 수정')
                    ->modalWidth('2xl'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
