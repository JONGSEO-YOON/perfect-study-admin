<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AcademyResource\Pages;
use App\Models\Academy;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AcademyResource extends Resource
{
    protected static ?string $model = Academy::class;

    protected static ?string $navigationGroup = '설정';

    protected static ?int $navigationSort = 0;

    protected static ?string $navigationLabel = '학원 관리';

    protected static ?string $modelLabel = '학원';

    protected static ?string $pluralModelLabel = '학원';

    public static function getBreadcrumb(): string
    {
        return '학원 관리';
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->role === 'root_admin';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('기본 정보')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('학원명')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('code')
                                ->label('학원 코드')
                                ->unique(ignoreRecord: true)
                                ->maxLength(50),
                        ]),
                        Grid::make(2)->schema([
                            TextInput::make('contact_phone')
                                ->label('연락처')
                                ->tel()
                                ->maxLength(20),
                            TextInput::make('contact_email')
                                ->label('이메일')
                                ->email()
                                ->maxLength(255),
                        ]),
                        Toggle::make('is_active')
                            ->label('활성 상태')
                            ->default(true)
                            ->inline(),
                    ]),
                
                Section::make('사업자 정보')
                    ->description('학부모 앱 하단에 표시되는 정보입니다.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('business_name')
                                ->label('상호명')
                                ->maxLength(255),
                            TextInput::make('business_number')
                                ->label('사업자등록번호')
                                ->placeholder('000-00-00000')
                                ->maxLength(20),
                        ]),
                        Grid::make(2)->schema([
                            TextInput::make('representative_name')
                                ->label('대표자명')
                                ->maxLength(100),
                            TextInput::make('business_phone')
                                ->label('사업장 전화번호')
                                ->tel()
                                ->maxLength(20),
                        ]),
                        Textarea::make('business_address')
                            ->label('사업장 주소')
                            ->rows(2),
                    ]),
                
                Section::make('메모')
                    ->schema([
                        Textarea::make('memo')
                            ->label('메모')
                            ->rows(3),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('No')
                    ->rowIndex(),
                TextColumn::make('name')
                    ->label('학원명')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label('코드')
                    ->searchable(),
                TextColumn::make('business_name')
                    ->label('상호명')
                    ->placeholder('-'),
                TextColumn::make('business_number')
                    ->label('사업자번호')
                    ->placeholder('-'),
                TextColumn::make('contact_phone')
                    ->label('연락처')
                    ->placeholder('-'),
                IconColumn::make('is_active')
                    ->label('활성')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('등록일')
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('학원 수정')
                    ->modalWidth('2xl'),
                Tables\Actions\DeleteAction::make()
                    ->modalHeading('학원 삭제'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('학원이 없습니다.')
            ->emptyStateDescription('새 학원을 등록해주세요.');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAcademies::route('/'),
        ];
    }
}
