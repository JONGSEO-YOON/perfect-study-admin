<?php

namespace App\Filament\Resources\ClassroomResource\RelationManagers;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

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
                Tables\Actions\Action::make('transfer')
                    ->label('전반')
                    ->modalHeading('전반')
                    ->modalDescription(fn($record) => "{$record->user?->name} 학생을 다른 반으로 이동합니다.")
                    ->icon('heroicon-m-arrows-right-left')
                    ->color('warning')
                    ->visible(fn() => auth()->user()->isRoleAbove('admin', true))
                    ->form([
                        Select::make('target_classroom_id')
                            ->label('이동할 반')
                            ->options(function () {
                                $query = Classroom::query()
                                    ->where('id', '!=', $this->ownerRecord->id)
                                    ->orderBy('name');
                                if (auth()->user()->academy_id) {
                                    $query->where('academy_id', auth()->user()->academy_id);
                                }
                                return $query->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Textarea::make('reason')
                            ->label('전반 사유')
                            ->rows(2)
                            ->placeholder('예: 학생 요청, 레벨 조정 등'),
                    ])
                    ->action(function (array $data, $record) {
                        $fromClassroom = $this->ownerRecord;
                        $toClassroom = Classroom::find($data['target_classroom_id']);

                        if (!$toClassroom) {
                            Notification::make()->title('대상 반을 찾을 수 없습니다.')->danger()->send();
                            return;
                        }

                        DB::transaction(function () use ($record, $fromClassroom, $toClassroom, $data) {
                            // 기존 반 제거 (soft delete)
                            DB::table('classroom_student')
                                ->where('classroom_id', $fromClassroom->id)
                                ->where('student_id', $record->id)
                                ->whereNull('deleted_at')
                                ->update(['deleted_at' => now()]);

                            // 새 반에 이미 활성 상태로 있으면 skip, 없으면 attach
                            $exists = DB::table('classroom_student')
                                ->where('classroom_id', $toClassroom->id)
                                ->where('student_id', $record->id)
                                ->whereNull('deleted_at')
                                ->exists();

                            if (!$exists) {
                                DB::table('classroom_student')->insert([
                                    'classroom_id' => $toClassroom->id,
                                    'student_id' => $record->id,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }

                            // 전반 이력 기록 (단일 이벤트)
                            $fromTeacher = $fromClassroom->teacher?->user?->name ?? '-';
                            $toTeacher = $toClassroom->teacher?->user?->name ?? '-';
                            \App\Models\StudentStatusHistory::create([
                                'student_id' => $record->id,
                                'changed_by' => auth()->id(),
                                'event_type' => 'classroom_transferred',
                                'from_status' => null,
                                'to_status' => null,
                                'reason' => $data['reason'] ?? '전반',
                                'memo' => "{$fromClassroom->name} (담임: {$fromTeacher}) → {$toClassroom->name} (담임: {$toTeacher})",
                            ]);
                        });

                        Notification::make()
                            ->title("'{$toClassroom->name}' 반으로 이동되었습니다.")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('remove')
                    ->label('삭제')
                    ->modalHeading('학생 삭제')
                    ->icon('heroicon-m-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        DB::table('classroom_student')
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
