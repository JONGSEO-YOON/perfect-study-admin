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

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        if (!\App\Models\Academy::isMenuGroupVisibleForCurrentUser('tests')) {
            return false;
        }
        return auth()->user()->userable instanceof \App\Models\Teacher;
    }

    /**
     * 문제 유형표(question_categories)의 고/고3 하위 1차 과목들을 옵션으로 반환.
     * 공통수학1/2, 대수, 미적분1/2, 확률과 통계, 기하, 이산수학 등 핵심만 포함.
     * (test/교과외/연산문제/내부 더미 등은 제외)
     */
    public static function getExamSubjectOptions(): array
    {
        $rootIds = QuestionCategory::where('depth', 0)
            ->whereRaw("REPLACE(REPLACE(name, '<p>', ''), '</p>', '') IN ('고', '고3')")
            ->pluck('id');

        $whitelist = [
            '공통수학1', '공통수학2',
            '대수', '미적분', '미적분1', '미적분2', '미적분 2',
            '확률과 통계', '확률과통계', '확통',
            '기하', '기하와 벡터',
            '이산수학',
            '수학 1', '수학1', '수학 2', '수학2',
        ];

        return QuestionCategory::where('depth', 1)
            ->whereIn('id', function ($q) use ($rootIds) {
                $q->select('descendant_id')
                    ->from('question_category_closure')
                    ->where('depth', 1)
                    ->whereIn('ancestor_id', $rootIds);
            })
            ->orderBy('id')
            ->pluck('name')
            ->map(fn($name) => trim(str_replace('(2025개정)', '', strip_tags(trim($name)))))
            ->map(fn($name) => trim(preg_replace('/\(.+?\)/', '', $name)))
            ->filter(fn($name) => $name !== '' && in_array($name, $whitelist))
            ->unique()
            ->mapWithKeys(fn($name) => [$name => $name])
            ->toArray();
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
        // 교재 문제인 경우 seq 처리
        // - 호출자가 명시적으로 seq를 넘겼으면(예: PDF 스캔) 그대로 보존하고 재정렬 안 함
        // - seq가 비었으면 boot creating에서 max+1로 자동 채워진 상태 → 재정렬 불필요
        // 기존 reorder 로직은 같은 초에 insert된 문제들의 created_at 동일성으로 인해
        // 순서가 무작위로 섞이는 부작용이 있어 제거.
        if ($question->material_id && !isset($data['seq']) && !$question->seq) {
            // 안전망: seq가 정말로 빠진 경우만 재정렬
            self::reorderQuestionSequences($question->material_id);
        }

        return $question;
    }

    /**
     * 교재의 문제 번호를 재정렬
     * - 1순위: 기존 seq (이미 부여된 순서 보존)
     * - 2순위: id (insert 순서)
     * - created_at은 같은 초에 다수 insert되면 순서가 보장되지 않으므로 사용하지 않음
     */
    private static function reorderQuestionSequences($materialId)
    {
        $questions = Question::where('material_id', $materialId)
            ->whereNull('parent_question_id')
            ->orderByRaw('COALESCE(seq, 999999) ASC')
            ->orderBy('id')
            ->get();

        foreach ($questions as $index => $question) {
            $newSeq = $index + 1;
            if ($question->seq !== $newSeq) {
                $question->timestamps = false;
                $question->update(['seq' => $newSeq]);
                $question->timestamps = true;
            }
        }
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
                ->heading('기출 정보')
                ->collapsible(true)
                ->collapsed(fn ($record) => $record && !$record->source_type)
                ->visible(fn(Get $get) => !$get('is_sub_question'))
                ->schema([
                    ToggleButtons::make('source_type')
                        ->label('문제 출처')
                        ->inline()
                        ->options([
                            '' => '일반 문제',
                            'mock_exam' => '모의고사 기출',
                            'school_exam' => '학교 기출',
                        ])
                        ->default('')
                        ->live()
                        ->afterStateHydrated(function ($component, $state) {
                            if ($state === null) {
                                $component->state('');
                            }
                        })
                        ->dehydrateStateUsing(fn ($state) => $state ?: null)
                        ->columnSpanFull(),

                    // === 모의고사 기출 필드 ===
                    Grid::make(4)
                        ->visible(fn(Get $get) => $get('source_type') === 'mock_exam')
                        ->schema([
                            TextInput::make('exam_year')
                                ->label('년도')
                                ->numeric()
                                ->minValue(1990)
                                ->maxValue((int) date('Y') + 1)
                                ->placeholder('예: 2024')
                                ->required(),
                            Select::make('exam_month')
                                ->label('월')
                                ->options([
                                    3 => '3월',
                                    4 => '4월',
                                    5 => '5월',
                                    6 => '6월',
                                    7 => '7월',
                                    9 => '9월',
                                    10 => '10월',
                                    11 => '11월 (수능)',
                                ])
                                ->required(),
                            Select::make('exam_grade')
                                ->label('학년')
                                ->options([
                                    '고1' => '고1',
                                    '고2' => '고2',
                                    '고3' => '고3',
                                ])
                                ->required(),
                            Select::make('exam_subject')
                                ->label('과목 (선택)')
                                ->options(fn() => self::getExamSubjectOptions())
                                ->searchable()
                                ->placeholder('미선택')
                                ->nullable(),
                            TextInput::make('exam_series')
                                ->label('문제 계열 (선택, 직접 입력 가능)')
                                ->datalist(fn() => \App\Models\ExamSeries::activeNames())
                                ->placeholder('예: 가형, 미적분, 또는 신규 명칭 직접 입력')
                                ->maxLength(50)
                                ->nullable(),
                            TextInput::make('exam_score')
                                ->label('배점')
                                ->numeric()
                                ->step(0.5)
                                ->minValue(0.5)
                                ->placeholder('예: 2, 2.5, 3, 4')
                                ->required(),
                            TextInput::make('exam_question_number')
                                ->label('문제 번호')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(45)
                                ->placeholder('예: 21'),
                        ]),

                    // === 학교 기출 필드 ===
                    Grid::make(4)
                        ->visible(fn(Get $get) => $get('source_type') === 'school_exam')
                        ->schema([
                            Select::make('school_id')
                                ->label('학교')
                                ->searchable()
                                ->getSearchResultsUsing(fn (string $search): array =>
                                    \App\Models\School::where('name', 'like', "%{$search}%")
                                        ->limit(50)
                                        ->pluck('name', 'id')
                                        ->toArray()
                                )
                                ->getOptionLabelUsing(fn ($value): ?string =>
                                    \App\Models\School::find($value)?->name
                                )
                                ->required()
                                ->columnSpan(2),
                            TextInput::make('exam_year')
                                ->label('년도')
                                ->numeric()
                                ->minValue(1990)
                                ->maxValue((int) date('Y') + 1)
                                ->placeholder('예: 2024')
                                ->required(),
                            Select::make('exam_semester')
                                ->label('학기')
                                ->options([
                                    1 => '1학기',
                                    2 => '2학기',
                                ])
                                ->required(),
                            Select::make('exam_subject')
                                ->label('과목 (선택)')
                                ->options(fn() => self::getExamSubjectOptions())
                                ->searchable()
                                ->placeholder('미선택')
                                ->nullable(),
                            Select::make('exam_type')
                                ->label('시험 유형')
                                ->options([
                                    'midterm' => '중간고사',
                                    'final' => '기말고사',
                                ])
                                ->required(),
                            Select::make('exam_grade')
                                ->label('학년')
                                ->options([
                                    '고1' => '고1',
                                    '고2' => '고2',
                                    '고3' => '고3',
                                ]),
                            TextInput::make('exam_question_number')
                                ->label('문제 번호')
                                ->numeric()
                                ->minValue(1)
                                ->placeholder('예: 15'),
                        ]),
                ]),
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
                ->placeholder('교재를 선택하세요.')
                ->afterStateUpdated(function ($state, Set $set) {
                    // 교재 선택 시 자동 번호 설정 제거 (사용자가 직접 입력하도록)
                }),
            TextInput::make('seq')
                ->label('문제번호')
                // ->required()
                ->numeric()
                ->visible(fn(Get $get) => $get('material_id'))
                ->placeholder(fn($component) => $component->getRecord() ? null : '비어두면 자동 정렬됩니다')
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, Get $get, $component) {
                    if (!$state) {
                        return;
                    }

                    $materialId = $get('material_id');
                    if (!$materialId) {
                        return;
                    }

                    $query = \App\Models\Question::where('material_id', $materialId)
                        ->where('seq', $state);

                    // 수정 중인 경우 현재 레코드 제외
                    if ($record = $component->getRecord()) {
                        $query->where('id', '!=', $record->id);
                    }

                    if ($query->exists()) {
                        // 알림 보내기
                        Notification::make()
                            ->danger()
                            ->title('중복된 문제번호')
                            ->body('이 교재에 이미 등록된 문제번호입니다.')
                            ->send();

                        // 필드에 에러 표시 (빨간색)
                        $component->state($state);
                    }
                })
                ->rules([
                    fn(Get $get, $component): \Closure => function (string $attribute, $value, \Closure $fail) use ($get, $component) {
                        if (!$value) {
                            return;
                        }

                        $materialId = $get('material_id');
                        if (!$materialId) {
                            return;
                        }

                        $query = \App\Models\Question::where('material_id', $materialId)
                            ->where('seq', $value);

                        // 수정 중인 경우 현재 레코드 제외
                        if ($record = $component->getRecord()) {
                            $query->where('id', '!=', $record->id);
                        }

                        if ($query->exists()) {
                            $fail('이 교재에 이미 등록된 문제번호입니다.');
                        }
                    },
                ]),
            // ->afterStateHydrated(function ($component, $state, Get $get) {
            //     // 새 레코드이고 seq가 비어있고 material_id가 있는 경우에만 자동 설정
            //     if (!$state && !$component->getRecord()?->exists && $get('material_id')) {
            //         $materialId = $get('material_id');
            //         $maxSeq = \App\Models\Question::where('material_id', $materialId)->max('seq') ?? 0;
            //         $component->state($maxSeq + 1);
            //     }
            // }),

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
                $query->where('parent_question_id', null);

                // root_admin이 아니면 기출 문제에 공유 규칙 적용
                if (auth()->user()->role !== 'root_admin' && auth()->user()->academy_id) {
                    $academyId = auth()->user()->academy_id;

                    $query->where(function ($q) use ($academyId) {
                        // 기출이 아닌 문제: 전체 공개
                        $q->whereNull('source_type')
                            // 자기 학원 기출
                            ->orWhere(function ($sub) use ($academyId) {
                                $sub->whereNotNull('source_type')
                                    ->where('questions.academy_id', $academyId);
                            })
                            // 공유 규칙으로 허용된 다른 학원 기출
                            ->orWhere(function ($sub) use ($academyId) {
                                $sharingRules = \App\Models\ExamSharingRule::where('academy_id', $academyId)
                                    ->where('is_allowed', true)
                                    ->get();

                                if ($sharingRules->isEmpty()) return;

                                foreach ($sharingRules as $rule) {
                                    $sub->orWhere(function ($r) use ($rule) {
                                        $r->where('questions.source_type', $rule->source_type);
                                        if ($rule->exam_year) $r->where('questions.exam_year', $rule->exam_year);
                                        if ($rule->exam_month) $r->where('questions.exam_month', $rule->exam_month);
                                        if ($rule->exam_semester) $r->where('questions.exam_semester', $rule->exam_semester);
                                        if ($rule->exam_type) $r->where('questions.exam_type', $rule->exam_type);
                                        if ($rule->exam_subject) $r->where('questions.exam_subject', $rule->exam_subject);
                                        if ($rule->school_id) $r->where('questions.school_id', $rule->school_id);
                                    });
                                }
                            });
                    });
                }

                return $query;
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
                TextColumn::make('source_type')
                    ->label('출처')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'mock_exam' => '모의고사',
                        'school_exam' => '학교기출',
                        default => '',
                    })
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'mock_exam' => 'info',
                        'school_exam' => 'success',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('exam_year')
                    ->label('출제년도')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    }),
                Filter::make('source_type')
                    ->form([
                        Select::make('source_type')
                            ->label('문제 출처')
                            ->options([
                                'mock_exam' => '모의고사 기출',
                                'school_exam' => '학교 기출',
                            ])
                            ->placeholder('전체'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['source_type'] ?? null,
                            fn(Builder $query, $type) => $query->where('source_type', $type)
                        );
                    }),
                Filter::make('exam_year')
                    ->form([
                        Select::make('exam_year')
                            ->label('출제 년도')
                            ->options(array_combine(
                                range(date('Y'), 2010, -1),
                                range(date('Y'), 2010, -1)
                            ))
                            ->placeholder('전체'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['exam_year'] ?? null,
                            fn(Builder $query, $year) => $query->where('exam_year', $year)
                        );
                    }),

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
                        ->after(function ($record) {
                            // 교재가 있는 문제인 경우 seq 재정리
                            if ($record->material_id) {
                                $questions = \App\Models\Question::where('material_id', $record->material_id)
                                    ->orderBy('seq')
                                    ->get();

                                foreach ($questions as $index => $question) {
                                    $question->update(['seq' => $index + 1]);
                                }
                            }
                        })

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
