<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LectureResource\Pages;
use App\Filament\Resources\LectureResource\RelationManagers;
use App\Models\Classroom;
use App\Models\GradeSystem;
use App\Models\Lecture;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LectureResource extends Resource
{
    protected static ?string $model = Lecture::class;

    protected static ?string $navigationLabel = '강의실';

    protected static ?string $title = '강의실';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationGroup = '자료실';

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
                        Grid::make(5)
                            ->schema([
                                Radio::make('target_group')
                                    ->label('강의 대상')
                                    ->required()
                                    ->live()
                                    ->reactive()
                                    ->options([
                                        'grade' => '학년',
                                        'level' => '레벨',
                                        'classroom' => '교실/반',
                                        'student' => '학생',
                                    ])
                                    ->default('grade')
                                    ->columns(4)
                                    ->columnSpan(2),
                            ])->columnSpanFull(),
                        Grid::make(3)
                            ->schema([
                                Select::make('target_grades')
                                    ->label('학년')
                                    ->multiple()
                                    ->required()
                                    ->options(function () {
                                        return GradeSystem::query()
                                            ->orderBy('sequential_order')
                                            ->pluck(
                                                'display_name',
                                                'id',
                                            );
                                    })
                                    ->visible(fn(Get $get) => $get('target_group') === 'grade'),
                                Select::make('target_levels')
                                    ->label('레벨')
                                    ->multiple()
                                    ->required()
                                    ->options([
                                        'A' => 'A',
                                        'M' => 'M',
                                        'S' => 'S',
                                    ])
                                    ->visible(fn(Get $get) => $get('target_group') === 'level'),
                                Select::make('target_classrooms')
                                    ->label('반')
                                    ->multiple()
                                    ->required()
                                    ->options(function () {
                                        return Classroom::query()
                                            ->orderBy('name')
                                            ->pluck('name', 'id');
                                    })
                                    ->visible(fn(Get $get) => $get('target_group') === 'classroom'),
                                Select::make('target_students')
                                    ->label('학생')
                                    ->multiple()
                                    ->required()
                                    ->options(function () {
                                        $classroomIds = Classroom::query()
                                            ->orderBy('name')
                                            ->pluck('id');
                                        return Student::query()
                                            ->whereHas('classrooms', function ($q) use ($classroomIds) {
                                                $q->where('classrooms.id', $classroomIds);
                                            })
                                            ->get()
                                            ->mapWithKeys(fn($student) => [$student->user->id => $student->user->name]);
                                    })
                                    ->visible(fn(Get $get) => $get('target_group') === 'student'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                Toggle::make('display')
                                    ->required()
                                    ->inlineLabel()
                                    ->reactive()
                                    ->live()
                                    ->inline()
                                    ->label('공개 여부'),
                                Grid::make(4)
                                    ->schema([
                                        DatePicker::make('published_at')
                                            ->required()
                                            ->label('공개일'),
                                        DatePicker::make('expired_at')
                                            ->required()
                                            ->label('공개 종료일'),
                                    ])
                                    ->visible(fn(Get $get) => $get('display'))
                                    ->columnSpanFull()
                            ])->columnSpanFull(),

                        TextInput::make('title')
                            ->label('제목')
                            ->required(),
                        RichEditor::make('description')
                            ->label('설명')
                            ->required()
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
                TextColumn::make('title')
                    ->label('제목')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('target_group')
                    ->label('대상')
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'grade' => '학년',
                            'level' => '레벨',
                            'classroom' => '교실/반',
                            'student' => '학생',
                            default => '알 수 없음',
                        };
                    })
                    ->sortable(),
                TextColumn::make('display')
                    ->label('공개 여부')
                    ->formatStateUsing(function ($state) {
                        return $state ? '공개' : '비공개';
                    }),
                TextColumn::make('published_at')
                    ->label('공개일')
                    ->date('Y-m-d')
                    ->formatStateUsing(function ($record, $state) {
                        return $record->display ? $state->format('Y-m-d') : '';
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('expired_at')
                    ->label('공개 종료일')
                    ->formatStateUsing(function ($record, $state) {
                        return $record->display ? $state->format('Y-m-d') : '';
                    })
                    ->sortable(),


            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('register-video')
                    ->label('강의 등록')
                    ->icon('heroicon-m-video-camera')
                    ->url(fn($record) => '/admin/lectures/' . $record->id . '/register-video'),
                Tables\Actions\EditAction::make()
                    ->modalHeading('강의 수정')
                    ->modalWidth('4xl'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->modalHeading('강의 삭제')
                ]),
            ])
            ->emptyStateHeading('강의실이 없습니다.');
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
            'index' => Pages\ListLectures::route('/'),
            // 'create' => Pages\CreateLecture::route('/create'),
            // 'edit' => Pages\EditLecture::route('/{record}/edit'),
        ];
    }
}
