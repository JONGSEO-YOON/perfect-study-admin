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
                    ->attachAnother(false),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->modalHeading('학생 삭제')
                    ->label('삭제'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label('삭제')
                        ->modalHeading('학생 삭제'),
                ]),
            ])
            ->emptyStateHeading('학생이 없습니다.')
            ->queryStringIdentifier('classroom-student');
    }
}
