<?php

namespace App\Filament\Resources;

use AddressInfo;
use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers;
use App\Forms\Components\AddressInput;
use App\Forms\Components\PhoneInput;
use App\Models\GradeSystem;
use App\Models\School;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Psy\VersionUpdater\Checker;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    // protected static ?string $navigationIcon = 'heroicon-m-academic-cap';

    protected static ?string $navigationGroup = '교실 관리';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = '학생 관리';

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
                        FileUpload::make('profile_photo_path')
                            ->extraAttributes([
                                'class' => '!items-center'
                            ])
                            ->label('사진')
                            ->image()
                            ->avatar()
                            ->placeholder('사진 업로드')
                            ->columnSpanFull(),
                        TextInput::make('name')
                            ->label('이름')
                            ->required(),
                        DatePicker::make('birthed_at')
                            ->label('생년월일')
                            ->required(),
                        Grid::make(2)
                            ->schema([
                                Radio::make('gender')
                                    ->label('성별')
                                    ->inlineLabel()
                                    ->inline()
                                    ->required()
                                    ->options([
                                        '남' => '남',
                                        '여' => '여',
                                    ]),
                            ]),
                        Hidden::make('address'),
                        Hidden::make('postal_code'),
                        AddressInput::make('address-input')
                            ->label('주소')
                            ->required()
                            ->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                Select::make('school_id')
                                    ->label('학교')
                                    ->nullable()
                                    ->getSearchResultsUsing(fn(string $search): array => School::where('name', 'like', "%{$search}%")->limit(10)
                                        ->get()
                                        ->map(function ($school) {
                                            return [
                                                'id' => $school->id,
                                                'name' => $school->name . ' - ' . $school->province
                                            ];
                                        })
                                        ->pluck('name', 'id')->toArray())
                                    ->searchable()
                                    ->preload(10),
                                Select::make('grade_system_id')
                                    ->label('학년')
                                    ->required()
                                    ->options(function () {
                                        return GradeSystem::query()
                                            ->orderBy('sequential_order')
                                            ->pluck(
                                                'display_name',
                                                'id',
                                            );
                                    }),
                            ])->relationship('userable'),
                        TextInput::make('email')
                            ->label('이메일'),
                        Grid::make(2)
                            ->schema([
                                PhoneInput::make('phone')
                                    ->label('전화번호 (본인)'),
                                PhoneInput::make('landline')
                                    ->label('전화번호 (자택)'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                PhoneInput::make('phone_mother')
                                    ->label('전화번호 (모)'),
                                PhoneInput::make('phone_father')
                                    ->label('전화번호 (부)'),
                                Toggle::make('sms_agree')
                                    ->label('SMS 수신 여부')
                                    ->inlineLabel()
                                    ->inline()
                                    ->columnSpanFull()
                                    ->default(true),
                                CheckboxList::make('sms_targets')
                                    ->label('SMS 수신 대상')
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->options([
                                        'self' => '본인',
                                        'father' => '부',
                                        'mother' => '모',
                                    ])
                                    ->columns(3),
                            ])->relationship('userable'),
                        TextInput::make('username')
                            ->label('계정')
                            ->readOnly(fn($record) => $record?->id)
                            ->required(),
                        TextInput::make('password')
                            ->confirmed()
                            ->password()
                            ->label('비밀번호')
                            ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
                            ->dehydrated(fn(?string $state): bool => filled($state))
                            ->required(fn(string $operation): bool => $operation === 'create'),
                        TextInput::make('password_confirmation')
                            ->password()
                            ->dehydrated(false)
                            ->label('비밀번호 확인')
                            ->required(fn(string $operation): bool => $operation === 'create'),
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('initially_attended_at')
                                    ->label('최초 수강일')
                            ])
                            ->relationship('userable'),
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
                    ])
                    ->relationship('user')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                //
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
                    ->formatStateUsing(function ($record) {
                        return $record->gradeSystem->display_name;
                    })
                    ->sortable(),
                TextColumn::make('user.phone')
                    ->label('전화번호')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.birthed_at')
                    ->date('Y-m-d')
                    ->label('생년월일')
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
                Tables\Actions\EditAction::make()
                    ->modalHeading('학생 수정하기')
                    ->modalWidth('xl'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListStudents::route('/'),
            // 'create' => Pages\CreateStudent::route('/create'),
            // 'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
