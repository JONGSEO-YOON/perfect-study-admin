<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AcademyResource\Pages;
use App\Models\Academy;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class AcademyResource extends Resource
{
    protected static ?string $model = Academy::class;

    protected static ?string $navigationLabel = '학원 관리';

    protected static ?string $navigationGroup = '설정';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    public static function canViewAny(): bool
    {
        return auth()->user()->role === 'root_admin';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('기본 정보')
                ->schema([
                    TextInput::make('name')
                        ->label('학원 이름')
                        ->required()
                        ->afterStateUpdated(fn ($set, $state) => $set('slug', Str::slug($state)))
                        ->live(onBlur: true),
                    TextInput::make('slug')
                        ->label('슬러그')
                        ->required()
                        ->unique(ignoreRecord: true),
                    TextInput::make('phone')
                        ->label('전화번호'),
                    TextInput::make('address')
                        ->label('주소')
                        ->columnSpanFull(),
                    Toggle::make('is_active')
                        ->label('활성화')
                        ->default(true),
                ])->columns(2),

            Section::make('토스 페이먼츠 설정')
                ->description('학원별 결제 가맹점 키를 설정합니다.')
                ->schema([
                    TextInput::make('toss_client_key')
                        ->label('Client Key')
                        ->password()
                        ->revealable(),
                    TextInput::make('toss_secret_key')
                        ->label('Secret Key')
                        ->password()
                        ->revealable(),
                    TextInput::make('toss_customer_key')
                        ->label('Customer Key')
                        ->password()
                        ->revealable(),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('name')->label('학원 이름')->searchable(),
                TextColumn::make('slug')->label('슬러그'),
                IconColumn::make('is_active')->label('활성')->boolean(),
                TextColumn::make('users_count')
                    ->label('사용자 수')
                    ->counts('users'),
                TextColumn::make('students_count')
                    ->label('학생 수')
                    ->counts('students'),
                TextColumn::make('created_at')
                    ->label('생성일')
                    ->date('Y-m-d'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAcademies::route('/'),
            'create' => Pages\CreateAcademy::route('/create'),
            'edit' => Pages\EditAcademy::route('/{record}/edit'),
        ];
    }
}
