<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use App\Models\User;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = '결제';

    protected static ?string $modelLabel = '결제';

    protected static ?string $pluralModelLabel = '결제';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('사용자')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('student_id')
                    ->label('학생')
                    ->relationship('student', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->label('금액')
                    ->required()
                    ->numeric()
                    ->prefix('₩'),
                Forms\Components\Select::make('payment_status')
                    ->label('결제 상태')
                    ->options([
                        'pending' => '대기중',
                        'paid' => '결제완료',
                        'cancelled' => '취소',
                        'completed' => '완료',
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\TextInput::make('payment_method')
                    ->label('결제 방법')
                    ->maxLength(255),
                Forms\Components\TextInput::make('billing_name')
                    ->label('청구 이름')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('billing_memo')
                    ->label('청구 메모')
                    ->rows(3),
                Forms\Components\DateTimePicker::make('paid_at')
                    ->label('결제일시'),
                Forms\Components\DateTimePicker::make('cancelled_at')
                    ->label('취소일시'),
                Forms\Components\Textarea::make('cancel_reason')
                    ->label('취소 사유')
                    ->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('사용자')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('student.name')
                    ->label('학생')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('금액')
                    ->money('KRW')
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->label('결제 상태')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        'completed' => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => '대기중',
                        'paid' => '결제완료',
                        'cancelled' => '취소',
                        'completed' => '완료',
                    }),
                TextColumn::make('payment_method')
                    ->label('결제 방법')
                    ->toggleable(),
                TextColumn::make('billing_name')
                    ->label('청구 이름')
                    ->searchable(),
                TextColumn::make('paid_at')
                    ->label('결제일시')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
                TextColumn::make('cancelled_at')
                    ->label('취소일시')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('payment_status')
                    ->label('결제 상태')
                    ->options([
                        'pending' => '대기중',
                        'paid' => '결제완료',
                        'cancelled' => '취소',
                        'completed' => '완료',
                    ]),
                SelectFilter::make('payment_method')
                    ->label('결제 방법')
                    ->relationship('payment_method', 'payment_method'),
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'view' => Pages\ViewPayment::route('/{record}'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}