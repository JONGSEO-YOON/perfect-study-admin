<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TossPaymentResource\Pages;
use App\Models\TossPayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class TossPaymentResource extends Resource
{
    protected static ?string $model = TossPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Toss 결제';

    protected static ?string $modelLabel = 'Toss 결제';

    protected static ?string $pluralModelLabel = 'Toss 결제';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('payment_id')
                    ->label('결제 ID')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('order_id')
                    ->label('주문번호')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\KeyValue::make('payment_info')
                    ->label('결제 정보')
                    ->keyLabel('항목')
                    ->valueLabel('값')
                    ->required(),
                Forms\Components\Textarea::make('payment_log')
                    ->label('결제 로그')
                    ->rows(4),
                Forms\Components\TextInput::make('success_url')
                    ->label('성공 URL')
                    ->required()
                    ->url()
                    ->maxLength(255),
                Forms\Components\TextInput::make('fail_url')
                    ->label('실패 URL')
                    ->required()
                    ->url()
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->label('결제 상태')
                    ->options([
                        'pending' => '대기중',
                        'success' => '성공',
                        'failed' => '실패',
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\TextInput::make('payment_key')
                    ->label('결제 키')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('approved_at')
                    ->label('승인 시간'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('order_id')
                    ->label('주문번호')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('payment_info')
                    ->label('결제 정보')
                    ->formatStateUsing(fn ($state) => is_array($state) ? 
                        collect($state)->map(fn($value, $key) => "$key: $value")->implode(', ') : 
                        $state
                    )
                    ->limit(50),
                TextColumn::make('status')
                    ->label('상태')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'success' => 'success',
                        'failed' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => '대기중',
                        'success' => '성공',
                        'failed' => '실패',
                    }),
                TextColumn::make('payment_key')
                    ->label('결제 키')
                    ->limit(30),
                TextColumn::make('approved_at')
                    ->label('승인 시간')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('생성일')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('수정일')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('결제 상태')
                    ->options([
                        'pending' => '대기중',
                        'success' => '성공',
                        'failed' => '실패',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListTossPayments::route('/'),
            'create' => Pages\CreateTossPayment::route('/create'),
            'view' => Pages\ViewTossPayment::route('/{record}'),
            'edit' => Pages\EditTossPayment::route('/{record}/edit'),
        ];
    }
}