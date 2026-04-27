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
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\ListRecords;
use App\Forms\Components\ExamGridSelect;

class ListTestSheets extends ListRecords
{
    protected static string $resource = TestSheetResource::class;

    protected static ?string $title = '문제지 관리';

    public function getBreadcrumb(): ?string
    {
        return null;
    }

    protected static function getExamSubjectItems(): array
    {
        // 고/고3 루트 카테고리 하위의 과목(depth=1)을 자동으로 가져옴
        $rootIds = \App\Models\QuestionCategory::where('depth', 0)
            ->whereRaw("REPLACE(REPLACE(name, '<p>', ''), '</p>', '') IN ('고', '고3')")
            ->pluck('id');

        return \App\Models\QuestionCategory::where('depth', 1)
            ->whereIn('id', function ($q) use ($rootIds) {
                $q->select('descendant_id')
                    ->from('question_category_closure')
                    ->where('depth', 1)
                    ->whereIn('ancestor_id', $rootIds);
            })
            ->orderBy('id')
            ->pluck('name')
            ->map(fn($name) => trim(str_replace('(2025개정)', '', strip_tags(trim($name)))))
            ->filter(fn($name) => !in_array($name, ['교과외', '연산문제']))
            ->unique()
            ->map(fn($name) => [
                'value' => $name,
                'label' => $name,
            ])
            ->values()
            ->toArray();
    }

    /**
     * 현재 모달 form의 조건에 매치되는 기출 문제 총 개수 반환
     * (모달에서 사용자에게 미리 보여주기 위한 카운트)
     */
    protected static function countAvailableExamQuestions(
        string $sourceType,
        array $grades = [],
        array $years = [],
        array $months = [],
        array $subjects = [],
        ?int $schoolId = null,
        array $semesters = [],
        array $types = [],
        array $questionNumbers = [],
        array $questionTypeIds = [],
        ?string $creationMethod = 'number',
        array $seriesList = []
    ): int {
        $query = \App\Models\Question::withoutGlobalScopes()
            ->where('source_type', $sourceType)
            ->whereNull('parent_question_id');

        if (!empty($grades)) $query->whereIn('exam_grade', $grades);
        if (!empty($years)) $query->whereIn('exam_year', $years);
        if (!empty($months)) $query->whereIn('exam_month', $months);
        if (!empty($subjects)) $query->whereIn('exam_subject', $subjects);
        if (!empty($seriesList)) $query->whereIn('exam_series', $seriesList);
        if ($schoolId) $query->where('school_id', $schoolId);
        if (!empty($semesters)) $query->whereIn('exam_semester', $semesters);
        if (!empty($types)) $query->whereIn('exam_type', $types);

        if ($creationMethod === 'number' && !empty($questionNumbers)) {
            $query->whereIn('exam_question_number', $questionNumbers);
        }

        if ($creationMethod === 'category' && !empty($questionTypeIds)) {
            $query->whereIn('question_type_id', $questionTypeIds);
        }

        return $query->count();
    }

    /**
     * 선택된 학년/년도/월(/학교/학기/시험유형/과목/계열) 조건에 따라
     * DB에 실제로 등록된 문제 번호만 반환.
     * 등록된 번호가 없으면 빈 배열 반환 (조건이 아무 것도 없을 때만 1~45 fallback).
     */
    protected static function getAvailableQuestionNumbers(
        string $sourceType,
        array $grades = [],
        array $years = [],
        array $months = [],
        array $subjects = [],
        ?int $schoolId = null,
        array $semesters = [],
        array $types = [],
        array $seriesList = []
    ): array {
        $query = \App\Models\Question::withoutGlobalScopes()
            ->where('source_type', $sourceType)
            ->whereNotNull('exam_question_number');

        $hasAnyFilter = !empty($grades) || !empty($years) || !empty($months)
            || !empty($subjects) || !empty($seriesList) || $schoolId
            || !empty($semesters) || !empty($types);

        if (!empty($grades)) $query->whereIn('exam_grade', $grades);
        if (!empty($years)) $query->whereIn('exam_year', $years);
        if (!empty($months)) $query->whereIn('exam_month', $months);
        if (!empty($subjects)) $query->whereIn('exam_subject', $subjects);
        if (!empty($seriesList)) $query->whereIn('exam_series', $seriesList);
        if ($schoolId) $query->where('school_id', $schoolId);
        if (!empty($semesters)) $query->whereIn('exam_semester', $semesters);
        if (!empty($types)) $query->whereIn('exam_type', $types);

        $numbers = $query->distinct()
            ->orderBy('exam_question_number')
            ->pluck('exam_question_number')
            ->filter()
            ->values()
            ->toArray();

        // 필터가 지정되었지만 결과가 없으면 빈 배열 → 사용자가 조건 확인 가능
        // 필터가 아무 것도 없을 때만 1~45 fallback (전체 가능 범위)
        if (empty($numbers) && !$hasAnyFilter) {
            $numbers = range(1, 45);
        }

        return collect($numbers)
            ->map(fn($n) => ['value' => (int)$n, 'label' => $n . '번'])
            ->toArray();
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
                    ToggleButtons::make('source_type')
                        ->label('문제 소스')
                        ->inline()
                        ->options([
                            '' => '교재 문제만',
                            'all' => '교재 + 기출 전체',
                            'mock_exam' => '모의고사 기출만',
                            'school_exam' => '학교 기출만',
                        ])
                        ->default('')
                        ->live()
                        ->columnSpanFull(),
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
                ->modalWidth('5xl')
                ->createAnother(false)
                ->label('모의고사 기출')
                ->color('info')
                ->form([
                    Grid::make(4)
                        ->schema([
                            ExamGridSelect::make('exam_grades')
                                ->label('학년 선택')
                                ->multiple()
                                ->cols(2)
                                ->maxHeight(240)
                                ->live()
                                ->items(
                                    \App\Models\GradeSystem::orderBy('sequential_order')
                                        ->get()
                                        ->map(fn($g) => ['value' => $g->display_name, 'label' => $g->display_name])
                                        ->toArray()
                                ),
                            ExamGridSelect::make('exam_years')
                                ->label('년도 선택')
                                ->multiple()
                                ->cols(2)
                                ->maxHeight(240)
                                ->live()
                                ->items(
                                    collect(range((int) date('Y') + 1, 1990, -1))
                                        ->map(fn($y) => ['value' => $y, 'label' => $y . '년'])
                                        ->toArray()
                                ),
                            ExamGridSelect::make('exam_months')
                                ->label('월 선택')
                                ->multiple()
                                ->cols(2)
                                ->maxHeight(240)
                                ->live()
                                ->items([
                                    ['value' => 3, 'label' => '3월'],
                                    ['value' => 4, 'label' => '4월'],
                                    ['value' => 5, 'label' => '5월'],
                                    ['value' => 6, 'label' => '6월'],
                                    ['value' => 7, 'label' => '7월'],
                                    ['value' => 9, 'label' => '9월'],
                                    ['value' => 10, 'label' => '10월'],
                                    ['value' => 11, 'label' => '11월'],
                                ]),
                            ExamGridSelect::make('creation_method')
                                ->label('문제 추가 옵션')
                                ->cols(1)
                                ->items([
                                    ['value' => 'number', 'label' => '문제 번호로 추가'],
                                    ['value' => 'category', 'label' => '단원으로 추가'],
                                ])
                                ->default('number')
                                ->live(),
                        ]),

                    // 과목 선택 (학년/년도/월에 따라 동적으로 옵션 변경) - 선택사항
                    ExamGridSelect::make('exam_subjects')
                        ->label('과목 선택 (선택)')
                        ->multiple()
                        ->cols(5)
                        ->live()
                        ->items(function (Get $get) {
                            $grades = (array) ($get('exam_grades') ?? []);
                            $years = (array) ($get('exam_years') ?? []);
                            $months = (array) ($get('exam_months') ?? []);

                            // 학년/년도가 비어있으면 기본 과목 옵션
                            if (empty($grades) || empty($years)) {
                                return self::getExamSubjectItems();
                            }

                            // 회차별 사용 가능한 과목들의 합집합
                            return \App\Services\ExamSubjectResolver::getSubjectsForCombinations(
                                $grades, $years, $months
                            );
                        })
                        ->columnSpanFull(),

                    // 문제 계열 선택 - 선택사항
                    ExamGridSelect::make('exam_series_list')
                        ->label('문제 계열 (선택)')
                        ->multiple()
                        ->cols(5)
                        ->live()
                        ->items([
                            ['value' => '가형', 'label' => '가형'],
                            ['value' => '나형', 'label' => '나형'],
                            ['value' => '이과', 'label' => '이과'],
                            ['value' => '문과', 'label' => '문과'],
                            ['value' => '공통', 'label' => '공통'],
                            ['value' => '확률과통계', 'label' => '선택 (확률과 통계)'],
                            ['value' => '기하', 'label' => '선택 (기하)'],
                            ['value' => '미적분', 'label' => '선택 (미적분)'],
                            ['value' => '이산수학', 'label' => '선택 (이산수학)'],
                        ])
                        ->columnSpanFull(),

                    // 매치 가능 문제 개수 미리보기
                    Placeholder::make('available_count_preview')
                        ->label('')
                        ->content(function (Get $get) {
                            $count = self::countAvailableExamQuestions(
                                sourceType: 'mock_exam',
                                grades: (array) ($get('exam_grades') ?? []),
                                years: (array) ($get('exam_years') ?? []),
                                months: (array) ($get('exam_months') ?? []),
                                subjects: (array) ($get('exam_subjects') ?? []),
                                questionNumbers: (array) ($get('question_numbers') ?? []),
                                questionTypeIds: (array) ($get('question_type_ids') ?? []),
                                creationMethod: $get('creation_method') ?? 'number',
                                seriesList: (array) ($get('exam_series_list') ?? [])
                            );

                            $color = $count === 0 ? '#dc2626' : ($count < 10 ? '#d97706' : '#059669');
                            $msg = $count === 0
                                ? '⚠️ 선택 조건에 매치되는 모의고사 기출 문제가 없습니다. 학년/년도/단원/번호/계열을 다시 확인하세요.'
                                : "✓ 현재 조건에 매치되는 모의고사 기출 문제: <strong>{$count}개</strong>";
                            return new \Illuminate\Support\HtmlString(
                                "<div style='color: {$color}; font-size: 13px; padding: 8px 12px; background: " .
                                ($count === 0 ? '#fef2f2' : ($count < 10 ? '#fffbeb' : '#f0fdf4')) .
                                "; border-radius: 6px; border: 1px solid " .
                                ($count === 0 ? '#fecaca' : ($count < 10 ? '#fde68a' : '#bbf7d0')) .
                                ";'>{$msg}</div>"
                            );
                        })
                        ->columnSpanFull(),

                    // 문제 번호로 추가 시: 등록된 문제 개수만큼만 동적으로 표시
                    // 등록된 번호가 없으면 빈 상태로 표시되어 사용자가 인지 가능
                    ExamGridSelect::make('question_numbers')
                        ->label('문제 번호 선택 (등록된 번호만 표시)')
                        ->multiple()
                        ->cols(10)
                        ->live()
                        ->items(function (Get $get) {
                            return self::getAvailableQuestionNumbers(
                                sourceType: 'mock_exam',
                                grades: (array) ($get('exam_grades') ?? []),
                                years: (array) ($get('exam_years') ?? []),
                                months: (array) ($get('exam_months') ?? []),
                                subjects: (array) ($get('exam_subjects') ?? []),
                                seriesList: (array) ($get('exam_series_list') ?? [])
                            );
                        })
                        ->columnSpanFull()
                        ->visible(fn(Get $get) => $get('creation_method') !== 'category'),

                    // 단원으로 추가 시: 문제 유형 선택
                    ViewField::make('question_type_ids')
                        ->label('문제 유형')
                        ->view('filament.components.forms.question-type', [
                            'multiple' => true,
                        ])
                        ->live()
                        ->required(fn(Get $get) => $get('creation_method') === 'category')
                        ->columnSpanFull()
                        ->visible(fn(Get $get) => $get('creation_method') === 'category'),

                    // 단원으로 추가 시: 문제 수, 레벨
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
                        ])->columnSpanFull()
                        ->visible(fn(Get $get) => $get('creation_method') === 'category'),
                    Grid::make(3)
                        ->schema([
                            Select::make('levels')
                                ->label('레벨 (기출은 선택)')
                                ->helperText('기출 문제는 레벨이 미지정된 경우가 많습니다. 비워두면 레벨 무시.')
                                ->options([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'])
                                ->multiple()
                                ->live(),
                            Checkbox::make('is_even_distribution')
                                ->columnStart(1)
                                ->default(true)
                                ->live()
                                ->label('레벨별 문제 균등 분배'),
                            Grid::make(5)
                                ->schema(function (Get $get) {
                                    $textInput = [];
                                    $levels = $get('levels');
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
                        ])
                        ->visible(fn(Get $get) => $get('creation_method') === 'category'),
                    Hidden::make('target_group')->default(null),
                    Hidden::make('source_type')->default('mock_exam'),
                ])
                ->action(function ($data) {
                    $tempData = TempData::create(['value' => $data]);
                    redirect('/admin/test-sheets/create/' . $tempData->id);
                })
                ->modalSubmitActionLabel('문제 선택')
                ->visible(fn() => in_array(auth()->user()->role, ['root_admin', 'admin'])),

            // === 학교 기출 문제지 ===
            Actions\CreateAction::make('create-school-exam')
                ->icon('heroicon-m-building-library')
                ->modalHeading('학교 기출 문제지 추가')
                ->modalWidth('5xl')
                ->createAnother(false)
                ->label('학교 기출')
                ->color('success')
                ->form([
                    Select::make('school_id')
                        ->label('학교 검색 (여러 학교 선택 가능)')
                        ->multiple()
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
                        ->columnSpanFull(),

                    Grid::make(5)
                        ->schema([
                            ExamGridSelect::make('exam_grades')
                                ->label('학년 선택')
                                ->multiple()
                                ->cols(2)
                                ->maxHeight(240)
                                ->live()
                                ->items(
                                    \App\Models\GradeSystem::orderBy('sequential_order')
                                        ->get()
                                        ->map(fn($g) => ['value' => $g->display_name, 'label' => $g->display_name])
                                        ->toArray()
                                ),
                            ExamGridSelect::make('exam_years')
                                ->label('년도 선택')
                                ->multiple()
                                ->cols(2)
                                ->maxHeight(240)
                                ->live()
                                ->items(
                                    collect(range((int) date('Y') + 1, 1990, -1))
                                        ->map(fn($y) => ['value' => $y, 'label' => $y . '년'])
                                        ->toArray()
                                ),
                            ExamGridSelect::make('exam_semesters')
                                ->label('학기 선택')
                                ->multiple()
                                ->cols(2)
                                ->live()
                                ->items([
                                    ['value' => 1, 'label' => '1학기'],
                                    ['value' => 2, 'label' => '2학기'],
                                ]),
                            ExamGridSelect::make('exam_types')
                                ->label('시험 유형')
                                ->multiple()
                                ->cols(2)
                                ->live()
                                ->items([
                                    ['value' => 'midterm', 'label' => '중간고사'],
                                    ['value' => 'final', 'label' => '기말고사'],
                                ]),
                            ExamGridSelect::make('creation_method')
                                ->label('문제 추가 옵션')
                                ->cols(1)
                                ->items([
                                    ['value' => 'number', 'label' => '문제 번호로 추가'],
                                    ['value' => 'category', 'label' => '단원으로 추가'],
                                ])
                                ->default('number')
                                ->live(),
                        ]),

                    // 과목 선택 - 선택사항
                    ExamGridSelect::make('exam_subjects')
                        ->label('과목 선택 (선택)')
                        ->multiple()
                        ->cols(7)
                        ->live()
                        ->items(self::getExamSubjectItems())
                        ->columnSpanFull(),

                    // 매치 가능 문제 개수 미리보기 (학교 기출)
                    Placeholder::make('available_count_preview_school')
                        ->label('')
                        ->content(function (Get $get) {
                            $schoolIds = (array) ($get('school_id') ?? []);
                            $schoolId = !empty($schoolIds) ? (int)($schoolIds[0]) : null;

                            $count = self::countAvailableExamQuestions(
                                sourceType: 'school_exam',
                                grades: (array) ($get('exam_grades') ?? []),
                                years: (array) ($get('exam_years') ?? []),
                                subjects: (array) ($get('exam_subjects') ?? []),
                                schoolId: $schoolId,
                                semesters: (array) ($get('exam_semesters') ?? []),
                                types: (array) ($get('exam_types') ?? []),
                                questionNumbers: (array) ($get('question_numbers') ?? []),
                                questionTypeIds: (array) ($get('question_type_ids') ?? []),
                                creationMethod: $get('creation_method') ?? 'number'
                            );

                            $color = $count === 0 ? '#dc2626' : ($count < 10 ? '#d97706' : '#059669');
                            $msg = $count === 0
                                ? '⚠️ 선택 조건에 매치되는 학교 기출 문제가 없습니다.'
                                : "✓ 현재 조건에 매치되는 학교 기출 문제: <strong>{$count}개</strong>";
                            return new \Illuminate\Support\HtmlString(
                                "<div style='color: {$color}; font-size: 13px; padding: 8px 12px; background: " .
                                ($count === 0 ? '#fef2f2' : ($count < 10 ? '#fffbeb' : '#f0fdf4')) .
                                "; border-radius: 6px; border: 1px solid " .
                                ($count === 0 ? '#fecaca' : ($count < 10 ? '#fde68a' : '#bbf7d0')) .
                                ";'>{$msg}</div>"
                            );
                        })
                        ->columnSpanFull(),

                    // 문제 번호로 추가 시: 등록된 문제 개수만큼만 동적 표시
                    ExamGridSelect::make('question_numbers')
                        ->label('문제 번호 선택')
                        ->multiple()
                        ->cols(10)
                        ->live()
                        ->items(function (Get $get) {
                            $schoolIds = (array) ($get('school_id') ?? []);
                            // 학교 검색 필드는 multiple이라서 배열로 옴 → 첫 번째 사용 (단일 학교 기준 fallback)
                            $schoolId = !empty($schoolIds) ? (int)($schoolIds[0]) : null;

                            return self::getAvailableQuestionNumbers(
                                sourceType: 'school_exam',
                                grades: (array) ($get('exam_grades') ?? []),
                                years: (array) ($get('exam_years') ?? []),
                                subjects: (array) ($get('exam_subjects') ?? []),
                                schoolId: $schoolId,
                                semesters: (array) ($get('exam_semesters') ?? []),
                                types: (array) ($get('exam_types') ?? [])
                            );
                        })
                        ->columnSpanFull()
                        ->visible(fn(Get $get) => $get('creation_method') !== 'category'),

                    // 단원으로 추가 시: 문제 유형 선택
                    ViewField::make('question_type_ids')
                        ->label('문제 유형')
                        ->view('filament.components.forms.question-type', [
                            'multiple' => true,
                        ])
                        ->live()
                        ->required(fn(Get $get) => $get('creation_method') === 'category')
                        ->columnSpanFull()
                        ->visible(fn(Get $get) => $get('creation_method') === 'category'),

                    // 단원으로 추가 시: 문제 수, 레벨
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
                        ])->columnSpanFull()
                        ->visible(fn(Get $get) => $get('creation_method') === 'category'),
                    Grid::make(3)
                        ->schema([
                            Select::make('levels')
                                ->label('레벨 (기출은 선택)')
                                ->helperText('기출 문제는 레벨이 미지정된 경우가 많습니다. 비워두면 레벨 무시.')
                                ->options([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'])
                                ->multiple()
                                ->live(),
                            Checkbox::make('is_even_distribution')
                                ->columnStart(1)
                                ->default(true)
                                ->live()
                                ->label('레벨별 문제 균등 분배'),
                            Grid::make(5)
                                ->schema(function (Get $get) {
                                    $textInput = [];
                                    $levels = $get('levels');
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
                        ])
                        ->visible(fn(Get $get) => $get('creation_method') === 'category'),
                    Hidden::make('target_group')->default(null),
                    Hidden::make('source_type')->default('school_exam'),
                ])
                ->action(function ($data) {
                    $tempData = TempData::create(['value' => $data]);
                    redirect('/admin/test-sheets/create/' . $tempData->id);
                })
                ->modalSubmitActionLabel('문제 선택')
                ->visible(fn() => in_array(auth()->user()->role, ['root_admin', 'admin'])),
        ];
    }
}
