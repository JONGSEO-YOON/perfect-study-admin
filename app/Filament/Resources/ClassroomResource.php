<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassroomResource\Pages;
use App\Filament\Resources\ClassroomResource\RelationManagers;
use App\Forms\Components\TimeTableInput;
use App\Models\Classroom;
use App\Models\Teacher;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

class ClassroomResource extends Resource
{
    protected static ?string $model = Classroom::class;

    protected static ?string $navigationLabel = '반 관리';

    protected static ?string $title = '반 관리';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationGroup = '교실 관리';

    public static function getBreadcrumb(): string
    {
        return '';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Grid::make(2)
                    ->schema([
                        //
                        TextInput::make('name')
                            ->label('반 이름')
                            ->required(),
                        Select::make('teacher_id')
                            ->label('강사')
                            ->options(function () {
                                return Teacher::with('user')->get()->pluck('user.name', 'id')->toArray();
                            })
                            ->preload()
                            ->searchable()
                            ->required(),
                        DatePicker::make('started_at')
                            ->label('개설일'),
                        DatePicker::make('ended_at')
                            ->label('종료일'),
                        TimeTableInput::make('timetable')
                            ->label('시간표'),
                    ]),
                Grid::make(2)
                    ->schema([
                        //
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

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('id')
                    ->label('No')
                    ->rowIndex(),
                TextColumn::make('name')
                    ->label('이름')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('teacher.user.name')
                    ->label('강사')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('students_count')
                    ->counts('students')
                    ->label('학생 수')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('started_at')
                    ->date('Y-m-d')
                    ->label('개설일')
                    ->sortable(),
                TextColumn::make('ended_at')
                    ->date('Y-m-d')
                    ->label('종료일')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->date('Y-m-d')
                    ->label('등록일')
                    ->sortable(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('edit-students')
                    ->modalHeading(false)
                    ->label('학생 관리')
                    ->icon('heroicon-m-users')
                    ->modalContent(fn($record) => view('filament.components.modals.classroom-students', [
                        'record' => $record,
                    ]))

                    ->modalCancelAction(false)
                    ->modalCancelActionLabel('닫기')
                    ->modalSubmitActionLabel('저장')
                    ->modalSubmitAction(false)
                    ->modalWidth('xl'),
                Tables\Actions\EditAction::make()
                    ->modalHeading('반 수정하기')
                    ->modalWidth('xl'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->modalHeading('반 삭제'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
            RelationManagers\StudentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClassrooms::route('/'),
            // 'create' => Pages\CreateClassroom::route('/create'),
            // 'edit' => Pages\EditClassroom::route('/{record}/edit'),
        ];
    }
}
