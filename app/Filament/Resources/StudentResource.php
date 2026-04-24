<?php

namespace App\Filament\Resources;

use AddressInfo;
use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers;
use App\Forms\Components\AddressInput;
use App\Forms\Components\PhoneInput;
use App\Models\Classroom;
use App\Models\GradeSystem;
use App\Models\School;
use App\Models\Student;
use App\Filament\Resources\WithdrawnStudentResource;
use App\Models\TestSheet;
use App\Models\WrongAnswerNote;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;
use Psy\VersionUpdater\Checker;
use Closure;
use Filament\Forms\Get;
use App\Models\User;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    // protected static ?string $navigationIcon = 'heroicon-m-academic-cap';

    protected static ?string $navigationGroup = '교실 관리';

    protected static ?int $navigationSort = 1; // 교실 관리

    protected static ?string $navigationLabel = '학생 관리';

    public static function canViewAny(): bool
    {
        return \App\Models\Academy::isMenuGroupVisibleForCurrentUser('classroom');
    }

    public static function getBreadcrumb(): string
    {
        return '';
    }

    /**
     * 전화번호 유효성 검사
     * 
     * @param array $value 전화번호 배열 [첫번째, 두번째, 세번째]
     * @return array ['is_valid' => bool, 'message' => string]
     */
    static function validatePhoneNumber($value)
    {
        // $value가 배열인지 확인
        if (!is_array($value)) {
            return ['is_valid' => false, 'message' => '전화번호 형식이 올바르지 않습니다.'];
        }

        // 배열의 길이가 3인지 확인
        if (count($value) !== 3) {
            return ['is_valid' => false, 'message' => '전화번호 형식이 올바르지 않습니다.'];
        }
        $number = '';
        if ($value[0] === '010') {
            $number = '4';
        } else {
            $number = '3,4';
        }

        // 두 번째 요소가 3자리 또는 4자리 숫자인지 확인
        if (!preg_match('/^\d{' . $number . '}$/', $value[1])) {
            return ['is_valid' => false, 'message' => '전화번호 중간 자리는 ' . $number . '자리 숫자여야 합니다.'];
        }

        // 세 번째 요소가 4자리 숫자인지 확인
        if (!preg_match('/^\d{4}$/', $value[2])) {
            return ['is_valid' => false, 'message' => '전화번호 마지막 자리는 4자리 숫자여야 합니다.'];
        }

        return ['is_valid' => true, 'message' => ''];
    }

    public static function _form(bool $simplified = false): array
    {
        return [
            //
            Hidden::make('privacy')
                ->default(false)
                ->dehydrated(false),
            Grid::make(2)
                ->schema([
                    FileUpload::make('profile_photo_path')
                        ->extraAttributes([
                            'class' => '!items-center'
                        ])
                        ->label('사진')
                        ->image()
                        ->avatar()
                        ->placeholder('사진 업로드')
                        ->hidden($simplified)
                        ->columnSpanFull(),
                    TextInput::make('name')
                        ->label('이름')
                        ->required(),
                    DatePicker::make('birthed_at')
                        ->label('생년월일')
                        ->required(),
                    Grid::make(2)
                        ->schema([
                            Radio::make('gender')
                                ->label('성별')
                                ->inlineLabel()
                                ->inline()
                                ->required()
                                ->options([
                                    '남' => '남',
                                    '여' => '여',
                                ]),
                        ]),
                    Hidden::make('address'),
                    Hidden::make('postal_code'),
                    AddressInput::make('address-input')
                        ->label('주소')
                        ->columnSpanFull(),
                    Grid::make(2)
                        ->schema([
                            Select::make('school_id')
                                ->label('학교')
                                ->nullable()
                                ->options(function () {
                                    return School::query()
                                        ->orderBy('name')
                                        ->pluck('name', 'id');
                                })
                                ->getSearchResultsUsing(fn(string $search): array => School::where('name', 'like', "%{$search}%")->limit(10)
                                    ->get()
                                    ->map(function ($school) {
                                        return [
                                            'id' => $school->id,
                                            'name' => $school->name . ' - ' . $school->province
                                        ];
                                    })
                                    ->pluck('name', 'id')->toArray())
                                ->searchable()
                                ->preload(),
                            Select::make('grade_system_id')
                                ->label('학년')
                                ->required()
                                ->options(function () {
                                    return GradeSystem::query()
                                        ->orderBy('sequential_order')
                                        ->pluck(
                                            'display_name',
                                            'id',
                                        );
                                }),
                        ])->relationship('userable'),
                    TextInput::make('email')
                        ->label('이메일'),
                    Grid::make(2)
                        ->schema([
                            PhoneInput::make('phone')
                                ->label('전화번호 (본인)')
                                ->required()
                                ->rules([
                                    fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                        $result = self::validatePhoneNumber($value);
                                        if (!$result['is_valid']) {
                                            $fail($result['message']);
                                        }

                                        $phone = implode('-', $value);
                                        $query = User::where('phone', $phone)->where('id', '!=', $get('id'));
                                        // 현재 접속한 학원 기준으로 중복 체크
                                        $academyId = (app()->has('current_academy') && app('current_academy'))
                                            ? app('current_academy')->id
                                            : auth()->user()->academy_id;
                                        if ($academyId) {
                                            $query->where('academy_id', $academyId);
                                        }
                                        if ($query->exists()) {
                                            $fail('이미 존재하는 전화번호입니다.');
                                        }
                                    },
                                ]),
                            PhoneInput::make('landline')
                                ->label('전화번호 (자택)'),
                        ])
                        ->hidden(fn($get) => $get('../privacy')),
                    Grid::make(2)
                        ->hidden(fn($get) => $get('../privacy'))
                        ->schema([
                            PhoneInput::make('phone_mother')
                                ->label('전화번호 (모)')
                                ->rules([
                                    fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                        if (!self::validatePhoneNumber($get('phone_father'))['is_valid']) {
                                            $result = self::validatePhoneNumber($value);
                                            if (!$result['is_valid']) {
                                                $fail('부모님 전화번호 중 하나는 입력해야합니다.');
                                            }
                                        }
                                    },
                                ]),
                            PhoneInput::make('phone_father')
                                ->label('전화번호 (부)')
                                ->rules([
                                    fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                        if (!self::validatePhoneNumber($get('phone_mother'))['is_valid']) {
                                            $result = self::validatePhoneNumber($value);
                                            if (!$result['is_valid']) {
                                                $fail('부모님 전화번호 중 하나는 입력해야합니다.');
                                            }
                                        }
                                    },
                                ]),
                            Toggle::make('sms_agree')
                                ->label('SMS 수신 여부')
                                ->inlineLabel()
                                ->inline()
                                ->columnSpanFull()
                                ->default(true),
                            CheckboxList::make('sms_targets')
                                ->label('SMS 수신 대상')
                                ->inlineLabel()
                                ->columnSpanFull()
                                ->options([
                                    'self' => '본인',
                                    'father' => '부',
                                    'mother' => '모',
                                ])
                                // ->default(fn() => ['self'])
                                ->columns(3),
                        ])->relationship('userable'),

                    Grid::make(4)
                        ->schema([
                            Select::make('cash_receipt_type')
                                ->label('현금영수증 종류')
                                ->options([
                                    '개인' => '개인',
                                    '사업자' => '사업자',
                                ]),
                            TextInput::make('cash_receipt_no')
                                ->label('현금영수증 번호')
                                ->columnSpan(3),
                        ])
                        ->relationship('userable'),
                    Grid::make(2)
                        ->schema([
                            DatePicker::make('initially_attended_at')
                                ->label('최초 수강일')
                        ])
                        ->relationship('userable'),
                    KeyValue::make('meta')
                        ->keyLabel('정보')
                        ->valueLabel('입력')
                        ->label('추가 정보')
                        ->default([
                            '과목별 내신 등급' => '',
                            '모의고사 등급' => '',
                            '수강료 할인유형' => '',
                        ])
                        ->columnSpanFull(),
                    Textarea::make('remark')
                        ->label('비고')
                        ->columnSpanFull(),
                    FileUpload::make('attachments')
                        ->label('첨부 파일')
                        ->multiple()
                        ->placeholder('클릭하거나 파일을 드래그하여 업로드')
                        ->previewable(false)
                        ->downloadable(true)
                        ->columnSpanFull()
                ])
                ->relationship('user')
        ];
    }


    public static function form(Form $form): Form
    {
        return
            $form
            ->schema(self::_form(false));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(function ($query) {
                // 퇴원생은 퇴원생 관리에서 조회
                $query->where('status', '!=', 'withdrawn');

                if (!auth()->user()->isRoleAbove('manager', true)) {
                    $query->whereHas('classrooms', function ($q) {
                        $q->where('classrooms.teacher_id', auth()->user()->userable->id)
                            ->orWhere('classrooms.sub_teacher_id', auth()->user()->userable->id);
                    });
                }
                return $query;
            })
            ->columns([
                //
                TextColumn::make('id')
                    ->label('No')
                    ->rowIndex(),
                TextColumn::make('user.name')
                    ->label('이름')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('school.name')
                    ->label('학교')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('academy.name')
                    ->label('소속 학원')
                    ->visible(fn () => auth()->user()->role === 'root_admin'),
                TextColumn::make('gradeSystem.sequential_order')
                    ->label('학년')
                    ->formatStateUsing(function ($record) {
                        return $record->gradeSystem->display_name;
                    })
                    ->sortable(),
                TextColumn::make('user.phone')
                    ->label('전화번호')
                    ->searchable()
                    ->sortable()
                    ->visible(
                        fn($livewire) => auth()->user()->isRoleAbove('admin', true)
                            || !auth()->user()->userable instanceof \App\Models\Teacher
                            || Classroom::find($livewire?->tableFilters['classroom_id']['value'] ?? null)
                            ?->teacher_id == auth()->user()->userable->id
                    )
                    ->html()
                    ->formatStateUsing(function ($record) {

                        return new HtmlString(<<<EOF
                        <div>
                            <div class="rounded bg-gray-100 border inline-flex px-2 py-0.5 text-xs text-gray-600">본인</div> {$record->user->phone} <br/>
                            <div class="rounded bg-gray-100 border inline-flex px-2 py-0.5 text-xs text-gray-600">부</div> {$record->phone_father} <br/>
                            <div class="rounded bg-gray-100 border inline-flex px-2 py-0.5 text-xs text-gray-600">모</div> {$record->phone_mother} <br/>
                        </div>
                        EOF);
                    }),
                // TextColumn::make('user.birthed_at')
                //     ->date('Y-m-d')
                //     ->label('생년월일')
                //     ->sortable(),
                TextColumn::make('status')
                    ->label('상태')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active', 'enrolled' => 'success',
                        'pending' => 'warning',
                        'withdrawn' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'active', 'enrolled' => '재원',
                        'pending' => '승인예정',
                        'withdrawn' => '퇴원',
                        default => $state,
                    })
                    ->url(fn($record) => $record->status === 'withdrawn'
                        ? WithdrawnStudentResource::getUrl('index')
                        : null),
                TextColumn::make('created_at')
                    ->date('Y-m-d')
                    ->label('등록일')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('classroom_id')
                    ->label('반')
                    ->options(function () {
                        return Classroom::query()
                            ->orderBy('name')
                            ->pluck('name', 'id');
                    })
                    ->query(function (Builder $query, $data) {
                        $classroomId = $data["value"] ?? null;
                        $query
                            ->when($classroomId, function ($query, $classroomId) {
                                $query->whereHas('classrooms', function ($q) use ($classroomId) {
                                    $q->where('classrooms.id', $classroomId);
                                });
                            });
                    }),
                SelectFilter::make('status')
                    ->label('상태')
                    ->options([
                        'enrolled' => '재원',
                        'pending' => '승인대기',
                    ]),
                SelectFilter::make('grade_system_id')
                    ->label('학년')
                    ->options(fn () => GradeSystem::orderBy('sequential_order')->pluck('display_name', 'id')->toArray())
                    ->searchable()
                    ->preload(),
                SelectFilter::make('school_id')
                    ->label('학교')
                    ->options(fn () => School::orderBy('name')->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload(),
                SelectFilter::make('academy_id')
                    ->label('소속 학원')
                    ->options(fn () => \App\Models\Academy::pluck('name', 'id')->toArray())
                    ->visible(fn () => auth()->user()->role === 'root_admin'),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\Action::make('attendance')
                    ->label('출석')
                    ->icon('heroicon-m-calendar')
                    ->modalSubmitAction(false)
                    ->modalContent(fn($record) => view('filament.components.modals.attendance', [
                        'shareLink' =>  route("attendance-calendar", [
                            "studentData" => base64_encode(json_encode([
                                'student_id' => $record->id,
                                'name' => $record->user->name
                            ]))
                        ]),
                    ]))
                    ->modalWidth('7xl'),
                Tables\Actions\Action::make('view-report-card')
                    ->label('성적표')
                    ->icon('heroicon-m-newspaper')
                    ->modalHeading('성적표 조회')
                    ->modalSubmitAction(false)
                    ->modalContent(fn($record, $livewire) => view('filament.components.modals.student-report-card', [
                        'record' => $record,
                        'classroomId' => $livewire->tableFilters['classroom_id']['value'] ?? null,
                    ]))
                    ->modalWidth('7xl'),

                Tables\Actions\EditAction::make()
                    ->modalHeading('학생 수정하기')
                    ->fillForm(function ($record, $livewire) {
                        return array_merge($record->toArray(), [
                            'privacy' => !$record->canEdit(auth()->user())
                                && Classroom::find($livewire?->tableFilters['classroom_id']['value'] ?? null)
                                ?->teacher_id !== auth()->user()->userable->id,
                        ]);
                    })
                    ->label(function ($record) {
                        if ($record->canEdit(auth()->user())) {
                            return '수정';
                        }
                        return '조회';
                    })
                    ->icon(function ($record) {
                        if ($record->canEdit(auth()->user())) {
                            return 'heroicon-m-pencil-square';
                        }
                        return 'heroicon-m-eye';
                    })
                    ->modalSubmitAction(function ($record) {
                        if (!$record->canEdit(auth()->user())) {
                            return false;
                        }
                    })
                    ->before(function ($record) {
                        // 수정 전 원본 데이터 스냅샷 보관 (변경사항 추적용)
                        $record->_originalData = $record->getOriginal();
                        $record->_originalUserName = $record->user?->name;
                        $record->_originalSchoolId = $record->school_id;
                        $record->_originalGradeSystemId = $record->grade_system_id;
                    })
                    ->after(function ($record, array $data) {
                        // 변경된 필드 감지
                        $changes = [];
                        $fieldLabels = [
                            'user.name' => '이름',
                            'user.phone' => '본인 전화번호',
                            'school_id' => '학교',
                            'grade_system_id' => '학년',
                            'phone_father' => '부 전화번호',
                            'phone_mother' => '모 전화번호',
                            'initially_attended_at' => '최초 등원일',
                            'sms_agree' => 'SMS 동의',
                            'remark' => '비고',
                        ];

                        $original = $record->_originalData ?? [];
                        foreach ($fieldLabels as $key => $label) {
                            if (str_starts_with($key, 'user.')) continue; // user 필드는 별도 처리
                            $oldValue = $original[$key] ?? null;
                            $newValue = $record->fresh()->$key ?? null;
                            if ((string)$oldValue !== (string)$newValue) {
                                $changes[] = "{$label}: '" . ($oldValue ?: '-') . "' → '" . ($newValue ?: '-') . "'";
                            }
                        }

                        // user 이름 변경 감지
                        $newUserName = $record->fresh()->user?->name;
                        if (($record->_originalUserName ?? null) !== $newUserName && $newUserName) {
                            $changes[] = "이름: '" . ($record->_originalUserName ?: '-') . "' → '{$newUserName}'";
                        }

                        if (!empty($changes)) {
                            \App\Models\StudentStatusHistory::create([
                                'student_id' => $record->id,
                                'changed_by' => auth()->id(),
                                'event_type' => 'updated',
                                'from_status' => $record->status,
                                'to_status' => $record->status,
                                'reason' => '정보 수정',
                                'memo' => implode(', ', $changes),
                            ]);
                        }
                    })
                    ->modalWidth('xl'),

                Tables\Actions\Action::make('withdraw')
                    ->label('퇴원 처리')
                    ->icon('heroicon-m-user-minus')
                    ->color('danger')
                    ->visible(fn() => in_array(auth()->user()->role, ['root_admin', 'admin'])
                        || auth()->user()->role === 'counselor')
                    ->requiresConfirmation()
                    ->modalHeading('퇴원 처리')
                    ->form([
                        Select::make('withdrawal_reason')
                            ->label('퇴원 사유')
                            ->options([
                                'poor_performance' => '성적부진',
                                'change_of_atmosphere' => '분위기전환',
                                'teacher_mismatch' => '선생님맞지않음',
                                'academy_atmosphere' => '학원분위기안좋음',
                                'relocation' => '이사',
                                'other' => '기타',
                            ])
                            ->required()
                            ->reactive(),
                        TextInput::make('withdrawal_reason_detail')
                            ->label('기타 사유')
                            ->visible(fn(\Filament\Forms\Get $get) => $get('withdrawal_reason') === 'other'),
                        DatePicker::make('withdrawn_at')
                            ->label('퇴원 일자')
                            ->required()
                            ->default(now()),
                    ])
                    ->action(function ($record, array $data) {
                        $oldStatus = $record->status;

                        // 퇴원 전 소속 반+담임 정보를 히스토리에 기록
                        $classroomInfo = $record->classrooms()
                            ->with('teacher.user')
                            ->get()
                            ->map(fn($c) => $c->name . ' (담임: ' . ($c->teacher?->user?->name ?? '-') . ')')
                            ->join(', ');

                        $record->update([
                            'status' => 'withdrawn',
                            'withdrawal_reason' => $data['withdrawal_reason'],
                            'withdrawal_reason_detail' => $data['withdrawal_reason_detail'] ?? null,
                            'withdrawn_at' => $data['withdrawn_at'],
                        ]);

                        \App\Models\StudentStatusHistory::create([
                            'student_id' => $record->id,
                            'changed_by' => auth()->id(),
                            'event_type' => 'withdrawn',
                            'from_status' => $oldStatus,
                            'to_status' => 'withdrawn',
                            'reason' => $data['withdrawal_reason'] === 'other'
                                ? '기타: ' . ($data['withdrawal_reason_detail'] ?? '')
                                : [
                                    'poor_performance' => '성적부진',
                                    'change_of_atmosphere' => '분위기전환',
                                    'teacher_mismatch' => '선생님맞지않음',
                                    'academy_atmosphere' => '학원분위기안좋음',
                                    'relocation' => '이사',
                                ][$data['withdrawal_reason']] ?? $data['withdrawal_reason'],
                            'memo' => $classroomInfo ? "소속 반: {$classroomInfo}" : null,
                        ]);

                        Notification::make()->title('퇴원 처리되었습니다.')->success()->send();
                    }),

                ActionGroup::make([
                    Tables\Actions\Action::make('print-wrong-notes')
                        ->label('오답 노트 출력')
                        ->icon('heroicon-m-printer')
                        ->modalHeading('오답 노트 출력')
                        ->modalSubmitActionLabel('출력')
                        ->modalWidth('xl')
                        ->fillForm(fn($record) => [
                            'from' => now()->subMonth()->format('Y-m-d'),
                            'to' => now()->format('Y-m-d'),
                            'is_dont_know_only' => false,
                            'question_count' => count(WrongAnswerNote::getQuestions(
                                $record->id,
                                now()->subMonth()->format('Y-m-d'),
                                now()->format('Y-m-d')
                            )),
                        ])
                        ->form([
                            Grid::make(2)
                                ->schema([
                                    DatePicker::make('from')
                                        ->live()
                                        ->label('출제 시작일')
                                        ->required()
                                        ->afterStateUpdated(function ($get, $set, $record) {
                                            $from = Carbon::parse($get('from'))->startOfDay();
                                            $to = Carbon::parse($get('to'))->endOfDay();
                                            $isDontKnowOnly = $get('is_dont_know_only');
                                            $set(
                                                'question_count',
                                                count(WrongAnswerNote::getQuestions(
                                                    $record->id,
                                                    $from,
                                                    $to,
                                                    $isDontKnowOnly
                                                ))
                                            );
                                        }),
                                    DatePicker::make('to')
                                        ->live()
                                        ->label('출제 종료일')
                                        ->required()
                                        ->afterStateUpdated(function ($get, $set, $record) {
                                            $from = Carbon::parse($get('from'))->startOfDay();
                                            $to = Carbon::parse($get('to'))->endOfDay();
                                            $isDontKnowOnly = $get('is_dont_know_only');
                                            $set(
                                                'question_count',
                                                count(WrongAnswerNote::getQuestions(
                                                    $record->id,
                                                    $from,
                                                    $to,
                                                    $isDontKnowOnly
                                                ))
                                            );
                                        }),
                                    Toggle::make('is_dont_know_only')
                                        ->label('[잘 모르겠음] 문제만 출력')
                                        ->columnSpanFull()
                                        ->inlineLabel()
                                        ->inline()
                                        ->reactive()
                                        ->live()
                                        ->afterStateUpdated(function ($get, $set, $record) {
                                            $from = Carbon::parse($get('from'))->startOfDay();
                                            $to = Carbon::parse($get('to'))->endOfDay();
                                            $isDontKnowOnly = $get('is_dont_know_only');
                                            $set(
                                                'question_count',
                                                count(WrongAnswerNote::getQuestions(
                                                    $record->id,
                                                    $from,
                                                    $to,
                                                    $isDontKnowOnly
                                                ))
                                            );
                                        })
                                        ->default(false),
                                    TextInput::make('question_count')
                                        ->numeric()
                                        ->label('출제 문제 수')
                                        ->readOnly()
                                        ->disabled()
                                        ->required(),
                                ])
                        ])
                        ->action(function ($record, $data) {
                            $from = Carbon::parse($data['from'])->startOfDay();
                            $to = Carbon::parse($data['to'])->endOfDay();
                            $isDontKnowOnly = $data['is_dont_know_only'];
                            $questionCount = WrongAnswerNote::where('student_id', $record->id)
                                ->whereBetween('created_at', [$from, $to])
                                ->count();
                            if ($questionCount === 0) {
                                Notification::make()
                                    ->title('출제될 문제가 없습니다.')
                                    ->warning()
                                    ->send();
                                return;
                            }
                            return redirect('/admin/test-sheet/print?student_id=' . $record->id . '&from=' . $data['from'] . '&to=' . $data['to'] . '&is_dont_know_only=' . $isDontKnowOnly);
                        }),

                    Tables\Actions\Action::make('create-wrong-note-test')
                        ->label('오답 노트 출제')
                        ->icon('heroicon-m-document-arrow-up')
                        ->modalHeading('오답 노트 출제')
                        ->modalSubmitActionLabel('출제하기')
                        ->modalWidth('xl')
                        ->fillForm(fn($record) => [
                            'from' => now()->subMonth()->format('Y-m-d'),
                            'to' => now()->format('Y-m-d'),
                            'is_dont_know_only' => false,
                            'question_count' => count(WrongAnswerNote::getQuestions(
                                $record->id,
                                now()->subMonth()->format('Y-m-d'),
                                now()->format('Y-m-d')
                            )),
                        ])
                        ->form([
                            Grid::make(2)
                                ->schema([
                                    DatePicker::make('from')
                                        ->live()
                                        ->label('출제 시작일')
                                        ->required()
                                        ->afterStateUpdated(function ($get, $set, $record) {
                                            $from = Carbon::parse($get('from'))->startOfDay();
                                            $to = Carbon::parse($get('to'))->endOfDay();
                                            $isDontKnowOnly = $get('is_dont_know_only');
                                            $set(
                                                'question_count',
                                                count(WrongAnswerNote::getQuestions(
                                                    $record->id,
                                                    $from,
                                                    $to,
                                                    $isDontKnowOnly
                                                ))
                                            );
                                        }),
                                    DatePicker::make('to')
                                        ->live()
                                        ->label('출제 종료일')
                                        ->required()
                                        ->afterStateUpdated(function ($get, $set, $record) {
                                            $from = Carbon::parse($get('from'))->startOfDay();
                                            $to = Carbon::parse($get('to'))->endOfDay();
                                            $isDontKnowOnly = $get('is_dont_know_only');
                                            $set(
                                                'question_count',
                                                count(WrongAnswerNote::getQuestions(
                                                    $record->id,
                                                    $from,
                                                    $to,
                                                    $isDontKnowOnly
                                                ))
                                            );
                                        }),
                                    Toggle::make('is_dont_know_only')
                                        ->label('[잘 모르겠음] 문제만 출제')
                                        ->columnSpanFull()
                                        ->inlineLabel()
                                        ->inline()
                                        ->reactive()
                                        ->live()
                                        ->afterStateUpdated(function ($get, $set, $record) {
                                            $from = Carbon::parse($get('from'))->startOfDay();
                                            $to = Carbon::parse($get('to'))->endOfDay();
                                            $isDontKnowOnly = $get('is_dont_know_only');
                                            $set(
                                                'question_count',
                                                count(WrongAnswerNote::getQuestions(
                                                    $record->id,
                                                    $from,
                                                    $to,
                                                    $isDontKnowOnly
                                                ))
                                            );
                                        })
                                        ->default(false),
                                    TextInput::make('question_count')
                                        ->numeric()
                                        ->label('출제 문제 수')
                                        ->readOnly()
                                        ->disabled()
                                        ->required(),
                                ])
                        ])
                        ->action(function ($record, $data) {
                            $from = Carbon::parse($data['from'])->startOfDay();
                            $to = Carbon::parse($data['to'])->endOfDay();
                            $isDontKnowOnly = $data['is_dont_know_only'];

                            // 문제 개수 확인
                            $questions = WrongAnswerNote::getQuestions(
                                $record->id,
                                $from->format('Y-m-d'),
                                $to->format('Y-m-d'),
                                $isDontKnowOnly
                            );

                            if (empty($questions)) {
                                Notification::make()
                                    ->title('출제될 문제가 없습니다.')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            // 오답 노트 테스트 생성
                            $testSheet = static::createWrongNoteTestSheet($record, $questions, $from, $to, $isDontKnowOnly);

                            if ($testSheet) {
                                Notification::make()
                                    ->title('오답 노트 테스트가 생성되었습니다.')
                                    ->body("총 {$testSheet->total_score}문항의 테스트가 출제되었습니다.")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('테스트 생성에 실패했습니다.')
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Tables\Actions\Action::make('manage-account')
                        ->label(fn($record) => '계정 관리')
                        ->visible(function ($record) {
                            return $record->canEdit(auth()->user());
                        })
                        ->color(function ($record) {
                            if ($record->user->username === null) {
                                return 'gray';
                            } elseif (!$record->user->is_active) {
                                return 'danger';
                            } else {
                                return 'gray';
                            }
                        })
                        ->icon('heroicon-m-user')
                        ->fillForm(fn($record) => [
                            'username' => $record->user->username,
                            'is_active' => $record->user->is_active,
                        ])
                        ->form([
                            TextInput::make('username')
                                ->label('계정')
                                ->required()
                                ->rules([
                                    fn ($record) => function (string $attribute, $value, \Closure $fail) use ($record) {
                                        // 현재 접속한 학원 기준으로 username 중복 체크
                                        $academyId = (app()->has('current_academy') && app('current_academy'))
                                            ? app('current_academy')->id
                                            : auth()->user()->academy_id;
                                        $exists = \App\Models\User::where('username', $value)
                                            ->where('academy_id', $academyId)
                                            ->where('id', '!=', $record->user->id)
                                            ->exists();
                                        if ($exists) {
                                            $fail('이미 사용 중인 계정입니다.');
                                        }
                                    },
                                ]),
                            TextInput::make('password')
                                ->confirmed()
                                ->password()
                                ->label('비밀번호')
                                ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
                                ->dehydrated(fn(?string $state): bool => filled($state))
                                ->required(fn(string $operation): bool => $operation === 'create'),
                            TextInput::make('password_confirmation')
                                ->password()
                                ->dehydrated(false)
                                ->label('비밀번호 확인')
                                ->required(fn(string $operation): bool => $operation === 'create'),
                            Grid::make(2)
                                ->schema([
                                    Toggle::make('is_active')
                                        ->label('승인')
                                        ->inlineLabel()
                                        ->inline()
                                        ->default(true),
                                ]),
                        ])
                        ->modalHeading('계정 관리')
                        ->modalWidth('sm')
                        ->action(function ($record, $data) {
                            $record->user->update([
                                'username' => $data['username'],
                                'is_active' => $data['is_active'],
                            ]);

                            // 승인 상태에 따라 학생 status 변경
                            if ($data['is_active'] && $record->status === 'pending') {
                                // 인원 제한 체크 - 학생이 속한 학원 기준
                                $academy = $record->academy;
                                if ($academy && $academy->student_limit) {
                                    $enrolledCount = \App\Models\Student::withoutGlobalScopes()
                                        ->where('academy_id', $academy->id)
                                        ->where('status', 'enrolled')
                                        ->count();
                                    if ($enrolledCount >= $academy->student_limit) {
                                        Notification::make()
                                            ->title("승인 인원 제한 초과 (현재 {$enrolledCount}명 / 제한 {$academy->student_limit}명)")
                                            ->danger()
                                            ->send();
                                        return;
                                    }
                                }
                                $record->update(['status' => 'enrolled']);

                                // 이력 기록: 승인
                                \App\Models\StudentStatusHistory::create([
                                    'student_id' => $record->id,
                                    'changed_by' => auth()->id(),
                                    'event_type' => 'approved',
                                    'from_status' => 'pending',
                                    'to_status' => 'enrolled',
                                    'reason' => '계정 승인',
                                    'memo' => "계정: {$data['username']}",
                                ]);
                            } elseif (!$data['is_active'] && $record->status === 'enrolled') {
                                $record->update(['status' => 'pending']);

                                // 이력 기록: 승인 취소
                                \App\Models\StudentStatusHistory::create([
                                    'student_id' => $record->id,
                                    'changed_by' => auth()->id(),
                                    'event_type' => 'approval_revoked',
                                    'from_status' => 'enrolled',
                                    'to_status' => 'pending',
                                    'reason' => '계정 비활성화',
                                    'memo' => "계정: {$data['username']}",
                                ]);
                            }

                            if ($data['password'] ?? false) {
                                $record->user->update([
                                    'password' => $data['password'],
                                ]);
                            }
                            Notification::make()
                                ->title('계정이 성공적으로 업데이트되었습니다.')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('manage-counseling')
                        ->label('상담 관리')
                        ->modalHeading('상담 관리')
                        ->icon('heroicon-m-clipboard-document-list')
                        ->url(fn($record) => '/admin/counselings?tableFilters[student_id][value]=' . $record->id)
                        ->modalWidth('2xl'),
                    DeleteAction::make()
                        ->visible(fn($record) => $record->canEdit(auth()->user()))
                        ->modalHeading('학생 삭제')
                        ->before(function ($record) {
                            // 삭제 전 스냅샷을 이력에 기록
                            $classroomInfo = $record->classrooms()
                                ->with('teacher.user')
                                ->get()
                                ->map(fn($c) => $c->name . ' (담임: ' . ($c->teacher?->user?->name ?? '-') . ')')
                                ->join(', ');

                            $snapshot = [
                                "학생명: " . ($record->user?->name ?? '-'),
                                "학교: " . ($record->school?->name ?? '-'),
                                "학년: " . ($record->gradeSystem?->display_name ?? '-'),
                                "상태: " . ($record->status ?? '-'),
                            ];

                            if ($classroomInfo) {
                                $snapshot[] = "소속 반: {$classroomInfo}";
                            }

                            \App\Models\StudentStatusHistory::create([
                                'student_id' => $record->id,
                                'changed_by' => auth()->id(),
                                'event_type' => 'deleted',
                                'from_status' => $record->status,
                                'to_status' => null,
                                'reason' => '학생 삭제',
                                'memo' => implode(' | ', $snapshot),
                            ]);
                        })
                ]),
            ])
            ->hiddenFilterIndicators(true)
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make()
                //         ->modalHeading('학생 삭제'),
                // ]),
            ])
            ->emptyStateHeading('학생이 없습니다.');
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
            'index' => Pages\ListStudents::route('/'),
            // 'create' => Pages\CreateStudent::route('/create'),
            // 'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }

    /**
     * 오답 노트 기반으로 테스트 시트를 생성합니다.
     */
    public static function createWrongNoteTestSheet(
        Student $student,
        array $questions,
        Carbon $from,
        Carbon $to,
        bool $isDontKnowOnly = false
    ): ?TestSheet {
        if (empty($questions)) {
            return null;
        }

        // 테스트 이름 생성
        $periodText = $from->format('m월d일') . ' ~ ' . $to->format('m월d일');
        $typeText = $isDontKnowOnly ? ' (잘 모르는 문제)' : '';
        $testName = "{$student->user->name} 학생 오답 노트 테스트 ({$periodText}){$typeText}";

        // TestSheet 생성
        $testSheet = TestSheet::create([
            'name' => $testName,
            'title' => $testName,
            'sub_title' => $periodText,
            'tags' => ['오답 노트'],
            'target_group' => 'student',
            'target_students' => [$student->user->id],
            'questions' => $questions,
            'status' => 'progress', // 바로 시작 상태로
            'use_score_table' => false,
            'is_auto' => false,
            'user_id' => auth()->id(),
            'start_date' => now(),
            'end_date' => now()->addWeeks(2), // 2주 후 마감
            'target_grades' => [],
            'target_levels' => [],
            'target_classrooms' => [],
            'scopes' => ['오답 복습'],
            'show_explanation_video' => false,
            'print_layout' => [
                'title' => $testName,
                'subTitle' => $periodText,
                'grade' => $student->gradeSystem->display_name ?? '',
                'startingNumber' => 1,
            ],
        ]);

        return $testSheet;
    }
}
