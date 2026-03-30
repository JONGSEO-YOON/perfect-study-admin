<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplementaryScheduleResource\Pages;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\SupplementarySchedule;
use App\Models\Teacher;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SupplementaryScheduleResource extends Resource
{
    protected static ?string $model = SupplementarySchedule::class;

    protected static ?string $navigationIcon = null;

    protected static ?string $navigationLabel = '보충 달력';

    protected static ?string $title = '보충 달력';

    protected static ?string $navigationGroup = '교실 관리';

    protected static ?int $navigationSort = 6;

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
                        Select::make('teacher_id')
                            ->label('담당 선생님')
                            ->options(function () {
                                return User::where('userable_type', Teacher::class)
                                    ->with('userable')
                                    ->get()
                                    ->mapWithKeys(fn($user) => [$user->userable->id => $user->name])
                                    ->toArray();
                            })
                            ->default(function () {
                                $user = auth()->user();
                                if ($user->userable instanceof Teacher) {
                                    return $user->userable->id;
                                }
                                return null;
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(fn() => auth()->user()->role === 'general')
                            ->dehydrated(),
                        Select::make('classroom_id')
                            ->label('반')
                            ->options(function () {
                                $query = Classroom::query()->orderBy('name');
                                if (auth()->user()->role === 'general' && auth()->user()->userable instanceof Teacher) {
                                    $query->where('teacher_id', auth()->user()->userable->id);
                                }
                                return $query->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload(),
                        Select::make('student_id')
                            ->label('보충 학생')
                            ->options(function () {
                                $query = Student::with('user');
                                if (auth()->user()->role === 'general' && auth()->user()->userable instanceof Teacher) {
                                    $query->whereHas('classrooms', function ($q) {
                                        $q->where('classrooms.teacher_id', auth()->user()->userable->id);
                                    });
                                }
                                return $query->get()
                                    ->mapWithKeys(fn($student) => [$student->id => $student->user->name ?? ''])
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload(),
                        DatePicker::make('scheduled_date')
                            ->label('보충 날짜')
                            ->required(),
                        TimePicker::make('start_time')
                            ->label('시작 시간')
                            ->seconds(false),
                        TimePicker::make('end_time')
                            ->label('종료 시간')
                            ->seconds(false),
                        TextInput::make('room')
                            ->label('강의실'),
                        Textarea::make('reason')
                            ->label('보충 사유')
                            ->columnSpanFull()
                            ->rows(3),
                        Textarea::make('memo')
                            ->label('메모')
                            ->columnSpanFull()
                            ->rows(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                // 일반 강사는 자기 보충만 조회
                if (auth()->user()->role === 'general' && auth()->user()->userable instanceof Teacher) {
                    $query->where('teacher_id', auth()->user()->userable->id);
                }
                return $query;
            })
            ->columns([
                TextColumn::make('scheduled_date')
                    ->label('보충 날짜')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('start_time')
                    ->label('시작')
                    ->time('H:i'),
                TextColumn::make('end_time')
                    ->label('종료')
                    ->time('H:i'),
                TextColumn::make('teacher.user.name')
                    ->label('담당 선생님')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('student.user.name')
                    ->label('학생')
                    ->searchable()
                    ->sortable()
                    ->default('-'),
                TextColumn::make('classroom.name')
                    ->label('반')
                    ->sortable()
                    ->default('-'),
                TextColumn::make('room')
                    ->label('강의실')
                    ->default('-'),
                TextColumn::make('reason')
                    ->label('보충 사유')
                    ->limit(30)
                    ->tooltip(fn($record) => $record->reason),
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
                            ->when($data['from'] ?? null, fn(Builder $q, $date) => $q->whereDate('scheduled_date', '>=', $date))
                            ->when($data['until'] ?? null, fn(Builder $q, $date) => $q->whereDate('scheduled_date', '<=', $date));
                    }),
                SelectFilter::make('teacher_id')
                    ->options(function () {
                        return User::where('userable_type', Teacher::class)
                            ->with('userable')
                            ->get()
                            ->mapWithKeys(fn($user) => [$user->userable->id => $user->name])
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->label('선생님')
                    ->visible(fn() => auth()->user()->role !== 'general'),
                SelectFilter::make('classroom_id')
                    ->options(function () {
                        return Classroom::orderBy('name')->pluck('name', 'id')->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->label('반'),
            ], FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalWidth('xl'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('scheduled_date', 'desc')
            ->emptyStateHeading('보충 일정이 없습니다.')
            ->emptyStateDescription('보충 일정을 추가하려면 "보충 추가" 버튼을 눌러주세요.');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupplementarySchedules::route('/'),
        ];
    }
}
