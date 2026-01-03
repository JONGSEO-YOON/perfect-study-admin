<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResourceCategoryResource\Pages;
use App\Filament\Resources\ResourceCategoryResource\RelationManagers;
use App\Models\ResourceCategory;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ResourceCategoryResource extends Resource
{
    protected static ?string $model = ResourceCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = '자료실 관리';

    public static function getBreadcrumb(): string
    {
        return '';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Grid::make(3)
                    ->schema([

                        TextInput::make('name')
                            ->label('분류')
                            ->columnSpan(2),
                        Select::make('order')
                            ->label('순서')
                            ->options([
                                '1' => '1',
                                '2' => '2',
                                '3' => '3',
                                '4' => '4',
                                '5' => '5',
                                '6' => '6',
                                '7' => '7',
                                '8' => '8',
                                '9' => '9',
                                '10' => '10'
                            ])
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('name')
                    ->label('분류'),
                TextColumn::make('order')
                    ->label('순서')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->modalHeading('자료실 분류 삭제'),
                ]),
            ])
            ->defaultSort('order', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            //
            RelationManagers\SubCategoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListResourceCategories::route('/'),
            // 'create' => Pages\CreateResourceCategory::route('/create'),
            'edit' => Pages\EditResourceCategory::route('/{record}/edit'),
        ];
    }
}
