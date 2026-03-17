<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentNoticeResource\Pages;
use App\Filament\Resources\StudentNoticeResource\RelationManagers;
use App\Models\StudentNotice;
use App\Models\Student;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class StudentNoticeResource extends Resource
{
    protected static ?string $model = StudentNotice::class;

    protected static ?string $navigationLabel = '학생 공지';

    protected static ?string $title = '학생 공지';

    protected static ?int $navigationSort = 7;

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
                        Grid::make(1)
                            ->schema([
                                ToggleButtons::make('target_type')
                                    ->label('공지 대상')
                                    ->options([
                                        'all' => '전체',
                                        'class' => '반',
                                        'student' => '학생',
                                    ])
                                    ->default('all')
                                    ->inline()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('classroom_id', null);
                                        $set('student_id', null);
                                    })
                                    ->dehydrated(false),
                                Select::make('classroom_id')
                                    ->label('반')
                                    ->relationship('classroom', 'name')
                                    ->required(fn(Get $get) => $get('target_type') === 'class')
                                    ->visible(fn(Get $get) => $get('target_type') === 'class')
                                    ->searchable()
                                    ->preload(),
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
                                            ->get()
                                            ->mapWithKeys(fn($user) => [$user->userable->id => $user->name . ' (' . $user->birthed_at->format('Y-m-d') . ')'])
                                            ->toArray();
                                    })
                                    ->required(fn(Get $get) => $get('target_type') === 'student')
                                    ->visible(fn(Get $get) => $get('target_type') === 'student')
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\Checkbox::make('only_parent')
                                    ->label('부모에게만 공지')
                                    ->visible(fn(Get $get) => $get('target_type') === 'student'),
                            ])->columnSpanFull(),
                        TextInput::make('title')
                            ->label('제목')
                            ->required()
                            ->columnSpanFull(),
                        RichEditor::make('content')
                            ->label('내용')
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
                Toggle::make('pinned_at')
                    ->mutateDehydratedStateUsing(function ($state) {
                        if (!$state) {
                            return null;
                        }
                        return now();
                    })
                    ->label('상단 고정'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->orderBy('pinned_at', 'desc');
            })
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('학생 공지가 없습니다.')
            ->columns([
                TextColumn::make('id')
                    ->label('No')
                    ->rowIndex(),
                TextColumn::make('title')
                    ->label('제목')
                    ->html()
                    ->formatStateUsing(function ($record) {
                        return new HtmlString($record->pinned_at ? '<div class="flex items-center gap-x-1"><img class="size-4" src="/icons/pin.svg" />' . $record->title . '</div>' : $record->title);
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('target_type')
                    ->label('대상')
                    ->badge()
                    ->state(function ($record) {
                        if ($record->student_id) {
                            return '학생';
                        }
                        if ($record->classroom_id) {
                            return '반';
                        }
                        return '전체';
                    })
                    ->color(function ($state) {
                        return match ($state) {
                            '학생' => 'success',
                            '반' => 'warning',
                            '전체' => 'info',
                        };
                    }),
                TextColumn::make('classroom.name')
                    ->label('반')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('student.user.name')
                    ->label('학생')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('only_parent')
                    ->label('부모에게만')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('success')
                    ->falseColor('gray'),
                TextColumn::make('author.name')
                    ->label('작성자')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->date('Y-m-d')
                    ->label('작성일')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('공지 수정하기')
                    ->label(function ($record) {
                        if (auth()->user()->isRoleAbove('admin', true) || !auth()->user()->userable instanceof \App\Models\Teacher) {
                            return '수정';
                        }
                        return '조회';
                    })
                    ->icon(function ($record) {
                        if (auth()->user()->isRoleAbove('admin', true) || !auth()->user()->userable instanceof \App\Models\Teacher) {
                            return 'heroicon-m-pencil-square';
                        }
                        return 'heroicon-m-eye';
                    })
                    ->modalSubmitAction(function () {
                        if (!auth()->user()->isRoleAbove('admin', true) && auth()->user()->userable instanceof \App\Models\Teacher) {
                            return false;
                        }
                    })
                    ->modalWidth('4xl')
                    ->fillForm(function (StudentNotice $record): array {
                        $data = $record->attributesToArray();
                        if ($record->student_id) {
                            $data['target_type'] = 'student';
                        } elseif ($record->classroom_id) {
                            $data['target_type'] = 'class';
                        } else {
                            $data['target_type'] = 'all';
                        }
                        return $data;
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->modalHeading('공지 삭제'),
                ])
                    ->visible(function () {
                        return auth()->user()->isRoleAbove('admin', true);
                    }),
            ]);
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
            'index' => Pages\ListStudentNotices::route('/'),
        ];
    }
}
