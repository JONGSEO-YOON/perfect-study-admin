<?php

namespace App\Filament\Resources\TestSheetResource\Pages;

use App\Filament\Resources\TestSheetResource;
use App\Models\TempData;
use Filament\Actions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\ListRecords;

class ListTestSheets extends ListRecords
{
    protected static string $resource = TestSheetResource::class;

    protected static ?string $title = '문제지 관리';

    public function getBreadcrumb(): ?string
    {
        return null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-m-plus-circle')
                ->modalHeading('문제지 추가하기')
                ->modalWidth('2xl')
                ->createAnother(false)
                ->label('문제지 추가하기')
                ->form([
                    ViewField::make('question_type_ids')
                        ->label('문제 유형')
                        ->view('filament.components.forms.question-type', [
                            'multiple' => true,
                        ])
                        ->live()
                        ->required()
                        ->columnSpanFull(),
                    Grid::make(7)
                        ->schema([
                            ToggleButtons::make('question_count_choice')
                                ->dehydrated(false)
                                ->label('문제 수')
                                ->inline()
                                ->options([
                                    25 => 25,
                                    50 => 50,
                                    75 => 75,
                                    100 => 100,
                                ])
                                ->default(50)
                                ->columnSpan(3)
                                ->afterStateUpdated(function (Set $set, $state) {
                                    $set('question_count', $state);
                                })
                                ->live(),
                            TextInput::make('question_count')
                                ->label('직접 입력')
                                ->required()
                                ->integer()
                                ->columnSpan(2)
                                ->default(50)
                                ->afterStateUpdated(function (Set $set, $state) {
                                    if ($state == 25 || $state == 50 || $state == 75 || $state == 100) {
                                        $set('question_count_choice', $state);
                                    } else {
                                        $set('question_count_choice', null);
                                    }
                                })
                                ->live(),
                        ])
                        ->columnSpanFull(),
                    Grid::make(3)
                        ->schema([
                            Select::make('levels')
                                ->label('레벨')
                                ->options([
                                    1 => '1',
                                    2 => '2',
                                    3 => '3',
                                    4 => '4',
                                    5 => '5',
                                ])
                                ->multiple()
                                ->live()
                                ->required(),
                            Checkbox::make('is_even_distribution')
                                ->columnStart(1)
                                ->default(true)
                                ->live()
                                ->label('레별별 문제 균등 분배'),
                            Grid::make(5)
                                ->schema(function (Get $get) {
                                    $textInput = [];
                                    $levels = $get('levels');
                                    //levels is array
                                    for ($i = 0; $i < count($levels); $i++) {
                                        $textInput[] = TextInput::make('level.' . $levels[$i])
                                            ->label('레벨 ' . $levels[$i] . ' (가중치)')
                                            ->required()
                                            ->integer()
                                            ->default(0);
                                    }
                                    return $textInput;
                                })
                                ->visible(function (Get $get) {
                                    return !$get('is_even_distribution');
                                })
                        ]),
                    Checkbox::make('should_exclude_recent_questions')
                        ->columnStart(1)
                        ->default(true)
                        ->live()
                        ->label('최근 출제 문제 제외 (1달)'),
                    Checkbox::make('is_tag_based')
                        ->columnStart(1)
                        ->default(false)
                        ->live()
                        ->label('태그별 출제'),
                    TagsInput::make('tags')
                        ->label('태그')
                        ->separator(',')
                        ->default([])
                        ->placeholder('태그를 입력하세요.')
                        ->visible(function (Get $get) {
                            return $get('is_tag_based');
                        })
                        ->columnSpanFull(),

                ])
                ->action(function ($data) {
                    $tempData = TempData::create([
                        'value' => $data,
                    ]);
                    redirect('/admin/test-sheets/create/' . $tempData->id);
                })
                ->modalSubmitActionLabel('문제 선택'),
        ];
    }
}
