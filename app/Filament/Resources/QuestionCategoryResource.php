<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionCategoryResource\Pages;
use App\Filament\Resources\QuestionCategoryResource\RelationManagers;
use App\Filament\Resources\QuestionCategoryResource\RelationManagers\ChildrenRelationManager;
use App\Models\QuestionCategory;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuestionCategoryResource extends Resource
{
    protected static ?string $model = QuestionCategory::class;

    protected static ?string $navigationLabel = '문제 유형표 관리';

    protected static ?string $title = '문제 유형표 관리';

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationGroup = '문제 관리';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        if (!\App\Models\Academy::isMenuGroupVisibleForCurrentUser('tests')) {
            return false;
        }
        return auth()->user()->role == 'root_admin';
    }

    public static function getBreadcrumb(): string
    {
        return '문제 유형표 관리';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Grid::make(2)
                    ->schema([
                        // TextInput::make('name')
                        //     ->label('이름')
                        //     ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // ->defaultSort('order', 'asc')
            ->modifyQueryUsing(function (Builder $query) {
                $query->orderBy('depth');
            })
            ->columns([
                //
                ViewColumn::make('content')
                    ->view('filament.components.columns.question-category')
                    ->label('이름'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                ViewEntry::make('name')
                    ->view('filament.components.infolists.question-category-ancestors')
                    ->label(null)
                    ->columnSpanFull(true),
            ]);
    }

    #

    public static function getRelations(): array
    {
        return [
            //
            ChildrenRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestionCategories::route('/'),
            // 'create' => Pages\CreateQuestionCategory::route('/create'),
            // 'edit' => Pages\EditQuestionCategory::route('/{record}/edit'),
            'view' => Pages\ViewQuestionCategory::route('/{record}'),
        ];
    }
}
