<?php

namespace App\Filament\Pages;

use App\Models\Classroom;
use App\Models\GradeSystem;
use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\Student;
use App\Models\TempData;
use App\Models\TestSheet;
use App\Models\User;
use Closure;
use Faker\Provider\ar_EG\Text;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\StaticAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Str;

class CreateTestSheets extends Page implements HasForms, HasActions
{
    use InteractsWithForms, InteractsWithActions;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $slug = 'test-sheets/create/{id}';

    protected static string $view = 'filament.pages.create-test-sheets';

    protected static bool $shouldRegisterNavigation = false;

    protected ?string $maxContentWidth = 'full';

    protected static ?string $title = '문제 등록';

    #[Url]
    public $test_sheet_id;

    #[Url]
    public $copy;

    public $id;

    public $arguments = [];

    public $query = null;

    public $questions = null;

    public $initialPrintLayout = [];

    public $summary = null;

    public $state = "question-selection";

    public $printLayout = null;

    public $data = [
        'name' => null,
        'tags_toggle' => '기본',
        'tags' => [],
        'teacher_ids' => [],
        'assignment_type' => 'student',
        'target_group' => 'grade',
        'target_grades' => [],
        'target_levels' => [],
        'target_classrooms' => [],
        'target_students' => [],
        'is_auto' => false,
        'start_date' => null,
        'end_date' => null,
        'template' => 'default',
        'split' => 'default',
        'full_page_split' => 'default',
        'selected_page_index' => -1,
        'title' => '',
        'sub_title' => '',
        'use_score_table' => false,
        'show_explanation_video' => true,
        'score_table' => [],
        'color' => '#0ea5e9',
        'grade' => '',
        'custom_logo' => [],

    ];

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('문제지 정보')
                ->label('문제지 정보')
                ->heading('1. 문제지 정보')
                ->columns(4)
                ->schema([
                    TextInput::make('name')
                        ->label('문제지 명')
                        ->columnSpanFull()
                        ->required(),
                    ToggleButtons::make('tags_toggle')
                        ->label('태그')
                        ->options([
                            '기본' => '기본',
                            '연습문제' => '연습문제',
                            '숙제' => '숙제',
                            '연산' => '연산',
                            '입학 TEST' => '입학 TEST',
                            '일일 TEST' => '일일 TEST',
                            '주간 TEST' => '주간 TEST',
                            '단원 TEST' => '단원 TEST',
                            '총괄 TEST' => '총괄 TEST',
                            '내신대비' => '내신대비',
                            '수능대비' => '수능대비',
                            '기출 유사' => '기출 유사',
                            '기타자료 유사' => '기타자료 유사',
                            '유형별 학습' => '유형별 학습',
                            '유형별 오답' => '유형별 오답',
                            '단원별 취약' => '단원별 취약',
                            '기간별 오답' => '기간별 오답',
                            '학습지 오답' => '학습지 오답',
                            '교재 오답' => '교재 오답',
                            '모의고사쌍둥이' => '모의고사쌍둥이'
                        ])
                        ->inline()
                        ->columnSpanFull(),
                    TagsInput::make('tags')
                        ->label(false)
                        ->separator(',')
                        ->default([])
                        ->placeholder('태그를 입력하세요.')
                        ->columnSpanFull(),

                    // ToggleButtons::make('assignment_type')
                    //     ->label('생성 방식')
                    //     ->options([
                    //         'student' => '학생 출제',
                    //         'teacher' => '강사 할당',
                    //     ])
                    //     ->default('teacher')
                    //     ->inline()
                    //     ->columns(2)
                    //     ->columnSpanFull()
                    //     ->visible(!$this->test_sheet_id && auth()->user()->role !== 'general')
                    //     ->live()
                    //     ->afterStateUpdated(function (Get $get, Set $set) {
                    //         // 출제 대상 초기화
                    //         $set('target_group', null);
                    //         $set('target_grades', []);
                    //         $set('target_levels', []);
                    //         $set('target_classrooms', []);
                    //         $set('target_students', []);

                    //         // 강사 할당 초기화
                    //         $set('teacher_ids', []);

                    //         // 자동 출제 초기화 - 생성 방식에 따라 다르게 설정
                    //         $assignmentType = $get('assignment_type');
                    //         $set('is_auto', $assignmentType === 'student');
                    //         $set('start_date', null);
                    //         $set('end_date', null);
                    //     }),
                    // Select::make('teacher_ids')
                    //     ->label('강사 할당')
                    //     ->options(function () {
                    //         return \App\Models\Teacher::where('role', '!=', 'root_admin')->with('user')
                    //             ->get()
                    //             ->mapWithKeys(function ($teacher) {
                    //                 return [$teacher->id => $teacher->user->name];
                    //             });
                    //     })
                    //     ->multiple()
                    //     ->columnSpanFull()
                    //     ->visible(fn(Get $get) => $get('assignment_type') === 'teacher'),
                    Grid::make(4)
                        ->schema([
                            Radio::make('target_group')
                                ->label('출제 대상')
                                ->live()
                                ->reactive()
                                ->required()
                                ->options([
                                    'grade' => '학년',
                                    'level' => '레벨',
                                    'classroom' => '교실/반',
                                    'student' => '학생',
                                ])
                                ->default('grade')
                                ->columns(4)
                                ->columnSpanFull()
                                ->visible(fn(Get $get) => $get('assignment_type') === 'student'),

                        ])->columnSpanFull(),
                    Grid::make(2)
                        ->schema([
                            Select::make('target_grades')
                                ->label('학년')
                                ->multiple()
                                ->required()
                                ->options(function () {
                                    return GradeSystem::query()
                                        ->orderBy('sequential_order')
                                        ->pluck(
                                            'display_name',
                                            'id',
                                        );
                                })
                                ->visible(fn(Get $get) => $get('target_group') === 'grade' || $get('target_group') === 'level'),
                            Select::make('target_levels')
                                ->label('레벨')
                                ->multiple()
                                ->required()
                                ->options([
                                    'A' => 'A',
                                    'M' => 'M',
                                    'S' => 'S',
                                ])
                                ->visible(fn(Get $get) => $get('target_group') === 'level'),
                            Select::make('target_classrooms')
                                ->label('반')
                                ->multiple()
                                ->required()
                                ->options(function () {
                                    return Classroom::query()
                                        ->orderBy('name')
                                        ->pluck('name', 'id');
                                })
                                ->visible(fn(Get $get) => $get('target_group') === 'classroom'),
                            ToggleButtons::make('target_students')
                                ->label('학생')
                                ->multiple()
                                ->required()
                                ->inline()
                                ->options(function () {
                                    $classroomIds = Classroom::query()
                                        ->orderBy('name')
                                        ->pluck('id');
                                    return Student::query()
                                        ->whereHas('classrooms', function ($q) use ($classroomIds) {
                                            $q->whereIn('classrooms.id', $classroomIds);
                                        })
                                        ->get()
                                        ->mapWithKeys(fn($student) => [$student->user->id => $student->user->name]);
                                })
                                ->visible(fn(Get $get) => $get('target_group') === 'student' && $get('assignment_type') === 'student'),
                        ]),
                    Checkbox::make('is_auto')
                        ->default(true)
                        ->live()
                        ->label('자동 출제')
                        ->visible(fn(Get $get) => $get('assignment_type') === 'student'),
                    DateTimePicker::make('start_date')
                        ->label('출제일')
                        ->columnStart(1)
                        ->columnSpan(2)
                        ->required()
                        ->visible(fn(Get $get) => $get('is_auto')),
                    DateTimePicker::make('end_date')
                        ->label('마감일')
                        ->visible(fn(Get $get) => $get('is_auto'))
                        ->required()
                        ->columnSpan(2),


                ]),
            Section::make('문제지 템플릿')
                ->label('문제지 템플릿')
                ->heading('2. 문제지 템플릿')
                ->columns(4)
                ->schema([
                    ToggleButtons::make('template')
                        ->label('템플릿')
                        ->required()
                        ->live()
                        ->options([
                            'default' => '기본',
                            'round' => '둥근',
                            'simple' => '간단',
                            'friendly' => '친근',
                            'high3' => '고3',
                        ])
                        ->inline()
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            $this->dispatch('onPageMetaChanged', [
                                'template' => $get('template'),
                            ]);
                        })
                        ->columnSpanFull()
                        ->default('default'),
                    Hidden::make('selected_page_index')
                        ->live()
                        ->default('default'),
                    ViewField::make('color')
                        ->label('색상')
                        ->required()
                        ->live()
                        ->view('filament.components.forms.preset-color-picker')
                        ->columnSpanFull()
                        ->visible(fn(Get $get) => $get('template') !== 'high3'),
                    TextInput::make('grade')
                        ->label('학년')
                        ->columnSpanFull()
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            $this->dispatch('onPageMetaChanged', [
                                'grade' => $get('grade'),
                            ]);
                        })
                        ->live()
                        ->required(),
                    FileUpload::make('custom_logo')
                        ->label('학원 이미지')
                        ->image()
                        ->multiple(false)
                        ->placeholder('학원 이미지 업로드')
                        ->previewable(true)
                        ->downloadable(true)
                        ->visible(fn(Get $get) => $get('template') !== 'high3')
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            $key = array_key_first($this->data['custom_logo']);
                            $file = $this->data['custom_logo'][$key];
                            $filename = $file->getFilename();
                            $file->storeAs('public', $filename);
                            $this->dispatch('onPageMetaChanged', [
                                'customLogo' => '/storage/' . $filename,
                            ]);
                        })
                        ->live()
                        ->columnSpanFull(),
                    TextInput::make('title')
                        ->label('제목')
                        ->columnSpanFull()
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            $this->dispatch('onPageMetaChanged', [
                                'title' => $get('title'),
                            ]);
                        })
                        ->live()
                        ->required(),
                    TextInput::make('sub_title')
                        ->label('부제목')
                        ->columnSpanFull()
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            $this->dispatch('onPageMetaChanged', [
                                'subTitle' => $get('sub_title'),
                            ]);
                        })
                        ->required(),
                    Hidden::make('full_page_split'),
                    ToggleButtons::make('split')
                        ->label(function (Get $get) {
                            $page = $get('selected_page_index');
                            if ($page === -1) {
                                $page = '전체';
                            } else {
                                $page++;
                            }
                            return '문제 분할 (' . ($page) . ' 페이지)';
                        })
                        ->inline()
                        ->columnSpanFull()
                        ->required()
                        ->options([
                            'default' => '기본',
                            '2Items' => '2분할',
                            '3-1Items' => '3-1분할',
                            '3-2Items' => '3-2분할',
                            '4Items' => '4분할',
                            '6Items' => '6분할',
                        ])
                        ->columnStart(1)
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            if ($get('selected_page_index') == -1) {
                                $set('full_page_split', $get('split'));
                            }
                            $this->dispatch('onSplitChanged', [
                                'layoutMode' => $get('split'),
                                'pageIndex' => $get('selected_page_index'),
                            ]);
                        })
                        ->default('default'),

                ]),
            Section::make('배점표')
                ->label('배점표')
                ->heading('3. 배점표')
                ->columns(4)
                ->schema([
                    Toggle::make('use_score_table')
                        ->label('배점표 사용')
                        ->columnSpanFull()
                        ->live()
                        ->reactive()
                        ->default(true),
                    KeyValue::make('score_table')
                        ->label('배점표')
                        ->keyLabel('문항 번호')
                        ->keyPlaceholder('1,2,3 혹은 1-3')
                        ->valuePlaceholder('2')
                        ->valueLabel('배점')
                        ->columnSpanFull()
                        ->visible(fn(Get $get) => $get('use_score_table'))
                        ->rules([
                            fn(): Closure => function (string $attribute, $value, Closure $fail) {
                                foreach ($value as $key => $score) {
                                    // 배점 검증 (양의 정수인지 확인)
                                    if (!is_numeric($score) || intval($score) != $score || $score <= 0) {
                                        $fail('유효하지 않은 배점표입니다.');
                                        return;
                                    }

                                    // 문항 번호 형식 검증
                                    if (!self::validateQuestionFormat($key)) {
                                        $fail('유효하지 않은 배점표입니다.');
                                        return;
                                    }
                                }
                            },
                        ])
                ]),
            Section::make('기타')
                ->label('기타')
                ->heading('4. 기타')
                ->columns(4)
                ->schema([
                    Toggle::make('show_explanation_video')
                        ->label('해설 강의 표기')
                        ->columnSpanFull()
                        ->live()
                        ->reactive()
                        ->default(true),
                ])

        ])
            ->statePath('data');
    }

    public function mount($id)
    {
        $query = TempData::findOrFail($id)?->value;
        // dd($query);

        if ($this->test_sheet_id !== null) {
            $testSheet = TestSheet::findOrFail($this->test_sheet_id);
            $this->questions = Question::whereIn('id', collect($testSheet->questions)->pluck('id'))
                ->with('questionType', 'choices')
                ->get()
                ->sortBy(function ($question) use ($testSheet) {
                    // testSheet->questions 배열에서 해당 id의 인덱스를 찾아서 그 순서대로 정렬
                    return array_search(
                        $question->id,
                        collect($testSheet->questions)->pluck('id')->toArray()
                    );
                })
                ->values();


            $this->data = array_merge(
                $this->data,
                $testSheet->toArray()
            );
            if ($this->copy) {
                $this->data['target_group'] = null;
                $this->data['target_grades'] = [];
                $this->data['target_levels'] = [];
                $this->data['target_classrooms'] = [];
                $this->data['target_students'] = [];
            }
            $this->data['tags'] = collect($this->data['tags'])->values()->toArray();
            $this->data['tags_toggle'] = '';
            $this->initialPrintLayout = $testSheet->print_layout ?? [];
            $this->data['color'] = $this->initialPrintLayout['color'] ?? '#0ea5e9';
            $this->data['grade'] = $this->initialPrintLayout['grade'] ?? [];
        } else {
            $this->questions = self::selectRandomQuestions($query);
            $this->data = array_merge(
                $this->data,
                $query
            );
            //get first question

            if ($query['target_group'] === 'grade' || $query['target_group'] === 'level') {
                $grade = GradeSystem::findOrFail($query['target_grades'][0]);
            } else if ($query['target_group'] === 'classroom') {
                $grades = Classroom::findOrFail($query['target_classrooms'][0])->target_grades;
                $grade = GradeSystem::findOrFail($grades[0]);
            } else if ($query['target_group'] === 'student') {
                $student = User::findOrFail($query['target_students'][0])->userable;
                $grade = GradeSystem::findOrFail($student->grade_system_id);
            }

            $this->data['grade'] = $grade->display_name ?? '';

            if ($query['material_id'] ?? false) {
                $this->data['tags_toggle'] = '숙제';
            }
            $this->initialPrintLayout = [
                'grade' => $this->data['grade'],
            ];
        }
        $this->initialPrintLayout['startingNumber'] = (int) ($query['material_range_start'] ?? 1);
        $this->data['full_page_split'] = $this->data['split'];

        $this->summary = self::getDistributionSummary($this->questions);
        $this->id = $id;
        $this->query = $query;
    }

    public static function selectRandomQuestions(array $params): Collection
    {
        $totalQuestionCount = $params['question_count'];
        $excludeIds = $params['exclude_ids'] ?? [];

        if (!empty($params['material_id'])) {
            return self::selectMaterialQuestions($params, $excludeIds);
        }

        if ($params['should_exclude_recent_questions'] ?? false) {
            $excludeIds = array_merge($excludeIds, self::getRecentQuestionIds($params));
        }

        $result = collect();

        // 레벨 분포에 따른 문제 선택
        if ($params['is_even_distribution']) {
            $result = self::selectQuestionsWithEvenDistribution($params, $excludeIds);
        } else {
            $result = self::selectQuestionsWithWeightedDistribution($params, $excludeIds);
        }

        // 부족한 문제 수를 채우기 위한 추가 선택 (레벨별 균등 분배)
        if ($result->count() < $totalQuestionCount) {
            $levels = $params['levels'];
            $existingIds = $result->pluck('id')->merge($excludeIds)->unique()->values()->toArray();

            // 여러 라운드에 걸쳐 레벨별로 균등하게 채움
            $maxRounds = 5;
            for ($round = 0; $round < $maxRounds && $result->count() < $totalQuestionCount; $round++) {
                $remainingCount = $totalQuestionCount - $result->count();
                $perLevel = (int) ceil($remainingCount / count($levels));

                foreach ($levels as $level) {
                    $needed = min($perLevel, $totalQuestionCount - $result->count());
                    if ($needed <= 0) break;

                    $additional = self::selectAdditionalQuestions($params, $needed, $existingIds, $level);
                    $result = $result->concat($additional);
                    $existingIds = array_merge($existingIds, $additional->pluck('id')->toArray());
                }
            }
        }

        // 교재가 지정된 경우 순서 정렬
        if (!empty($params['material_id'])) {
            $result = $result->sortBy('seq')->values();
        }

        return $result;
    }

    protected static function getRecentQuestionIds(array $params): array
    {
        $target_group = $params['target_group'] ?? 'grade';
        $targetQuery = TestSheet::where('user_id', auth()->id())
            ->where('created_at', '>=', now()->subMonth());

        switch ($target_group) {
            case 'grade':
                if (!empty($params['target_grades'])) {
                    $targetQuery->whereJsonContains('target_grades', $params['target_grades']);
                }
                break;
            case 'level':
                if (!empty($params['target_grades'])) {
                    $targetQuery->whereJsonContains('target_grades', $params['target_grades']);
                }
                if (!empty($params['target_levels'])) {
                    $targetQuery->whereJsonContains('target_levels', $params['target_levels']);
                }
                break;
            case 'classroom':
                if (!empty($params['target_classrooms'])) {
                    $targetQuery->whereJsonContains('target_classrooms', $params['target_classrooms']);
                }
                break;
            case 'student':
                if (!empty($params['target_students'])) {
                    $targetQuery->whereJsonContains('target_students', $params['target_students']);
                }
                break;
        }

        return $targetQuery->get()
            ->pluck('questions.*.id')
            ->flatten()
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    protected static function buildBaseQuery($typeId = null, array $params, array $excludeIds, ?int $level = null)
    {
        return Question::when($typeId !== null, function ($query) use ($typeId) {
            if (is_array($typeId)) {
                return $query->whereIn('question_type_id', $typeId);
            }
            return $query->where('question_type_id', $typeId);
        })
            ->when($level !== null, function ($query) use ($level) {
                return $query->where('level', $level);
            })
            ->whereNotIn('id', $excludeIds)
            ->whereNull('parent_question_id')
            ->when($params['is_tag_based'] ?? false, function ($query) use ($params) {
                return $query->where(function ($q) use ($params) {
                    $tags = is_array($params['tags']) ? $params['tags'] : [$params['tags']];
                    foreach ($tags as $tag) {
                        $q->whereJsonContains('tags', $tag);
                    }
                });
            })
            // ->when($params['material_id'] ?? null, function ($query) use ($params) {
            //     return $query->where('material_id', $params['material_id'])
            //         ->when($params['is_material_range'] ?? false, function ($query) use ($params) {
            //             return $query->whereBetween('seq', [
            //                 $params['material_range_start'],
            //                 $params['material_range_end']
            //             ]);
            //         })
            //         ->orderBy('seq');
            // }, function ($query) {
            //     return $query
            // })
            // source_type 필터: null/''=교재만, 'all'=전체(교재+기출), 'mock_exam'/'school_exam'=해당 기출만
            ->when(empty($params['source_type']), function ($query) {
                // 기본: 교재 문제만 (기존 동작 유지)
                return $query->where('material_id', null)->whereNull('source_type');
            })
            ->when(($params['source_type'] ?? null) === 'all', function ($query) {
                // 전체: 교재 + 기출 모두
                return $query;
            })
            ->when($params['source_type'] ?? null, function ($query) use ($params) {
                if ($params['source_type'] === 'all') return;
                $query->where('source_type', $params['source_type']);

                // 모의고사 기출 필터
                if ($params['source_type'] === 'mock_exam') {
                    $query->when($params['exam_year'] ?? null, fn($q, $v) => $q->where('exam_year', $v))
                        ->when($params['exam_years'] ?? null, fn($q, $v) => $q->whereIn('exam_year', $v))
                        ->when($params['exam_month'] ?? null, fn($q, $v) => $q->where('exam_month', $v))
                        ->when($params['exam_months'] ?? null, fn($q, $v) => $q->whereIn('exam_month', $v))
                        ->when($params['exam_grade'] ?? null, fn($q, $v) => $q->where('exam_grade', $v))
                        ->when($params['exam_grades'] ?? null, fn($q, $v) => $q->whereIn('exam_grade', $v))
                        ->when($params['exam_subject'] ?? null, fn($q, $v) => $q->where('exam_subject', $v))
                        ->when($params['exam_subjects'] ?? null, fn($q, $v) => $q->whereIn('exam_subject', $v))
                        ->when($params['exam_scores'] ?? null, fn($q, $v) => $q->whereIn('exam_score', $v));
                }

                // 학교 기출 필터
                if ($params['source_type'] === 'school_exam') {
                    $query->when($params['school_id'] ?? null, fn($q, $v) => $q->where('school_id', $v))
                        ->when($params['exam_year'] ?? null, fn($q, $v) => $q->where('exam_year', $v))
                        ->when($params['exam_years'] ?? null, fn($q, $v) => $q->whereIn('exam_year', $v))
                        ->when($params['exam_grade'] ?? null, fn($q, $v) => $q->where('exam_grade', $v))
                        ->when($params['exam_grades'] ?? null, fn($q, $v) => $q->whereIn('exam_grade', $v))
                        ->when($params['exam_semester'] ?? null, fn($q, $v) => $q->where('exam_semester', $v))
                        ->when($params['exam_semesters'] ?? null, fn($q, $v) => $q->whereIn('exam_semester', $v))
                        ->when($params['exam_type'] ?? null, fn($q, $v) => $q->where('exam_type', $v))
                        ->when($params['exam_types'] ?? null, fn($q, $v) => $q->whereIn('exam_type', $v))
                        ->when($params['exam_subjects'] ?? null, fn($q, $v) => $q->whereIn('exam_subject', $v));
                }
            })
            ->inRandomOrder()
            ->with('questionType', 'choices');
    }

    protected static function selectQuestionsForType($typeId, $questionCount, array $params, array $excludeIds, ?int $level = null): Collection
    {
        if ($questionCount <= 0) {
            return collect();
        }


        return self::buildBaseQuery($typeId, $params, $excludeIds, $level)
            ->take($questionCount)
            ->get();
    }

    protected static function selectMaterialQuestions(array $params, array $excludeIds): Collection
    {
        return Question::where('material_id', $params['material_id'])
            ->when($params['material_range_start'] ?? false, function ($query) use ($params) {
                return $query->skip($params['material_range_start'] - 1)
                    ->take($params['material_range_end'] - $params['material_range_start'] + 1);
            })
            ->whereNotIn('id', $excludeIds)
            ->whereNull('parent_question_id')
            ->orderBy('seq')
            ->with('questionType', 'choices')
            ->take($params['question_count'])
            ->get();
    }

    protected static function selectQuestionsWithEvenDistribution(array $params, array $excludeIds): Collection
    {
        $result = collect();
        $levels = $params['levels'];
        $questionTypeIds = $params['question_type_ids'];

        if (empty($levels) || empty($questionTypeIds)) {
            return $result;
        }

        $questionsPerLevel = (int) floor($params['question_count'] / count($levels));
        $remainingQuestions = $params['question_count'] % count($levels);

        foreach ($levels as $levelIndex => $level) {
            $levelQuestionCount = $questionsPerLevel + ($levelIndex < $remainingQuestions ? 1 : 0);

            // 유형 ID를 셔플하여 나머지 문제가 특정 유형에 몰리지 않게 함
            $shuffledTypeIds = $questionTypeIds;
            shuffle($shuffledTypeIds);

            $questionsPerType = (int) floor($levelQuestionCount / count($shuffledTypeIds));
            $remainingTypeQuestions = $levelQuestionCount % count($shuffledTypeIds);

            foreach ($shuffledTypeIds as $typeIndex => $typeId) {
                $typeQuestionCount = $questionsPerType + ($typeIndex < $remainingTypeQuestions ? 1 : 0);
                $questions = self::selectQuestionsForType($typeId, $typeQuestionCount, $params, $excludeIds, $level);
                $result = $result->concat($questions);
            }
        }

        return $result;
    }

    protected static function selectQuestionsWithWeightedDistribution(array $params, array $excludeIds): Collection
    {
        $result = collect();
        $levelWeights = $params['level'];
        $questionTypeIds = $params['question_type_ids'];
        $totalWeight = array_sum($levelWeights);
        $totalQuestionCount = $params['question_count'];

        // 레벨별 문제 수 계산
        $levelQuestionCounts = self::calculateWeightedQuestionCounts($levelWeights, $totalQuestionCount);

        foreach ($levelQuestionCounts as $level => $count) {
            if ($count > 0) {
                // 유형 ID를 셔플하여 나머지 문제가 특정 유형에 몰리지 않게 함
                $shuffledTypeIds = $questionTypeIds;
                shuffle($shuffledTypeIds);

                $questionsPerType = (int) floor($count / count($shuffledTypeIds));
                $remainingTypeQuestions = $count % count($shuffledTypeIds);

                foreach ($shuffledTypeIds as $typeIndex => $typeId) {
                    $typeQuestionCount = $questionsPerType + ($typeIndex < $remainingTypeQuestions ? 1 : 0);
                    $questions = self::selectQuestionsForType($typeId, $typeQuestionCount, $params, $excludeIds, $level);
                    $result = $result->concat($questions);
                }
            }
        }

        return $result;
    }

    protected static function calculateWeightedQuestionCounts(array $levelWeights, int $totalQuestionCount): array
    {
        $totalWeight = array_sum($levelWeights);
        $levelQuestionCounts = [];
        $assignedQuestions = 0;

        foreach ($levelWeights as $level => $weight) {
            $levelCount = (int) round($totalQuestionCount * ($weight / $totalWeight));
            $levelQuestionCounts[$level] = $levelCount;
            $assignedQuestions += $levelCount;
        }

        // 반올림으로 인한 차이 보정
        $diff = $totalQuestionCount - $assignedQuestions;
        if ($diff != 0) {
            $maxWeightLevel = array_keys($levelWeights, max($levelWeights))[0];
            $levelQuestionCounts[$maxWeightLevel] += $diff;
        }

        return $levelQuestionCounts;
    }

    protected static function selectAdditionalQuestions(array $params, int $remainingCount, array $excludeIds, $level): Collection
    {
        return self::buildBaseQuery($params['question_type_ids'], $params, $excludeIds, $level)
            ->take($remainingCount)
            ->get();
    }

    /**
     * 각 레벨과 타입별로 실제 추출된 문제 수를 계산
     */
    public static function getDistributionSummary(Collection $questions): array
    {
        $summary = [
            'by_answer_type' => [],
            'by_type' => [],
            'by_level' => [],
            'by_type_and_level' => []
        ];

        foreach ($questions as $question) {
            // 타입별 카운트
            if (!isset($summary['by_type'][$question->question_type_id])) {
                $summary['by_type'][$question->question_type_id] = 0;
            }
            $summary['by_type'][$question->question_type_id]++;

            // 레벨별 카운트
            if (!isset($summary['by_level'][$question->level])) {
                $summary['by_level'][$question->level] = 0;
            }
            $summary['by_level'][$question->level]++;

            // 정답 유형별 카운트
            if (!isset($summary['by_answer_type'][$question->answer_type])) {
                $summary['by_answer_type'][$question->answer_type] = 0;
            }
            $summary['by_answer_type'][$question->answer_type]++;

            // 타입과 레벨 조합별 카운트
            if (!isset($summary['by_type_and_level'][$question->question_type_id])) {
                $summary['by_type_and_level'][$question->question_type_id] = [];
            }
            if (!isset($summary['by_type_and_level'][$question->question_type_id][$question->level])) {
                $summary['by_type_and_level'][$question->question_type_id][$question->level] = 0;
            }
            $summary['by_type_and_level'][$question->question_type_id][$question->level]++;
        }

        return $summary;
    }

    public function onOrderChanged($data)
    {
        // data의 value를 순서대로 나열한 배열 생성
        $orderedIds = collect($data)->pluck('value')->toArray();

        // Collection의 sortBy 메서드를 사용하여 $orderedIds 배열의 인덱스 순서대로 정렬
        $this->questions = $this->questions->sortBy(function ($question) use ($orderedIds) {
            return array_search($question->id, $orderedIds);
        })->values();

        $this->dispatch('onQuestionUpdated', $this->questions);
    }

    public function removeQuestion($questionId)
    {
        $this->questions = $this->questions->reject(function ($question) use ($questionId) {
            return $question->id == $questionId;
        })->values();
        $this->summary = self::getDistributionSummary($this->questions);
        $this->dispatch('onQuestionUpdated', $this->questions);
    }

    #[On('onQuestionTypeChanged')]
    public function onQuestionTypeChanged()
    {
        if (!$this->mountedActionsData ?? true) {
            return;
        }
        $levels = $this->mountedActionsData[0]['levels'];
        $questionTypeIds = $this->mountedActionsData[0]['question_type_ids'];
        $isEvenDistribution = true;
        if (empty($levels) || empty($questionTypeIds)) {
            $this->mountedActionsData[0]['questions'] = [];
            return;
        }
        $questions = self::selectRandomQuestions([
            'question_count' => 50,
            'question_type_ids' => $questionTypeIds,
            'levels' => $levels,
            'is_even_distribution' => $isEvenDistribution,
            'exclude_ids' => $this->questions->pluck('id')->toArray(),
        ]);
        $this->mountedActionsData[0]['questions'] = $questions->toArray();
    }

    public function addSimilarQuestionAction()
    {
        return Action::make('addSimilarQuestion')
            ->action(function ($arguments) {
                $this->arguments = $arguments;
                $this->replaceMountedAction('addQuestion');
            });
    }

    public function confirmQuestionAction()
    {
        return Action::make('confirmQuestion')
            ->icon('heroicon-m-check-circle')
            ->label('다음 단계')
            ->action(function () {
                $this->state = 'test-sheet-form';
            });
    }


    public function addQuestionAction()
    {
        return Action::make('addQuestion')
            ->modalHeading('새 문제 추가')
            ->modalWidth('6xl')
            ->icon('heroicon-m-plus-circle')
            ->label('새 문제 추가')
            ->modalSubmitActionLabel('추가하기')
            ->fillForm(function () {

                $levels = $this->query['levels'] ?? [];
                $questionTypeIds = $this->query['question_type_ids'] ?? [];
                if ($this->arguments['question']['question_type_id'] ?? false) {
                    $questionTypeIds = [$this->arguments['question']['question_type_id']];
                }
                if ($this->arguments['question']['level'] ?? false) {
                    $levels = [$this->arguments['question']['level']];
                }

                if (empty($levels) || empty($questionTypeIds)) {
                    return [
                        'questions' => [],
                        'question_ids' => [],
                        'question_type_ids' => $questionTypeIds,
                        'levels' => $levels,
                    ];
                }

                $questions = self::selectRandomQuestions([
                    'question_count' => 50,
                    'question_type_ids' => $questionTypeIds,
                    'levels' => $levels,
                    'is_even_distribution' => true,
                    'exclude_ids' => $this->questions->pluck('id')->toArray(),
                ]);

                return [
                    'questions' => $questions,
                    'question_ids' => [],
                    'question_type_ids' => $questionTypeIds,
                    'levels' => $levels,
                ];
            })
            ->form([
                Grid::make(2)
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                ViewField::make('question_type_ids')
                                    ->label('문제 유형')
                                    ->view('filament.components.forms.question-type', [
                                        'multiple' => true,
                                        'event' => 'onQuestionTypeChanged',
                                    ])
                                    ->reactive()
                                    ->live()
                                    ->columnSpanFull(),
                                Select::make('levels')
                                    ->label('레벨')
                                    ->options([
                                        1 => '1',
                                        2 => '2',
                                        3 => '3',
                                        4 => '4',
                                        5 => '5',
                                    ])
                                    ->columnSpan(2)
                                    ->multiple()
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        $levels = $get('levels');
                                        $questionTypeIds = $get('question_type_ids');
                                        $isEvenDistribution = true;
                                        if (empty($levels) || empty($questionTypeIds)) {
                                            $set('questions', collect());
                                            return;
                                        }
                                        $questions = self::selectRandomQuestions([
                                            'question_count' => 50,
                                            'question_type_ids' => $questionTypeIds,
                                            'levels' => $levels,
                                            'is_even_distribution' => $isEvenDistribution,
                                            'exclude_ids' => $this->questions->pluck('id')->toArray(),
                                        ]);
                                        $set('questions', $questions->toArray());
                                    }),


                            ])
                            ->columnSpan(1),
                        Grid::make(4)
                            ->schema([
                                ViewField::make('questions')
                                    ->label('문제 목록')
                                    ->reactive()
                                    ->view('filament.components.forms.question-list')
                                    ->live()
                                    ->columnSpanFull(),
                                Hidden::make('question_ids')
                                    ->afterStateUpdated(function (Get $get, Set $set) {})
                                    ->default([])
                                    ->required()
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan(1)
                    ])
            ])
            ->action(function ($data) {
                $questionIds = $data['question_ids'];
                $this->addQuestions($questionIds);
                $this->summary = self::getDistributionSummary($this->questions);
            });
    }

    public function addTempQuestion($id)
    {
        if (!$this->mountedActionsData ?? true) {
            return;
        }
        $questionsIds = $this->mountedActionsData[0]['question_ids'] ?? [];
        // if not in array
        if (!in_array($id, $questionsIds))
            $questionsIds[] = $id;
        $this->mountedActionsData[0]['question_ids'] = $questionsIds;
    }

    public function addQuestions($questionIds)
    {
        $questions = Question::whereIn('id', $questionIds)->get();
        $this->questions = $this->questions->concat($questions)->values();

        $this->dispatch('onQuestionUpdated', $this->questions);

        Notification::make()
            ->title('문제 추가 완료')
            ->success()
            ->send();
    }

    #[On('onPageSelected')]
    public function onPageSelected($data)
    {
        $pageIndex = $data['pageIndex'];
        $this->data['selected_page_index'] = $pageIndex;
        $this->data['split'] = $data['layoutMode'];
    }

    #[On('submitWithLayout')]
    public function handleSubmitWithLayout($layoutData)
    {
        $this->printLayout = $layoutData;
        $this->createTestSheet();
    }

    public function createTestSheet()
    {
        $this->form->validate();
        $formData = $this->data;

        $questionsData = $this->questions->map(function ($question) {
            return $question->toArray();
        })->toArray();

        $scopes = $this->questions
            ->map(fn($question) => $question->questionType->name)
            ->unique()
            ->values()
            ->toArray();

        // 테스트 시트 생성
        $tags = $formData['tags'];
        $tags_toggle = $formData['tags_toggle'] ?? '';
        if ($tags_toggle) {
            $tags = array_merge($tags, explode(',', $tags_toggle));
            // 중복 제거
            $tags = array_unique($tags);
        }

        $parsed_score_table = [];
        if ($formData['use_score_table']) {
            $parsed_score_table = self::parseScoreTable($formData['score_table'], count($questionsData));
        }

        $upsertData = [
            'name' => $formData['name'],
            'tags' => $tags,
            'status' => 'pending',
            'user_id' => auth()->id(),
            'target_group' => $formData['target_group'],
            'target_grades' => $formData['target_grades'],
            'target_levels' => $formData['target_levels'],
            'target_classrooms' => $formData['target_classrooms'],
            'target_students' => $formData['target_students'],
            'is_auto' => $formData['is_auto'],
            'start_date' => $formData['start_date'],
            'end_date' => $formData['end_date'],
            'template' => $formData['template'],
            'split' => $formData['full_page_split'],
            'title' => $formData['title'],
            'sub_title' => $formData['sub_title'],
            'questions' => $questionsData,
            'scopes' => $scopes,
            'temp_data_id' => $this->id,
            'use_score_table' => $formData['use_score_table'],
            'show_explanation_video' => $formData['show_explanation_video'],
            'score_table' => $formData['score_table'],
            'parsed_score_table' => $parsed_score_table,
            'print_layout' => $this->printLayout,
            // 기출 문제지 필드
            'source_type' => $this->query['source_type'] ?? null,
            'creation_method' => $this->query['creation_method'] ?? null,
            'exam_years' => $this->query['exam_years'] ?? (($this->query['exam_year'] ?? null) ? [$this->query['exam_year']] : null),
            'exam_months' => $this->query['exam_months'] ?? (($this->query['exam_month'] ?? null) ? [$this->query['exam_month']] : null),
            'exam_grades' => $this->query['exam_grades'] ?? (($this->query['exam_grade'] ?? null) ? [$this->query['exam_grade']] : null),
            'exam_subjects' => $this->query['exam_subjects'] ?? (($this->query['exam_subject'] ?? null) ? [$this->query['exam_subject']] : null),
            'exam_scores' => $this->query['exam_scores'] ?? null,
            'school_id' => $this->query['school_id'] ?? null,
            'exam_semesters' => $this->query['exam_semesters'] ?? (($this->query['exam_semester'] ?? null) ? [$this->query['exam_semester']] : null),
            'exam_types' => $this->query['exam_types'] ?? (($this->query['exam_type'] ?? null) ? [$this->query['exam_type']] : null),
        ];
        if ($this->test_sheet_id && !$this->copy) {
            TestSheet::find($this->test_sheet_id)->update($upsertData);
            Notification::make()
                ->title('시험지가 수정되었습니다.')
                ->success()
                ->send();
        } else {
            $testSheet = TestSheet::create($upsertData);
            if (isset($formData['teacher_ids']) && !empty($formData['teacher_ids'])) {
                $testSheet->teachers()->attach($formData['teacher_ids']);
            }
            Notification::make()
                ->title('시험지가 생성되었습니다.')
                ->success()
                ->send();
        }

        // 시험지 목록 페이지로 리다이렉트
        // return redirect()->route('filament.resources.test-sheets.index');
        return redirect('/admin/test-sheets');
    }

    /**
     * 문항 번호 형식을 검증하는 함수
     * @param string $key
     * @return bool
     */
    public static function validateQuestionFormat(string $key): bool
    {
        // 단일 숫자 (예: "1")
        if (preg_match('/^\d+$/', $key)) {
            return true;
        }

        // 콤마로 구분된 숫자들 (예: "2,3")
        if (preg_match('/^\d+(?:,\d+)+$/', $key)) {
            $numbers = explode(',', $key);
            return count(array_unique($numbers)) === count($numbers); // 중복 숫자 체크
        }

        // 범위 형식 (예: "4-6")
        if (preg_match('/^\d+-\d+$/', $key)) {
            list($start, $end) = explode('-', $key);
            return intval($start) < intval($end); // 시작 숫자가 끝 숫자보다 작은지 확인
        }

        return false;
    }

    public static function parseScoreTable(array $scoreData, int $totalQuestions = 0): array
    {
        $result = [
            'total_score' => 0,
            'table' => []
        ];

        // 모든 문항에 기본값 1 할당
        for ($i = 1; $i <= $totalQuestions; $i++) {
            $result['table'][$i] = 1;
            $result['total_score'] += 1;
        }

        // 배점표에 명시된 점수로 업데이트
        foreach ($scoreData as $key => $score) {
            $questionIndices = self::parseQuestionIndices($key);
            foreach ($questionIndices as $index) {
                // 총점에서 기존 점수(1)를 빼고 새로운 점수를 더함
                $result['total_score'] -= $result['table'][$index];
                $result['table'][$index] = intval($score);
                $result['total_score'] += intval($score);
            }
        }

        return $result;
    }

    /**
     * 문항 번호 문자열을 개별 인덱스 배열로 변환
     * @param string $key 문항 번호 문자열
     * @return array 개별 문항 번호 배열
     */
    public static function parseQuestionIndices(string $key): array
    {
        // 단일 숫자인 경우
        if (preg_match('/^\d+$/', $key)) {
            return [intval($key)];
        }

        // 콤마로 구분된 경우
        if (str_contains($key, ',')) {
            return array_map('intval', explode(',', $key));
        }

        // 범위인 경우
        if (str_contains($key, '-')) {
            list($start, $end) = array_map('intval', explode('-', $key));
            return range($start, $end);
        }

        return [];
    }

    #[On('onColorChanged')]
    function onColorChanged()
    {
        $this->dispatch('onPageMetaChanged', [
            'color' => $this->data['color'],
        ]);
    }

    public function backToQuestionSelection()
    {
        $this->state = 'question-selection';
    }
}
