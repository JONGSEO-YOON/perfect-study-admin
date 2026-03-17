<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CounselingResource\Pages;
use App\Filament\Resources\CounselingResource\RelationManagers;
use App\Filament\Resources\CounselingResource\Widgets;
use App\Models\Counseling;
use App\Models\Notification;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CounselingResource extends Resource
{
    protected static ?string $model = Counseling::class;

    protected static ?string $navigationLabel = '상담 관리';

    protected static ?string $title = '상담 관리';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationGroup = '교실 관리';

    public static function getBreadcrumb(): string
    {
        return '';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        Hidden::make('request')
                            ->default(false)
                            ->live()
                            ->reactive()
                            ->dehydrated(false),
                        Select::make('student_id')
                            ->label('학생')
                            ->options(function (Get $get) {

                                $canViewAllStudents =
                                    !auth()->user()->userable instanceof \App\Models\Teacher ||
                                    auth()->user()->isRoleAbove('general');
                                $query = User::where('userable_type', Student::class)
                                    ->with('userable');
                                if (!$canViewAllStudents) {
                                    $query = $query->whereHas('student.classrooms', function ($q) {
                                        $q->where('classrooms.teacher_id', auth()->user()->userable->id);
                                    });
                                }
                                return $query
                                    ->orWhere(function ($query) use ($get) {
                                        $query->where('userable_id', $get('student_id'))
                                            ->where('userable_type', Student::class);
                                    })
                                    ->get()
                                    ->mapWithKeys(fn($user) => [$user->userable->id => $user->name . ' (' . $user->birthed_at->format('Y-m-d') . ')'])
                                    ->toArray();
                            })
                            ->required()
                            ->preload()
                            ->searchable(),
                        Select::make('counselor_id')
                            ->label('상담자')
                            ->options(function (Get $get) {
                                $result = User::where('userable_type', Teacher::class)
                                    ->with('userable')
                                    ->get()
                                    ->mapWithKeys(fn($user) => [$user->userable->id => $user->name])
                                    ->toArray();
                                return $result;
                            })
                            ->required()
                            ->preload()
                            ->searchable(),
                        Grid::make(2)
                            ->schema([
                                ViewField::make('add_student_link')
                                    ->dehydrated(false)
                                    ->view('filament.components.forms.add-student-link')
                            ])->columnSpanFull(),
                        DatePicker::make('planned_start_at')
                            ->label('상담 희망 시작일'),
                        DatePicker::make('planned_end_at')
                            ->label('상담 희망 종료일'),
                        Grid::make(3)
                            ->schema([
                                Select::make('target')
                                    ->label('상담 대상')
                                    ->options([
                                        '학부모' => '학부모',
                                        '학생' => '학생',
                                        '기타' => '기타',
                                    ]),
                                Select::make('requester_type')
                                    ->label('상담 신청자')
                                    ->options([
                                        '담임' => '담임',
                                        '상담실' => '상담실',
                                        '기타' => '기타',
                                    ]),
                                Select::make('type')
                                    ->label('상담 유형')
                                    ->options([
                                        '성적 상담' => '성적 상담',
                                        '태도 상담' => '태도 상담',
                                        '내신 상담' => '내신 상담',
                                        '신규 상담' => '신규 상담',
                                        '퇴원 상담' => '퇴원 상담',
                                        '기타' => '기타',
                                    ]),
                                Select::make('status')
                                    ->label('상담 상태')
                                    ->options([
                                        '상담 요청' => '상담 요청',
                                        '상담 진행' => '상담 진행',
                                        '상담 완료' => '상담 완료',
                                    ])
                                    ->default('상담 요청')
                                    ->required(),

                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('requester_id')
                                            ->formatStateUsing(fn($record) => $record?->requester?->name ?? auth()->user()->name)
                                            ->label('요청자')
                                            ->disabled(),

                                        Textarea::make('request_message')
                                            ->label('요청 내용')
                                            ->required()
                                            ->disabled(fn(Get $get) => !$get('request'))
                                            ->rows(5)
                                            ->columnSpanFull(),
                                    ])
                                    ->hidden(fn(Get $get, $operation) => !$get('request')),
                            ]),
                        Tabs::make('Tabs')
                            ->activeTab(2)
                            ->columnSpanFull()
                            ->hidden(fn(Get $get, $operation) => $get('request'))
                            ->tabs([
                                Tab::make('Tab1')
                                    ->label('상담 요청')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('requester_id')
                                                    ->formatStateUsing(fn($record) => $record?->requester?->name ?? auth()->user()->name)
                                                    ->label('요청자')
                                                    ->disabled(),

                                                Textarea::make('request_message')
                                                    ->label('요청 내용')
                                                    ->disabled(fn(Get $get) => !$get('request'))
                                                    ->rows(5)
                                                    ->columnSpanFull(),
                                            ])
                                    ]),
                                Tab::make('Tab2')
                                    ->label('상담 내용')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                DatePicker::make('counseled_at')
                                                    ->label('상담 일시'),
                                                TextInput::make('title')
                                                    ->label('상담 주제')
                                                    ->columnSpanFull(),
                                                Textarea::make('content')
                                                    ->label('상담 내용')
                                                    ->rows(5)
                                                    ->columnSpanFull(),
                                            ])
                                    ]),
                                Tab::make('Tab3')
                                    ->label('원장님/관리자 확인')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Toggle::make('confirmed')
                                                    ->label('확인 여부'),
                                                Textarea::make('reply_message')
                                                    ->label('메시지')
                                                    ->rows(5)
                                                    ->columnSpanFull(),
                                            ])
                                    ])
                            ]),

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $canViewAllStudents =
                    !auth()->user()->userable instanceof \App\Models\Teacher ||
                    auth()->user()->isRoleAbove('general');
                if (!$canViewAllStudents) {
                    $query = $query->where(function ($query) {
                        $query->whereHas('student.classrooms', function ($q) {
                            $q->where('classrooms.teacher_id', auth()->user()->userable->id);
                        })->orWhere('counselor_id', auth()->user()->userable->id);
                    });
                }
                return $query;
            })
            ->columns([
                TextColumn::make('id')
                    ->label('No')
                    ->html()
                    ->formatStateUsing(fn($record, $state) =>
                    !$record->confirmed ?
                        '<span class="text-red-500 text-xl">&#x2022;</span>' . $state : $state)
                    ->rowIndex(),

                TextColumn::make('student.user.name')
                    ->label('학생')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('counselor.user.name')
                    ->label('상담자')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('상담 유형')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('target')
                    ->label('상담 대상')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('requester_type')
                    ->label('신청자')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('상태')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        '상담 요청' => 'gray',
                        '상담 진행' => 'warning',
                        '상담 완료' => 'success',
                    })
                    ->sortable(),

                TextColumn::make('planned_at')
                    ->state(true)
                    ->formatStateUsing(fn($record) => $record->planned_start_at?->format('y-m-d') . ' ~ ' . $record->planned_end_at?->format('y-m-d'))
                    ->label('상담 희망일'),
                TextColumn::make('created_at')
                    ->date('Y-m-d')
                    ->label('등록일')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('date_range')
                    ->form([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('from')
                                    ->label('시작일'),
                                DatePicker::make('until')
                                    ->label('종료일'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn(Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn(Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
                SelectFilter::make('counselor_id')
                    ->options(function () {
                        return User::where('userable_type', Teacher::class)
                            ->with('userable')
                            ->get()
                            ->mapWithKeys(fn($user) => [$user->userable->id => $user->name])
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->label('상담자'),
                SelectFilter::make('student_id')
                    ->options(function () {
                        $canViewAllStudents =
                            !auth()->user()->userable instanceof \App\Models\Teacher ||
                            auth()->user()->isRoleAbove('general');
                        $query = User::where('userable_type', Student::class)
                            ->with('userable');
                        if (!$canViewAllStudents) {
                            $query = $query->whereHas('student.classrooms', function ($q) {
                                $q->where('classrooms.teacher_id', auth()->user()->userable->id);
                            });
                        }
                        return $query->get()
                            ->mapWithKeys(fn($user) => [$user->userable->id => $user->name . ' (' . $user->birthed_at->format('Y-m-d') . ')'])
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->label('학생'),
                SelectFilter::make('status')
                    ->options([
                        '상담 요청' => '상담 요청',
                        '상담 진행' => '상담 진행',
                        '상담 완료' => '상담 완료',
                    ])
                    ->label('상태'),
            ], FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('상담 기록하기')
                    ->modalWidth('xl')
                    ->before(function ($record, $data) {
                        if (!$record->confirmed && $data['confirmed']) {
                            Notification::create([
                                'user_id' => $record->counselor_id,
                                'type' => Notification::TYPE_COUNSELING_CONFIRMATION,
                                'title' => '상담 원장/관리자 확인',
                                'content' =>  $record->student->user->name . '학생의 상담을 관리자가 확인했습니다.',
                                'data' => ['user_id' => $record->counselor_id, 'student_id' => $record->student_id],
                            ]);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->modalHeading('상담 삭제'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('상담이 없습니다.')
            ->emptyStateDescription('상담을 추가하려면 상담 기록하기 버튼을 눌러주세요.');
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\CounselingStatsWidget::class,
        ];
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
            'index' => Pages\ListCounselings::route('/'),
            // 'create' => Pages\CreateCounseling::route('/create'),
            // 'edit' => Pages\EditCounseling::route('/{record}/edit'),
        ];
    }
}
