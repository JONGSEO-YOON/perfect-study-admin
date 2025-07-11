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

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = '학생 관리';

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
                                        if (User::where('phone', $phone)->where('id', '!=', $get('id'))->exists()) {
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
                if (auth()->user()->isRoleAbove('manager', true)) {
                    return $query;
                }
                return $query->whereHas('classrooms', function ($q) {
                    $q->where('classrooms.teacher_id', auth()->user()->userable->id);
                });
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
            ], layout: FiltersLayout::AboveContent)
            ->actions([
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
                    ->modalWidth('xl'),

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
                                // ->readOnly()
                                ->required(),
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

                            if ($data['password'] ?? false) {
                                $record->user->update([
                                    'password' => $data['password'],
                                ]);
                                // dd($data['password']);
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
}
