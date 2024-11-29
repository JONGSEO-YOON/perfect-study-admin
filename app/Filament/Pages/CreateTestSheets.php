<?php

namespace App\Filament\Pages;

use App\Models\Classroom;
use App\Models\GradeSystem;
use App\Models\Question;
use App\Models\Student;
use App\Models\TempData;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\StaticAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
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
use Livewire\Attributes\On;

class CreateTestSheets extends Page implements HasForms, HasActions
{
    use InteractsWithForms, InteractsWithActions;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $slug = 'test-sheets/create/{id}';

    protected static string $view = 'filament.pages.create-test-sheets';

    protected static bool $shouldRegisterNavigation = false;

    protected ?string $maxContentWidth = '6xl';

    protected static ?string $title = '문제 등록';

    public $id;

    public $arguments = [];

    public $query = null;

    public $questions = null;

    public $summary = null;

    public $state = "question-selection";

    public $data = [
        'name' => null,
        'tags' => [],
        'target_group' => 'grade',
        'target_grades' => [],
        'target_levels' => [],
        'target_classrooms' => [],
        'target_students' => [],
        'is_auto' => true,
        'start_date' => null,
        'end_date' => null,

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
                    ToggleButtons::make('status')
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
                    Grid::make(4)
                        ->schema([
                            Radio::make('target_group')
                                ->label('출제 대상')
                                ->required()
                                ->live()
                                ->reactive()
                                ->options([
                                    'grade' => '학년',
                                    'level' => '레벨',
                                    'classroom' => '교실/반',
                                    'student' => '학생',
                                ])
                                ->default('grade')
                                ->columns(4)
                                ->columnSpanFull()
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
                            Select::make('target_students')
                                ->label('학생')
                                ->multiple()
                                ->required()
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
                                ->visible(fn(Get $get) => $get('target_group') === 'student'),
                        ]),
                    Checkbox::make('is_auto')
                        ->default(true)
                        ->live()
                        ->label('자동 출제'),
                    DatePicker::make('start_date')
                        ->label('출제일')
                        ->columnStart(1)
                        ->columnSpan(2)
                        ->visible(fn(Get $get) => $get('is_auto'))
                        ->required(),
                    DatePicker::make('end_date')
                        ->label('마감일')
                        ->visible(fn(Get $get) => $get('is_auto'))
                        ->columnSpan(2)
                        ->required(),
                ]),
            Section::make('문제지 템플릿')
                ->label('문제지 템플릿')
                ->heading('2. 문제지 템플릿')
                ->columns(4)
                ->schema([
                    ColorPicker::make('color')
                        ->label('색상')
                        ->columnSpan(1)
                        ->default('#000000'),
                ])

        ])
            ->statePath('data');
    }

    public function mount($id)
    {
        $query = TempData::findOrFail($id)?->value;
        // $questions = self::selectRandomQuestions($query);
        $this->questions = self::selectRandomQuestions($query);
        $this->summary = self::getDistributionSummary($this->questions);
        // dd($query, $summary, $questions);
        $this->id = $id;
        $this->query = $query;
    }

    public static function selectRandomQuestions(array $params): Collection
    {
        $totalQuestionCount = $params['question_count'];
        $questionTypeIds = $params['question_type_ids'];
        $isEvenDistribution = $params['is_even_distribution'];
        $excludeIds = $params['exclude_ids'] ?? []; // 제외할 ID 목록, 없으면 빈 배열
        $result = collect();

        if ($isEvenDistribution) {
            // Even distribution case: 모든 레벨에서 동일한 수의 문제 추출
            $levels = $params['levels'];
            $questionsPerLevel = (int) floor($totalQuestionCount / count($levels));
            $remainingQuestions = $totalQuestionCount % count($levels);

            foreach ($levels as $levelIndex => $level) {
                // 이 레벨에서 가져올 총 문제 수
                $levelQuestionCount = $questionsPerLevel + ($levelIndex < $remainingQuestions ? 1 : 0);
                // 각 타입별로 가져올 문제 수 계산
                $questionsPerType = (int) floor($levelQuestionCount / count($questionTypeIds));
                $remainingTypeQuestions = $levelQuestionCount % count($questionTypeIds);

                foreach ($questionTypeIds as $typeIndex => $typeId) {
                    $typeQuestionCount = $questionsPerType + ($typeIndex < $remainingTypeQuestions ? 1 : 0);
                    if ($typeQuestionCount > 0) {
                        $questions = Question::where('question_type_id', $typeId)
                            ->where('level', $level)
                            ->whereNotIn('id', $excludeIds) // 제외할 ID 필터링 추가
                            ->with('questionType')
                            ->inRandomOrder()
                            ->take($typeQuestionCount)
                            ->get();
                        $result = $result->concat($questions);
                    }
                }
            }
        } else {
            // Weighted distribution case: 가중치에 따라 문제 추출
            $levelWeights = $params['level'];
            $totalWeight = array_sum($levelWeights);
            // 각 레벨별 문제 수 계산 (전체 문제 수에서 가중치 비율대로)
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

            // 각 레벨별로 문제 추출
            foreach ($levelQuestionCounts as $level => $count) {
                if ($count > 0) {
                    // 이 레벨에서 각 타입별로 가져올 문제 수 계산
                    $questionsPerType = (int) floor($count / count($questionTypeIds));
                    $remainingTypeQuestions = $count % count($questionTypeIds);

                    foreach ($questionTypeIds as $typeIndex => $typeId) {
                        $typeQuestionCount = $questionsPerType + ($typeIndex < $remainingTypeQuestions ? 1 : 0);
                        if ($typeQuestionCount > 0) {
                            $questions = Question::where('question_type_id', $typeId)
                                ->where('level', $level)
                                ->whereNotIn('id', $excludeIds) // 제외할 ID 필터링 추가
                                ->with('questionType')
                                ->inRandomOrder()
                                ->take($typeQuestionCount)
                                ->get();
                            $result = $result->concat($questions);
                        }
                    }
                }
            }
        }

        return $result;
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
    }

    public function removeQuestion($questionId)
    {
        $this->questions = $this->questions->reject(function ($question) use ($questionId) {
            return $question->id == $questionId;
        });
        $this->summary = self::getDistributionSummary($this->questions);
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
            ->modalWidth('4xl')
            ->icon('heroicon-m-plus-circle')
            ->label('새 문제 추가')
            ->modalWidth('2xl')
            ->modalSubmitActionLabel('추가하기')
            ->fillForm(function () {

                $levels = $this->query['levels'];
                $questionTypeIds = $this->query['question_type_ids'];
                if ($this->arguments['question']['question_type_id'] ?? false) {
                    $questionTypeIds = [$this->arguments['question']['question_type_id']];
                }
                if ($this->arguments['question']['level'] ?? false) {
                    $levels = [$this->arguments['question']['level']];
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

                        ViewField::make('questions')
                            ->label('문제 목록')
                            ->reactive()
                            ->view('filament.components.forms.question-list')
                            ->live()
                            ->columnSpanFull(),
                        Hidden::make('question_ids')
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                dd($get('question_ids'));
                            })
                            ->default([])
                            ->required()
                            ->columnSpanFull(),
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
        $this->questions = $this->questions->concat($questions);
        Notification::make()
            ->title('문제 추가 완료')
            ->success()
            ->send();
    }
}
