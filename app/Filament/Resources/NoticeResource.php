<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NoticeResource\Pages;
use App\Filament\Resources\NoticeResource\RelationManagers;
use App\Models\Notice;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class NoticeResource extends Resource
{
    protected static ?string $model = Notice::class;

    protected static ?string $navigationLabel = '공지사항';

    protected static ?string $title = '공지사항';

    protected static ?int $navigationSort = 1;

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
                                CheckboxList::make('target_groups')
                                    ->label('공지 대상')
                                    ->required()
                                    ->options([
                                        '관리자' => '중간 관리자',
                                        '강사' => '강사',
                                        '학생' => '학생',
                                        '상담실' => '상담실',
                                    ])
                                    ->default(['관리자', '강사', '학생', '상담실'])
                                    ->columns(4)
                                    ->columnSpan(3),
                            ])->columnSpanFull(),
                        TextInput::make('title')
                            ->label('제목')
                            ->required(),
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
                $query->orderBy('pinned_at', 'desc')
                    ->when(!auth()->user()->isRoleAbove('admin', true), function ($query) {
                        if (auth()->user()->role === 'manager') {
                            $query->whereJsonContains('target_groups', '관리자')
                                ->orWhereJsonContains('target_groups', '"관리자"');
                        } else if (auth()->user()->role === 'general') {
                            $query->whereJsonContains('target_groups', '강사')
                                ->orWhereJsonContains('target_groups', '"강사"');
                        }
                    });
            })
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('공지사항이 없습니다.')
            ->columns([
                //
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
                TextColumn::make('target_groups')
                    ->label('공지 대상'),
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
                    ->modalWidth('4xl'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->modalHeading('공지 삭제'),
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
            'index' => Pages\ListNotices::route('/'),
        ];
    }
}
