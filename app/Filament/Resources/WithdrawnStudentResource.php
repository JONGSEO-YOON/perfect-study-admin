<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WithdrawnStudentResource\Pages;
use App\Models\Classroom;
use App\Models\GradeSystem;
use App\Models\Student;
use App\Models\StudentStatusHistory;
use App\Models\Teacher;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class WithdrawnStudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = null;

    protected static ?string $navigationLabel = '퇴원생 관리';

    protected static ?string $title = '퇴원생 관리';

    protected static ?string $navigationGroup = '교실 관리';

    protected static ?int $navigationSort = 0;

    protected static ?string $slug = 'withdrawn-students';

    public static function canViewAny(): bool
    {
        if (!\App\Models\Academy::isMenuGroupVisibleForCurrentUser('classroom')) {
            return false;
        }
        // 관리자와 상담실만
        $user = auth()->user();
        return $user && (
            in_array($user->role, ['root_admin', 'admin'])
            || ($user->userable_type === \App\Models\Teacher::class && $user->role === 'counselor')
        );
    }

    public static function getBreadcrumb(): string
    {
        return '';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('withdrawn_at', 'desc')
            ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'withdrawn'))
            ->columns([
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
                    ->formatStateUsing(fn($record) => $record->gradeSystem?->display_name ?? '-')
                    ->sortable(),
                TextColumn::make('user.phone')
                    ->label('전화번호')
                    ->searchable(),
                TextColumn::make('classrooms.name')
                    ->label('담임')
                    ->formatStateUsing(function ($record) {
                        $classroom = $record->classrooms->first();
                        return $classroom?->teacher?->user?->name ?? '-';
                    }),
                TextColumn::make('withdrawal_reason')
                    ->label('퇴원 사유')
                    ->formatStateUsing(function ($state, $record) {
                        $labels = [
                            'poor_performance' => '성적부진',
                            'change_of_atmosphere' => '분위기전환',
                            'teacher_mismatch' => '선생님맞지않음',
                            'academy_atmosphere' => '학원분위기안좋음',
                            'relocation' => '이사',
                            'other' => '기타',
                        ];
                        $label = $labels[$state] ?? $state;
                        if ($state === 'other' && $record->withdrawal_reason_detail) {
                            return "기타: {$record->withdrawal_reason_detail}";
                        }
                        return $label ?? '-';
                    }),
                TextColumn::make('withdrawn_at')
                    ->label('퇴원 일자')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->date('Y-m-d')
                    ->label('등록일')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('date_range')
                    ->form([
                        Grid::make(2)->schema([
                            DatePicker::make('from')->label('퇴원일 시작'),
                            DatePicker::make('until')->label('퇴원일 종료'),
                        ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn($q, $d) => $q->whereDate('withdrawn_at', '>=', $d))
                            ->when($data['until'] ?? null, fn($q, $d) => $q->whereDate('withdrawn_at', '<=', $d));
                    }),
                SelectFilter::make('classroom')
                    ->label('반')
                    ->options(fn() => Classroom::withoutGlobalScopes()->orderBy('name')->pluck('name', 'id')->toArray())
                    ->query(fn(Builder $query, array $data) => $query->when(
                        $data['value'] ?? null,
                        fn($q, $v) => $q->whereHas('classrooms', fn($sq) => $sq->where('classroom_id', $v))
                    ))
                    ->searchable()
                    ->preload(),
                SelectFilter::make('teacher')
                    ->label('강사')
                    ->options(function () {
                        $query = User::where('userable_type', Teacher::class)->with('userable');
                        if (auth()->user()->academy_id) {
                            $query->where('academy_id', auth()->user()->academy_id);
                        }
                        return $query->get()
                            ->filter(fn($u) => $u->userable !== null)
                            ->mapWithKeys(fn($u) => [$u->userable->id => $u->name])
                            ->toArray();
                    })
                    ->query(fn(Builder $query, array $data) => $query->when(
                        $data['value'] ?? null,
                        fn($q, $v) => $q->whereHas('classrooms', fn($sq) => $sq->where('teacher_id', $v))
                    ))
                    ->searchable()
                    ->preload(),
                SelectFilter::make('grade_system_id')
                    ->label('학년')
                    ->options(fn() => GradeSystem::orderBy('sequential_order')->pluck('display_name', 'id')->toArray())
                    ->searchable()
                    ->preload(),
                SelectFilter::make('withdrawal_reason')
                    ->label('퇴원 사유')
                    ->options([
                        'poor_performance' => '성적부진',
                        'change_of_atmosphere' => '분위기전환',
                        'teacher_mismatch' => '선생님맞지않음',
                        'academy_atmosphere' => '학원분위기안좋음',
                        'relocation' => '이사',
                        'other' => '기타',
                    ]),
            ], FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\Action::make('restore')
                    ->label('재원 처리')
                    ->icon('heroicon-m-arrow-uturn-left')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('재원 처리')
                    ->modalDescription('이 학생을 다시 재원생으로 변경하시겠습니까?')
                    ->action(function ($record) {
                        $oldStatus = $record->status;

                        $record->update([
                            'status' => 'enrolled',
                        ]);

                        StudentStatusHistory::create([
                            'student_id' => $record->id,
                            'changed_by' => auth()->id(),
                            'event_type' => 're_register',
                            'from_status' => $oldStatus,
                            'to_status' => 'enrolled',
                            'reason' => '재원 처리',
                        ]);

                        Notification::make()->title('재원 처리되었습니다.')->success()->send();
                    }),
                Tables\Actions\Action::make('history')
                    ->label('히스토리')
                    ->icon('heroicon-m-clock')
                    ->color('gray')
                    ->modalHeading('상태 변경 히스토리')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('닫기')
                    ->modalContent(function ($record) {
                        $histories = StudentStatusHistory::where('student_id', $record->id)
                            ->with('changedBy')
                            ->orderBy('created_at', 'desc')
                            ->get();
                        return view('filament.student-status-history', ['histories' => $histories]);
                    }),
            ])
            ->emptyStateHeading('퇴원생이 없습니다.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWithdrawnStudents::route('/'),
        ];
    }
}
