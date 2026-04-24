<?php

namespace App\Filament\Resources\ClassroomResource\RelationManagers;

use App\Models\Student;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('학생 관리')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->label('이름'),
                Tables\Columns\TextColumn::make('user.phone')
                    ->label('전화번호')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gradeSystem.display_name')
                    ->label('학년'),
                Tables\Columns\TextColumn::make('school.name')
                    ->label('학교')
                    ->default('-'),
                Tables\Columns\TextColumn::make('status')
                    ->label('상태')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'enrolled' => '재원',
                        'withdrawn' => '퇴원',
                        default => $state ?? '재원',
                    })
                    ->color(fn($state) => match ($state) {
                        'withdrawn' => 'danger',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('user.birthed_at')
                    ->date('Y-m-d')
                    ->label('생년월일'),
                Tables\Columns\TextColumn::make('classroom_created_at')
                    ->state(fn($record) => $record->pivot->created_at->format('Y-m-d H:i'))
                    ->label('반 등록일'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('학생 추가하기')
                    ->multiple()
                    ->modalHeading('학생 추가하기')
                    ->visible(fn() => auth()->user()->isRoleAbove('admin', true))
                    ->recordTitle(fn($record) => $record->user->name)
                    ->preloadRecordSelect(true)
                    ->form(fn(Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->preload()
                            ->getSearchResultsUsing(
                                fn(string $search): array =>
                                User::getAvailableStudentsByClassRoomId($search, $this->ownerRecord->id)
                            )
                    ])
                    ->after(function (array $data) {
                        // 반 배정 이력 기록
                        $classroom = $this->ownerRecord;
                        $teacherName = $classroom->teacher?->user?->name ?? '-';
                        $studentIds = (array) ($data['recordId'] ?? []);

                        foreach ($studentIds as $studentId) {
                            \App\Models\StudentStatusHistory::create([
                                'student_id' => $studentId,
                                'changed_by' => auth()->id(),
                                'event_type' => 'classroom_assigned',
                                'from_status' => null,
                                'to_status' => null,
                                'reason' => '반 배정',
                                'memo' => "{$classroom->name} (담임: {$teacherName})",
                            ]);
                        }
                    })
                    ->attachAnother(false),
            ])
            ->actions([
                Tables\Actions\Action::make('remove')
                    ->label('삭제')
                    ->modalHeading('학생 삭제')
                    ->icon('heroicon-m-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        \Illuminate\Support\Facades\DB::table('classroom_student')
                            ->where('classroom_id', $this->ownerRecord->id)
                            ->where('student_id', $record->id)
                            ->whereNull('deleted_at')
                            ->update(['deleted_at' => now()]);

                        // 반 해제 이력 기록
                        $classroom = $this->ownerRecord;
                        $teacherName = $classroom->teacher?->user?->name ?? '-';
                        \App\Models\StudentStatusHistory::create([
                            'student_id' => $record->id,
                            'changed_by' => auth()->id(),
                            'event_type' => 'classroom_removed',
                            'from_status' => null,
                            'to_status' => null,
                            'reason' => '반 해제',
                            'memo' => "{$classroom->name} (담임: {$teacherName})",
                        ]);
                    }),
            ])
            ->bulkActions([
            ])
            ->emptyStateHeading('학생이 없습니다.')
            ->queryStringIdentifier('classroom-student');
    }
}
