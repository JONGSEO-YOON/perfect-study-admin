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
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
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
                        Section::make('section')
                            ->label('강의 범위')
                            ->heading('강의 범위')
                            ->collapsible()
                            ->columnSpanFull()
                            ->schema([
                                Grid::make(3)->schema([
                                    Tabs::make('tabs')
                                        ->tabs([
                                            Tab::make('tab1')
                                                ->label('수1')
                                                ->columns(3)
                                                ->schema([
                                                    CheckboxList::make('scopes')
                                                        ->label('지수와 로그')
                                                        ->options([
                                                            '지수와 로그의 단순계산' => '지수와 로그의 단순계산',
                                                            '지수 법칙의 연산' => '지수 법칙의 연산',
                                                            '제곱근' => '제곱근',
                                                            '자연수,정수,유리수가 될 조건' => '자연수,정수,유리수가 될 조건',
                                                            '로그 계산의 활용' => '로그 계산의 활용',
                                                            '식을 이용한 지수, 로그의 풀이' => '식을 이용한 지수, 로그의 풀이',
                                                            '지수의 실생활의 활용' => '지수의 실생활의 활용',
                                                        ])
                                                        ->columns(1),
                                                    CheckboxList::make('scopes')
                                                        ->label('지수함수,로그함수')
                                                        ->options([
                                                            '지수,로그함수 그래프 정의' => '지수,로그함수 그래프 정의',
                                                            '지수,로그함수 그래프 이용(1)-교점이용' => '지수,로그함수 그래프 이용(1)-교점이용',
                                                            '지수,로그함수 그래프 이용(2)-직선, 역함수, 평행이동 이용' => '지수,로그함수 그래프 이용(2)-직선, 역함수, 평행이동 이용',
                                                            '지수,로그함수 그래프 이용(3)-최대,최소' => '지수,로그함수 그래프 이용(3)-최대,최소',
                                                            '지수,로그함수 그래프의 활용-길이' => '지수,로그함수 그래프의 활용-길이',
                                                            '지수,로그함수 그래프의 활용-넓이' => '지수,로그함수 그래프의 활용-넓이',
                                                            '지수,로그함수의 그래프 이용하기-점의 개수 구하기' => '지수,로그함수의 그래프 이용하기-점의 개수 구하기',
                                                            '지수, 로그 방정식' => '지수, 로그 방정식',
                                                            '지수, 로그 부등식' => '지수, 로그 부등식',
                                                            '지수, 로그함수의 대소관계' => '지수, 로그함수의 대소관계',
                                                            '지수, 로그함수의 대소관계-그래프이용' => '지수, 로그함수의 대소관계-그래프이용',
                                                        ])
                                                        ->columns(1),
                                                    CheckboxList::make('scopes')
                                                        ->label('삼각함수')
                                                        ->options([
                                                            '삼각비를 이용한 단순계산' => '삼각비를 이용한 단순계산',
                                                            '도형을 이용한 삼각비 계산' => '도형을 이용한 삼각비 계산',
                                                            '부채꼴의 호의 길이, 넓이' => '부채꼴의 호의 길이, 넓이',
                                                            '삼각함수 그래프(기본)' => '삼각함수 그래프(기본)',
                                                            '삼각함수의 최대, 최소' => '삼각함수의 최대, 최소',
                                                            '삼각함수의 방정식(특수각)' => '삼각함수의 방정식(특수각)',
                                                            '삼각함수의 방정식(특수각이 아닌경우)' => '삼각함수의 방정식(특수각이 아닌경우)',
                                                            '삼각함수 그래프의 교점의 개수' => '삼각함수 그래프의 교점의 개수',
                                                            '삼각함수의 부등식' => '삼각함수의 부등식',
                                                            '삼각함수 그래프의 활용' => '삼각함수 그래프의 활용',
                                                            '사인법칙, 코사인법칙' => '사인법칙, 코사인법칙',
                                                            '삼각함수 넓이의 활용' => '삼각함수 넓이의 활용',
                                                        ])
                                                        ->columns(1),
                                                    CheckboxList::make('scopes')
                                                        ->label('수열')
                                                        ->options([
                                                            '등차,등비의 일반항 이용' => '등차,등비의 일반항 이용',
                                                            '등차,등비의 합' => '등차,등비의 합',
                                                            '시그마 성질 및 단순계산' => '시그마 성질 및 단순계산',
                                                            '합과 일반항의 관계' => '합과 일반항의 관계',
                                                            '시그마의 여러가지 계산' => '시그마의 여러가지 계산',
                                                            '등차,등비 및 시그마의 활용' => '등차,등비 및 시그마의 활용',
                                                            '점화식' => '점화식',
                                                            '수학적귀납법' => '수학적귀납법',
                                                            '좌표를 이용한 수열 구하기' => '좌표를 이용한 수열 구하기',
                                                            '도형을 이용한 수열 구하기' => '도형을 이용한 수열 구하기',
                                                        ])
                                                        ->columns(1),
                                                ]),

                                            Tab::make('tab2')
                                                ->label('수2')
                                                ->columns(3)
                                                ->schema([
                                                    CheckboxList::make('scopes')
                                                        ->label('다항 함수의 극한과 연속')
                                                        ->options([
                                                            '극한 단순 계산 문제' => '극한 단순 계산 문제',
                                                            '극한의 변형문제' => '극한의 변형문제',
                                                            '극한의 미정계수 구하기' => '극한의 미정계수 구하기',
                                                            '함수값을 이용한 극한값 계산' => '함수값을 이용한 극한값 계산',
                                                            '샌드위치 정리' => '샌드위치 정리',
                                                            '극한의 활용' => '극한의 활용',
                                                            '연속일 조건' => '연속일 조건',
                                                            '두 함수의 곱이 연속일 조건' => '두 함수의 곱이 연속일 조건',
                                                            '합성함수의 연속' => '합성함수의 연속',
                                                            '연속과 불연속의 활용' => '연속과 불연속의 활용',
                                                            '함수의 극한과 연속 합답형' => '함수의 극한과 연속 합답형',
                                                            '그래프를 이용한 함수의 극한' => '그래프를 이용한 함수의 극한',
                                                            '그래프를 이용한 함수의 연속' => '그래프를 이용한 함수의 연속',
                                                            '그래프를 이용한 함수의 극한과 연속(합성 함수)' => '그래프를 이용한 함수의 극한과 연속(합성 함수)',
                                                            '사잇값 정리' => '사잇값 정리',
                                                        ])
                                                        ->columns(1),
                                                    CheckboxList::make('scopes')
                                                        ->label('다항함수 미분')
                                                        ->options([
                                                            '평균변화율, 미분계수 이용' => '평균변화율, 미분계수 이용',
                                                            '미분법 공식을 이용한 단순계산' => '미분법 공식을 이용한 단순계산',
                                                            '미분 가능조건' => '미분 가능조건',
                                                        ])
                                                        ->columns(1),
                                                    CheckboxList::make('scopes')
                                                        ->label('다항함수 미분(도함수 활용1)')
                                                        ->options([
                                                            '접선의 방정식' => '접선의 방정식',
                                                            '접선의 방정식의 활용' => '접선의 방정식의 활용',
                                                            '증가와 감소' => '증가와 감소',
                                                            '극대와 극소' => '극대와 극소',
                                                            '그래프 개형을 이용한 극대, 극소' => '그래프 개형을 이용한 극대, 극소',
                                                        ])
                                                        ->columns(1),
                                                    CheckboxList::make('scopes')
                                                        ->label('다항함수 미분(도함수 활용2)')
                                                        ->options([
                                                            '그래프의 최대, 최소' => '그래프의 최대, 최소',
                                                            '그래프의 최대, 최소(범위가 문자)' => '그래프의 최대, 최소(범위가 문자)',
                                                            '그래프(절댓값을 포함한 미분가능)' => '그래프(절댓값을 포함한 미분가능)',
                                                            '그래프의 근의 개수' => '그래프의 근의 개수',
                                                            '범위가 쪼개어져 있는 그래프의 근의개수' => '범위가 쪼개어져 있는 그래프의 근의개수',
                                                            '그래프 개형을 이용한 활용문제' => '그래프 개형을 이용한 활용문제',
                                                            '그래프 개형을 이용한 미분가능' => '그래프 개형을 이용한 미분가능',
                                                            '조건을 이용한 그래프의 유추' => '조건을 이용한 그래프의 유추',
                                                            '거리,속도, 가속도' => '거리,속도, 가속도',
                                                            '미분의 활용' => '미분의 활용',
                                                            '미분의 합답형' => '미분의 합답형',
                                                            '조건을 이용한 미분의 합답형' => '조건을 이용한 미분의 합답형',
                                                        ])
                                                        ->columns(1),
                                                    CheckboxList::make('scopes')
                                                        ->label('다항함수 적분(도함수 활용2)')
                                                        ->options([
                                                            '단순적분계산' => '단순적분계산',
                                                            '정적분의 계산' => '정적분의 계산',
                                                            '적분과 미분의 계산 활용' => '적분과 미분의 계산 활용',
                                                            '넓이 및 부피' => '넓이 및 부피',
                                                            '평행이동 및 주기, 대칭성을 이용한 적분값 계산' => '평행이동 및 주기, 대칭성을 이용한 적분값 계산',
                                                            '조건을 이용한 적분값 계산' => '조건을 이용한 적분값 계산',
                                                            '합답형' => '합답형',
                                                            '그래프 개형을 이용한 적분값 계산' => '그래프 개형을 이용한 적분값 계산',
                                                            '거리와 속도' => '거리와 속도',
                                                        ])
                                                        ->columns(1),
                                                ]),
                                        ])
                                        ->columnSpanFull(),
                                ]),
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
                TextColumn::make('scopes')
                    ->formatStateUsing(function ($state, $record) {
                        if (count($record->scopes) > 3) {
                            return implode(', ', array_slice($record->scopes, 0, 3)) . ' 외 ' . (count($record->scopes) - 3) . '개';
                        }
                        return implode(', ', $record->scopes);
                    })
                    ->label('범위'),
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
                    ->label('강의 관리')
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
