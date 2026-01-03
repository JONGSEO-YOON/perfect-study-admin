<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WithdrawnStudentResource\Pages;
use App\Models\Classroom;
use App\Models\GradeSystem;
use App\Models\Student;
use App\Models\Teacher;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WithdrawnStudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationGroup = '교실 관리';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationLabel = '퇴원생 관리';

    protected static ?string $modelLabel = '퇴원생';

    protected static ?string $pluralModelLabel = '퇴원생';

    public static function getBreadcrumb(): string
    {
        return '퇴원생 관리';
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->isRoleAbove('admin', true)
            || auth()->user()->userable instanceof \App\Models\Counselor;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withdrawn();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('withdrawn_at', 'desc')
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
                TextColumn::make('gradeSystem.display_name')
                    ->label('학년')
                    ->sortable(),
                TextColumn::make('homeroomTeacher.user.name')
                    ->label('담임')
                    ->placeholder('-'),
                TextColumn::make('withdrawal_reason')
                    ->label('퇴원사유')
                    ->formatStateUsing(fn ($state) => Student::WITHDRAWAL_REASONS[$state] ?? $state)
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'poor_performance' => 'danger',
                        'teacher_mismatch' => 'warning',
                        'academy_atmosphere' => 'warning',
                        'relocation' => 'info',
                        'change_of_atmosphere' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('withdrawn_at')
                    ->label('퇴원일')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('등록일')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('withdrawn_period')
                    ->form([
                        Grid::make(2)->schema([
                            DatePicker::make('withdrawn_from')
                                ->label('퇴원일(시작)'),
                            DatePicker::make('withdrawn_to')
                                ->label('퇴원일(종료)'),
                        ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['withdrawn_from'], fn ($q, $date) => $q->whereDate('withdrawn_at', '>=', $date))
                            ->when($data['withdrawn_to'], fn ($q, $date) => $q->whereDate('withdrawn_at', '<=', $date));
                    }),
                SelectFilter::make('classroom_id')
                    ->label('반')
                    ->options(fn () => Classroom::pluck('name', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        if (!$data['value']) return $query;
                        return $query->whereHas('classrooms', fn ($q) => $q->where('classrooms.id', $data['value']));
                    }),
                SelectFilter::make('homeroom_teacher_id')
                    ->label('담임')
                    ->options(fn () => Teacher::with('user')->get()->pluck('user.name', 'id')),
                SelectFilter::make('grade_system_id')
                    ->label('학년')
                    ->options(fn () => GradeSystem::orderBy('sequential_order')->pluck('display_name', 'id')),
                SelectFilter::make('withdrawal_reason')
                    ->label('퇴원사유')
                    ->options(Student::WITHDRAWAL_REASONS),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(5)
            ->actions([
                Tables\Actions\Action::make('view-detail')
                    ->label('상세')
                    ->icon('heroicon-m-eye')
                    ->modalHeading(fn ($record) => $record->user->name . ' 학생 정보')
                    ->modalSubmitAction(false)
                    ->modalContent(fn ($record) => view('filament.components.modals.withdrawn-student-detail', [
                        'record' => $record,
                    ]))
                    ->modalWidth('2xl'),
                Tables\Actions\Action::make('re-enroll')
                    ->label('재입원')
                    ->icon('heroicon-m-arrow-uturn-left')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('재입원 처리')
                    ->modalDescription(fn ($record) => $record->user->name . ' 학생을 재입원 처리하시겠습니까?')
                    ->form([
                        Textarea::make('memo')
                            ->label('메모')
                            ->placeholder('재입원 사유 등'),
                    ])
                    ->action(function ($record, array $data) {
                        if ($record->reEnroll($data['memo'] ?? null)) {
                            Notification::make()
                                ->title('재입원 처리 완료')
                                ->body($record->user->name . ' 학생이 재입원되었습니다.')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('재입원 처리 실패')
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('view-history')
                    ->label('이력')
                    ->icon('heroicon-m-clock')
                    ->modalHeading(fn ($record) => $record->user->name . ' 학생 입퇴원 이력')
                    ->modalSubmitAction(false)
                    ->modalContent(fn ($record) => view('filament.components.modals.enrollment-history', [
                        'record' => $record,
                    ]))
                    ->modalWidth('xl'),
            ])
            ->bulkActions([])
            ->emptyStateHeading('퇴원생이 없습니다.')
            ->emptyStateDescription('퇴원 처리된 학생이 여기에 표시됩니다.');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWithdrawnStudents::route('/'),
        ];
    }
}
