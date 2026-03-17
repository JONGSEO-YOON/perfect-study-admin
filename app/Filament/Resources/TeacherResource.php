<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherResource\Pages;
use App\Filament\Resources\TeacherResource\RelationManagers;
use App\Forms\Components\AddressInput;
use App\Forms\Components\PhoneInput;
use App\Models\Teacher;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class TeacherResource extends Resource
{
    protected static ?string $model = Teacher::class;

    protected static ?string $navigationLabel = '강사 관리';

    protected static ?int $navigationSort = 3;

    protected static bool $shouldRegisterNavigation = true;

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
                        FileUpload::make('profile_photo_path')
                            ->extraAttributes([
                                'class' => '!items-center'
                            ])
                            ->label('사진')
                            ->image()
                            ->avatar()
                            ->placeholder('사진 업로드')
                            ->columnSpanFull(),
                        Grid::make(1)
                            ->schema([
                                ToggleButtons::make('role')
                                    ->label('권한')
                                    ->required()
                                    ->options([
                                        'general' => '일반 강사',
                                        'manager' => '중간 관리자',
                                        'admin' => '관리자',
                                        'root_admin' => '최고 관리자',
                                    ])
                                    ->visible(fn($record) => auth()->user()->role == 'root_admin')
                                    ->inline()
                                    ->grouped()
                                    ->columnSpanFull()
                                    ->default('general'),
                            ])->relationship('userable'),

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
                            ->columnSpanFull(),
                        TextInput::make('username')
                            ->label('계정')
                            ->readOnly(fn($record) => $record?->id)
                            ->required(),

                        PhoneInput::make('phone')
                            ->label('전화번호')
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

                        // Grid::make(2)
                        //     ->schema([
                        //         Checkbox::make('is_admin')
                        //             ->label('관리자')
                        //             ->inlineLabel(),
                        //     ])
                        //     ->relationship('userable'),

                        TextInput::make('email')
                            ->label('이메일'),
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
                TextColumn::make('id')
                    ->label('No')
                    ->rowIndex(),
                TextColumn::make('user.name')
                    ->label('이름')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.username')
                    ->label('계정')
                    ->searchable()
                    ->visible(function () {
                        return !auth()->user()->userable instanceof \App\Models\Teacher
                            || auth()->user()->isRoleAbove('general');
                    })
                    ->sortable(),
                TextColumn::make('user.phone')
                    ->label('전화번호')
                    ->searchable()
                    ->visible(function () {
                        return !auth()->user()->userable instanceof \App\Models\Teacher
                            || auth()->user()->isRoleAbove('general');
                    })
                    ->sortable(),
                // TextColumn::make('is_admin')
                //     ->label('담당 반'),
                TextColumn::make('user.birthed_at')
                    ->date('Y-m-d')
                    ->visible(function () {
                        return !auth()->user()->userable instanceof \App\Models\Teacher
                            || auth()->user()->isRoleAbove('general');
                    })
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
                    ->modalHeading('강사 수정하기')
                    ->modalWidth('xl')
                    ->visible(function ($record) {
                        return auth()->user()->isRoleAboveOrSelf($record->user)
                            ||  (!auth()->user()->userable instanceof \App\Models\Teacher
                                && !$record->user->isRoleAbove('general'));
                    }),
                Tables\Actions\DeleteAction::make()
                    ->modalHeading('강사 삭제')
                    ->visible(function ($record) {
                        return ($record->user->id !== auth()->user()->id
                            && $record->user->role !== 'root_admin')
                            && (auth()->user()->isRoleAboveOrSelf($record->user)
                                ||  (!auth()->user()->userable instanceof \App\Models\Teacher
                                    && !$record->user->isRoleAbove('general')));
                    })
                    ->action(function ($record, Action $action) {
                        //if has classrooms
                        if (count($record->classrooms) > 0) {
                            Notification::make()
                                ->title('강사님께서 담당하고 있는 반이 있습니다.')
                                ->danger()
                                ->send();
                            $action->halt();
                            return;
                        }
                        $record->delete();
                        $action->success();
                    })
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make()
                //         ->modalHeading('강사 삭제'),
                // ]),
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
            'index' => Pages\ListTeachers::route('/'),
            // 'create' => Pages\CreateTeacher::route('/create'),
            // 'edit' => Pages\EditTeacher::route('/{record}/edit'),
        ];
    }
}
