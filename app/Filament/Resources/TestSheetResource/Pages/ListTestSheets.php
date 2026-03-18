<?php

namespace App\Filament\Resources\TestSheetResource\Pages;

use App\Filament\Resources\TestSheetResource;
use App\Models\Classroom;
use App\Models\GradeSystem;
use App\Models\Student;
use App\Models\TempData;
use App\Models\TestSheet;
use Filament\Actions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\ListRecords;

class ListTestSheets extends ListRecords
{
    protected static string $resource = TestSheetResource::class;

    protected static ?string $title = '문제지 관리';

    public function getBreadcrumb(): ?string
    {
        return null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make('my-test-sheet')
                ->icon('heroicon-m-document-text')
                ->modalHeading('내 문제지 사용하기')
                ->modalWidth('2xl')
                ->createAnother(false)
                ->label('내 문제지')
                ->form([

                    Select::make('my_test_sheet_id')
                        ->label('내 문제지 선택')
                        ->options(function () {
                            $user = auth()->user();
                            return $user->userable->testSheets()
                                ->get()
                                ->mapWithKeys(function ($testSheet) {
                                    return [$testSheet->id => $testSheet->name];
                                });
                        })
                        ->searchable()
                        ->required()
                        ->columnSpanFull(),
                ])
                ->action(function ($data) {
                    $testSheet = TestSheet::find($data['my_test_sheet_id']);

                    redirect('/admin/test-sheets/create/' . $testSheet->temp_data_id . '?test_sheet_id=' . $testSheet->id . '&copy=true');
                })
                ->modalSubmitActionLabel('문제지 선택')
                ->visible(fn() => auth()->user()->role === 'general'),
            Actions\CreateAction::make('create-test-sheet-by-book')
                ->icon('heroicon-m-plus-circle')
                ->modalHeading('교재 문제지 추가하기')
                ->modalWidth('2xl')
                ->createAnother(false)
                ->label('교재 문제지 추가하기')
                ->form([
                    Select::make('material_id')
                        ->label('교재 선택')
                        ->required()
                        ->options(
                            \App\Models\Material::where('type', 'book')->visible()->get()->pluck('name', 'id')
                        )
                        ->live()
                        ->searchable()
                        ->columnStart(1),
                    Grid::make(4)
                        ->schema([
                            TextInput::make('material_range_start')
                                ->label('시작 문제 번호')
                                ->required()
                                ->numeric()
                                ->default(1)
                                ->columnStart(1)
                                ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                    $count = $get('material_range_end') - $state + 1;
                                    if ($count) {
                                        $set('question_count', $count);
                                        // if ($get('question_count') == 25 || $get('question_count') == 50 || $get('question_count') == 75 || $get('question_count') == 100) {
                                        //     $set('question_count_choice', $count);
                                        // } else {
                                        //     $set('question_count_choice', null);
                                        // }
                                    }
                                }),
                            TextInput::make('material_range_end')
                                ->label('끝 문제 번호')
                                ->required()
                                ->numeric()
                                ->default(50)
                                ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                    $count = $state - $get('material_range_start') + 1;
                                    if ($count) {
                                        $set('question_count', $count);
                                        // if ($get('question_count') == 25 || $get('question_count') == 50 || $get('question_count') == 75 || $get('question_count') == 100) {
                                        //     $set('question_count_choice', $count);
                                        // } else {
                                        //     $set('question_count_choice', null);
                                        // }
                                    }
                                }),
                        ]),
                    // Grid::make(4)
                    //     ->schema([
                    //         Radio::make('target_group')
                    //             ->label('출제 대상')
                    //             ->required()
                    //             ->live()
                    //             ->reactive()
                    //             ->options([
                    //                 'grade' => '학년',
                    //                 'level' => '레벨',
                    //                 'classroom' => '교실/반',
                    //                 'student' => '학생',
                    //             ])
                    //             ->default('grade')
                    //             ->columns(4)
                    //             ->columnSpanFull()
                    //     ])->columnSpanFull(),
                    // Grid::make(2)
                    //     ->schema([
                    //         Select::make('target_grades')
                    //             ->label('학년')
                    //             ->multiple()
                    //             ->required()
                    //             ->options(function () {
                    //                 return GradeSystem::query()
                    //                     ->orderBy('sequential_order')
                    //                     ->pluck(
                    //                         'display_name',
                    //                         'id',
                    //                     );
                    //             })
                    //             ->visible(fn(Get $get) => $get('target_group') === 'grade' || $get('target_group') === 'level'),
                    //         Select::make('target_levels')
                    //             ->label('레벨')
                    //             ->multiple()
                    //             ->required()
                    //             ->options([
                    //                 'A' => 'A',
                    //                 'M' => 'M',
                    //                 'S' => 'S',
                    //             ])
                    //             ->visible(fn(Get $get) => $get('target_group') === 'level'),
                    //         Select::make('target_classrooms')
                    //             ->label('반')
                    //             ->multiple()
                    //             ->required()
                    //             ->options(function () {
                    //                 return Classroom::query()
                    //                     ->orderBy('name')
                    //                     ->pluck('name', 'id');
                    //             })
                    //             ->visible(fn(Get $get) => $get('target_group') === 'classroom'),
                    //         Select::make('target_students')
                    //             ->label('학생')
                    //             ->multiple()
                    //             ->required()
                    //             ->options(function () {
                    //                 $classroomIds = Classroom::query()
                    //                     ->orderBy('name')
                    //                     ->pluck('id');
                    //                 return Student::query()
                    //                     ->whereHas('classrooms', function ($q) use ($classroomIds) {
                    //                         $q->whereIn('classrooms.id', $classroomIds);
                    //                     })
                    //                     ->get()
                    //                     ->mapWithKeys(fn($student) => [$student->user->id => $student->user->name]);
                    //             })
                    //             ->visible(fn(Get $get) => $get('target_group') === 'student'),
                    //     ]),
                    Hidden::make('target_group')->default(null),
                    Hidden::make('question_count')
                        ->default(50),
                ])
                ->action(function ($data) {
                    $tempData = TempData::create([
                        'value' => $data,
                    ]);
                    redirect('/admin/test-sheets/create/' . $tempData->id);
                })
                ->modalSubmitActionLabel('문제 선택'),
            Actions\CreateAction::make()
                ->icon('heroicon-m-plus-circle')
                ->modalHeading('문제지 추가하기')
                ->modalWidth('2xl')
                ->createAnother(false)
                ->label('문제지 추가하기')
                ->form([
                    // Grid::make(4)
                    //     ->schema([
                    //         Radio::make('target_group')
                    //             ->label('출제 대상')
                    //             ->required()
                    //             ->live()
                    //             ->reactive()
                    //             ->options([
                    //                 'grade' => '학년',
                    //                 'level' => '레벨',
                    //                 'classroom' => '교실/반',
                    //                 'student' => '학생',
                    //             ])
                    //             ->default('grade')
                    //             ->columns(4)
                    //             ->columnSpanFull()
                    //     ])->columnSpanFull(),
                    // Grid::make(2)
                    //     ->schema([
                    //         Select::make('target_grades')
                    //             ->label('학년')
                    //             ->multiple()
                    //             ->required()
                    //             ->options(function () {
                    //                 return GradeSystem::query()
                    //                     ->orderBy('sequential_order')
                    //                     ->pluck(
                    //                         'display_name',
                    //                         'id',
                    //                     );
                    //             })
                    //             ->visible(fn(Get $get) => $get('target_group') === 'grade' || $get('target_group') === 'level'),
                    //         Select::make('target_levels')
                    //             ->label('레벨')
                    //             ->multiple()
                    //             ->required()
                    //             ->options([
                    //                 'A' => 'A',
                    //                 'M' => 'M',
                    //                 'S' => 'S',
                    //             ])
                    //             ->visible(fn(Get $get) => $get('target_group') === 'level'),
                    //         Select::make('target_classrooms')
                    //             ->label('반')
                    //             ->multiple()
                    //             ->required()
                    //             ->options(function () {
                    //                 return Classroom::query()
                    //                     ->orderBy('name')
                    //                     ->pluck('name', 'id');
                    //             })
                    //             ->visible(fn(Get $get) => $get('target_group') === 'classroom'),
                    //         Select::make('target_students')
                    //             ->label('학생')
                    //             ->multiple()
                    //             ->required()
                    //             ->options(function () {
                    //                 $classroomIds = Classroom::query()
                    //                     ->orderBy('name')
                    //                     ->pluck('id');
                    //                 return Student::query()
                    //                     ->whereHas('classrooms', function ($q) use ($classroomIds) {
                    //                         $q->whereIn('classrooms.id', $classroomIds);
                    //                     })
                    //                     ->get()
                    //                     ->mapWithKeys(fn($student) => [$student->user->id => $student->user->name]);
                    //             })
                    //             ->visible(fn(Get $get) => $get('target_group') === 'student'),
                    //     ]),
                    Hidden::make('target_group')->default(null),
                    ViewField::make('question_type_ids')
                        ->label('문제 유형')
                        ->view('filament.components.forms.question-type', [
                            'multiple' => true,
                        ])
                        ->live()
                        ->required()
                        ->columnSpanFull(),
                    Grid::make(7)
                        ->schema([
                            ToggleButtons::make('question_count_choice')
                                ->dehydrated(false)
                                ->label('문제 수')
                                ->inline()
                                ->options([
                                    25 => 25,
                                    50 => 50,
                                    75 => 75,
                                    100 => 100,
                                ])
                                ->default(50)
                                ->columnSpan(3)
                                ->afterStateUpdated(function (Set $set, $state) {
                                    $set('question_count', $state);
                                })
                                ->live(),
                            TextInput::make('question_count')
                                ->label('직접 입력')
                                ->required()
                                ->integer()
                                ->columnSpan(2)
                                ->default(50)
                                ->afterStateUpdated(function (Set $set, $state) {
                                    if ($state == 25 || $state == 50 || $state == 75 || $state == 100) {
                                        $set('question_count_choice', $state);
                                    } else {
                                        $set('question_count_choice', null);
                                    }
                                })
                                ->live(),
                        ])
                        ->columnSpanFull(),
                    Grid::make(3)
                        ->schema([
                            Select::make('levels')
                                ->label('레벨')
                                ->options([
                                    1 => '1',
                                    2 => '2',
                                    3 => '3',
                                    4 => '4',
                                    5 => '5',
                                ])
                                ->multiple()
                                ->live()
                                ->required(),
                            Checkbox::make('is_even_distribution')
                                ->columnStart(1)
                                ->default(true)
                                ->live()
                                ->label('레별별 문제 균등 분배'),
                            Grid::make(5)
                                ->schema(function (Get $get) {
                                    $textInput = [];
                                    $levels = $get('levels');
                                    //levels is array
                                    for ($i = 0; $i < count($levels); $i++) {
                                        $textInput[] = TextInput::make('level.' . $levels[$i])
                                            ->label('레벨 ' . $levels[$i] . ' (가중치)')
                                            ->required()
                                            ->integer()
                                            ->default(0);
                                    }
                                    return $textInput;
                                })
                                ->visible(function (Get $get) {
                                    return !$get('is_even_distribution');
                                })
                        ]),
                    Checkbox::make('should_exclude_recent_questions')
                        ->columnStart(1)
                        ->default(false)
                        ->live()
                        ->label('최근 출제 문제 제외 (1달)'),
                    Checkbox::make('is_tag_based')
                        ->columnStart(1)
                        ->default(false)
                        ->live()
                        ->label('태그별 출제'),
                    TagsInput::make('tags')
                        ->label('태그')
                        ->separator(',')
                        ->default([])
                        ->placeholder('태그를 입력하세요.')
                        ->visible(function (Get $get) {
                            return $get('is_tag_based');
                        })
                        ->columnSpanFull(),

                ])
                ->action(function ($data) {
                    $tempData = TempData::create([
                        'value' => $data,
                    ]);
                    redirect('/admin/test-sheets/create/' . $tempData->id);
                })
                ->modalSubmitActionLabel('문제 선택'),
            // === 모의고사 기출 문제지 ===
            Actions\CreateAction::make('create-mock-exam')
                ->icon('heroicon-m-academic-cap')
                ->modalHeading('모의고사 기출 문제지 추가')
                ->modalWidth('3xl')
                ->createAnother(false)
                ->label('모의고사 기출')
                ->color('info')
                ->form([
                    ToggleButtons::make('creation_method')
                        ->label('추가 방식')
                        ->inline()
                        ->options([
                            'number' => '문제 번호로 추가',
                            'category' => '단원으로 추가',
                        ])
                        ->default('number')
                        ->live()
                        ->required()
                        ->columnSpanFull(),

                    // === 방식 A: 문제 번호로 추가 (단일 선택) ===
                    Grid::make(4)
                        ->visible(fn(Get $get) => $get('creation_method') === 'number')
                        ->schema([
                            Select::make('exam_grade')
                                ->label('학년')
                                ->options([
                                    '고1' => '고1',
                                    '고2' => '고2',
                                    '고3' => '고3',
                                ])
                                ->required(),
                            Select::make('exam_year')
                                ->label('년도')
                                ->options(array_combine(
                                    range(date('Y'), 2010, -1),
                                    range(date('Y'), 2010, -1)
                                ))
                                ->required()
                                ->searchable(),
                            Select::make('exam_month')
                                ->label('월')
                                ->options([
                                    3 => '3월', 4 => '4월', 6 => '6월',
                                    7 => '7월', 9 => '9월', 10 => '10월',
                                    11 => '11월 (수능)',
                                ])
                                ->required(),
                            Select::make('exam_subject')
                                ->label('과목')
                                ->options([
                                    '공통수학' => '공통수학',
                                    '대수' => '대수',
                                    '미적분' => '미적분',
                                    '미적분2' => '미적분2',
                                    '확통' => '확률과 통계',
                                    '기하' => '기하',
                                ]),
                        ]),

                    // === 방식 B: 단원으로 추가 (복수 선택 가능) ===
                    Grid::make(3)
                        ->visible(fn(Get $get) => $get('creation_method') === 'category')
                        ->schema([
                            Select::make('exam_grades')
                                ->label('학년')
                                ->multiple()
                                ->options([
                                    '고1' => '고1',
                                    '고2' => '고2',
                                    '고3' => '고3',
                                ]),
                            Select::make('exam_years')
                                ->label('년도')
                                ->multiple()
                                ->options(array_combine(
                                    range(date('Y'), 2010, -1),
                                    range(date('Y'), 2010, -1)
                                ))
                                ->searchable(),
                            Select::make('exam_months')
                                ->label('월')
                                ->multiple()
                                ->options([
                                    3 => '3월', 4 => '4월', 6 => '6월',
                                    7 => '7월', 9 => '9월', 10 => '10월',
                                    11 => '11월 (수능)',
                                ]),
                        ]),

                    // 공통: 과목, 배점 (단원 방식)
                    Grid::make(3)
                        ->visible(fn(Get $get) => $get('creation_method') === 'category')
                        ->schema([
                            Select::make('exam_subjects')
                                ->label('과목')
                                ->multiple()
                                ->options([
                                    '공통수학' => '공통수학',
                                    '대수' => '대수',
                                    '미적분' => '미적분',
                                    '미적분2' => '미적분2',
                                    '확통' => '확률과 통계',
                                    '기하' => '기하',
                                ]),
                            Select::make('exam_scores')
                                ->label('배점')
                                ->multiple()
                                ->options([
                                    2 => '2점', 3 => '3점', 4 => '4점',
                                ]),
                        ]),

                    // 공통: 문제 유형 선택
                    ViewField::make('question_type_ids')
                        ->label('문제 유형')
                        ->view('filament.components.forms.question-type', [
                            'multiple' => true,
                        ])
                        ->live()
                        ->required()
                        ->columnSpanFull(),

                    // 공통: 문제 수, 레벨
                    Grid::make(7)
                        ->schema([
                            ToggleButtons::make('question_count_choice')
                                ->dehydrated(false)
                                ->label('문제 수')
                                ->inline()
                                ->options([25 => 25, 50 => 50, 75 => 75, 100 => 100])
                                ->default(25)
                                ->columnSpan(3)
                                ->afterStateUpdated(fn(Set $set, $state) => $set('question_count', $state))
                                ->live(),
                            TextInput::make('question_count')
                                ->label('직접 입력')
                                ->required()
                                ->integer()
                                ->columnSpan(2)
                                ->default(25)
                                ->afterStateUpdated(function (Set $set, $state) {
                                    if (in_array($state, [25, 50, 75, 100])) {
                                        $set('question_count_choice', $state);
                                    } else {
                                        $set('question_count_choice', null);
                                    }
                                })
                                ->live(),
                        ])->columnSpanFull(),
                    Grid::make(3)
                        ->schema([
                            Select::make('levels')
                                ->label('레벨')
                                ->options([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'])
                                ->multiple()
                                ->live()
                                ->required(),
                            Checkbox::make('is_even_distribution')
                                ->columnStart(1)
                                ->default(true)
                                ->live()
                                ->label('레벨별 문제 균등 분배'),
                        ]),
                    Hidden::make('target_group')->default(null),
                    Hidden::make('source_type')->default('mock_exam'),
                ])
                ->action(function ($data) {
                    $tempData = TempData::create(['value' => $data]);
                    redirect('/admin/test-sheets/create/' . $tempData->id);
                })
                ->modalSubmitActionLabel('문제 선택'),

            // === 학교 기출 문제지 ===
            Actions\CreateAction::make('create-school-exam')
                ->icon('heroicon-m-building-library')
                ->modalHeading('학교 기출 문제지 추가')
                ->modalWidth('3xl')
                ->createAnother(false)
                ->label('학교 기출')
                ->color('success')
                ->form([
                    ToggleButtons::make('creation_method')
                        ->label('추가 방식')
                        ->inline()
                        ->options([
                            'number' => '문제 번호로 추가',
                            'category' => '단원으로 추가',
                        ])
                        ->default('number')
                        ->live()
                        ->required()
                        ->columnSpanFull(),

                    // === 방식 A: 문제 번호로 추가 (단일 선택) ===
                    Grid::make(3)
                        ->visible(fn(Get $get) => $get('creation_method') === 'number')
                        ->schema([
                            Select::make('school_id')
                                ->label('학교')
                                ->searchable()
                                ->getSearchResultsUsing(fn(string $search): array =>
                                    \App\Models\School::where('name', 'like', "%{$search}%")
                                        ->limit(50)
                                        ->pluck('name', 'id')
                                        ->toArray()
                                )
                                ->getOptionLabelUsing(fn($value): ?string =>
                                    \App\Models\School::find($value)?->name
                                )
                                ->required()
                                ->columnSpan(2),
                            Select::make('exam_grade')
                                ->label('학년')
                                ->options([
                                    '고1' => '고1', '고2' => '고2', '고3' => '고3',
                                ])
                                ->required(),
                            Select::make('exam_year')
                                ->label('년도')
                                ->options(array_combine(
                                    range(date('Y'), 2010, -1),
                                    range(date('Y'), 2010, -1)
                                ))
                                ->required()
                                ->searchable(),
                            Select::make('exam_semester')
                                ->label('학기')
                                ->options([1 => '1학기', 2 => '2학기'])
                                ->required(),
                            Select::make('exam_type')
                                ->label('시험 유형')
                                ->options([
                                    'midterm' => '중간고사',
                                    'final' => '기말고사',
                                ])
                                ->required(),
                        ]),

                    // === 방식 B: 단원으로 추가 (복수 선택 가능) ===
                    Grid::make(3)
                        ->visible(fn(Get $get) => $get('creation_method') === 'category')
                        ->schema([
                            Select::make('school_id')
                                ->label('학교')
                                ->searchable()
                                ->getSearchResultsUsing(fn(string $search): array =>
                                    \App\Models\School::where('name', 'like', "%{$search}%")
                                        ->limit(50)
                                        ->pluck('name', 'id')
                                        ->toArray()
                                )
                                ->getOptionLabelUsing(fn($value): ?string =>
                                    \App\Models\School::find($value)?->name
                                )
                                ->columnSpan(2),
                            Select::make('exam_grades')
                                ->label('학년')
                                ->multiple()
                                ->options([
                                    '고1' => '고1', '고2' => '고2', '고3' => '고3',
                                ]),
                            Select::make('exam_years')
                                ->label('년도')
                                ->multiple()
                                ->options(array_combine(
                                    range(date('Y'), 2010, -1),
                                    range(date('Y'), 2010, -1)
                                ))
                                ->searchable(),
                            Select::make('exam_semesters')
                                ->label('학기')
                                ->multiple()
                                ->options([1 => '1학기', 2 => '2학기']),
                            Select::make('exam_types')
                                ->label('시험 유형')
                                ->multiple()
                                ->options([
                                    'midterm' => '중간고사',
                                    'final' => '기말고사',
                                ]),
                        ]),

                    // 공통: 과목 (단원 방식)
                    Grid::make(3)
                        ->visible(fn(Get $get) => $get('creation_method') === 'category')
                        ->schema([
                            Select::make('exam_subjects')
                                ->label('과목')
                                ->multiple()
                                ->options([
                                    '공통수학' => '공통수학',
                                    '대수' => '대수',
                                    '미적분' => '미적분',
                                    '미적분2' => '미적분2',
                                    '확통' => '확률과 통계',
                                    '기하' => '기하',
                                ]),
                        ]),

                    // 공통: 문제 유형 선택
                    ViewField::make('question_type_ids')
                        ->label('문제 유형')
                        ->view('filament.components.forms.question-type', [
                            'multiple' => true,
                        ])
                        ->live()
                        ->required()
                        ->columnSpanFull(),

                    // 공통: 문제 수, 레벨
                    Grid::make(7)
                        ->schema([
                            ToggleButtons::make('question_count_choice')
                                ->dehydrated(false)
                                ->label('문제 수')
                                ->inline()
                                ->options([25 => 25, 50 => 50, 75 => 75, 100 => 100])
                                ->default(25)
                                ->columnSpan(3)
                                ->afterStateUpdated(fn(Set $set, $state) => $set('question_count', $state))
                                ->live(),
                            TextInput::make('question_count')
                                ->label('직접 입력')
                                ->required()
                                ->integer()
                                ->columnSpan(2)
                                ->default(25)
                                ->afterStateUpdated(function (Set $set, $state) {
                                    if (in_array($state, [25, 50, 75, 100])) {
                                        $set('question_count_choice', $state);
                                    } else {
                                        $set('question_count_choice', null);
                                    }
                                })
                                ->live(),
                        ])->columnSpanFull(),
                    Grid::make(3)
                        ->schema([
                            Select::make('levels')
                                ->label('레벨')
                                ->options([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'])
                                ->multiple()
                                ->live()
                                ->required(),
                            Checkbox::make('is_even_distribution')
                                ->columnStart(1)
                                ->default(true)
                                ->live()
                                ->label('레벨별 문제 균등 분배'),
                        ]),
                    Hidden::make('target_group')->default(null),
                    Hidden::make('source_type')->default('school_exam'),
                ])
                ->action(function ($data) {
                    $tempData = TempData::create(['value' => $data]);
                    redirect('/admin/test-sheets/create/' . $tempData->id);
                })
                ->modalSubmitActionLabel('문제 선택'),
        ];
    }
}
