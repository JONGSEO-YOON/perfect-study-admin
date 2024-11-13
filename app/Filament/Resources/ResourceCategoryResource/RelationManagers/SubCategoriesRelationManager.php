<?php

namespace App\Filament\Resources\ResourceCategoryResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubCategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'subCategories';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('하위 분류')
                            ->required()
                            ->maxLength(255)
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
                    ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('하위 분류가 없습니다.')
            ->emptyStateDescription('하위 분류를 추가해보세요.')
            ->heading('하위 분류')
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('하위 분류'),
                Tables\Columns\TextColumn::make('order')
                    ->label('순서'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-m-plus-circle')
                    ->label('하위 분류 추가하기')
                    ->modalHeading('하위 분류 추가하기')
                    ->modalWidth('lg')
                    ->createAnother(false)
                    ->modalSubmitActionLabel('저장'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalWidth('lg'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order', 'asc');
    }
}
