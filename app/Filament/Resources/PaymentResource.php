<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    // protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = '결제';

    protected static ?string $modelLabel = '결제';

    protected static ?string $pluralModelLabel = '결제';

    protected static ?string $navigationGroup = '결제';

    // protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema(function ($record) {
                $baseFields = [
                    Forms\Components\Select::make('student_id')
                        ->label('학생')
                        ->relationship(
                            'student',
                            'id',
                            fn($query) => $query->with('user')
                        )
                        ->getOptionLabelFromRecordUsing(fn($record) => $record->user->name ?? '')
                        ->getSearchResultsUsing(function (string $search) {
                            return \App\Models\Student::whereHas('user', function ($query) use ($search) {
                                $query->where('name', 'like', "%{$search}%");
                            })
                                ->with('user')
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn($record) => [$record->id => $record->user->name ?? '']);
                        })
                        ->searchable()
                        ->required(),
                    Forms\Components\TextInput::make('amount')
                        ->label('금액')
                        ->required()
                        ->numeric()
                        ->prefix('₩'),
                    Forms\Components\TextInput::make('billing_name')
                        ->label('청구 이름')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Textarea::make('billing_memo')
                        ->label('청구 메모')
                        ->rows(3),
                ];

                if ($record && $record->payment_status !== 'pending') {
                    $baseFields = array_merge($baseFields, [
                        Forms\Components\TextInput::make('order_id')
                            ->label('주문번호')
                            ->disabled(),
                        Forms\Components\Select::make('payment_status')
                            ->label('결제 상태')
                            ->options([
                                'pending' => '대기중',
                                'paid' => '결제완료',
                                'cancelled' => '취소',
                                'completed' => '완료',
                            ])
                            ->disabled(),
                        Forms\Components\TextInput::make('payment_method')
                            ->label('결제 방법')
                            ->disabled(),
                        Forms\Components\TextInput::make('payment_key')
                            ->label('결제 키')
                            ->disabled(),
                        Forms\Components\Textarea::make('payment_log')
                            ->label('결제 로그')
                            ->rows(3)
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('approved_at')
                            ->label('결제 승인 시간')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('cancelled_at')
                            ->label('취소 시간')
                            ->disabled(),
                        Forms\Components\Textarea::make('cancel_reason')
                            ->label('취소 사유')
                            ->rows(2)
                            ->disabled(),
                    ]);
                }

                return $baseFields;
            });
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('생성자')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('student.user.name')
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
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        'completed' => 'info',
                        'failed' => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => '대기중',
                        'paid' => '결제완료',
                        'cancelled' => '취소',
                        'completed' => '완료',
                        'failed' => '실패',
                    }),

                TextColumn::make('billing_name')
                    ->label('청구 이름')
                    ->searchable(),
                TextColumn::make('payment_method')
                    ->label('결제 방법')
                    ->toggleable(),
                TextColumn::make('approved_at')
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
                    ->options([
                        'card' => '카드',
                        'transfer' => '계좌이체',
                        'virtual_account' => '가상계좌',
                        'mobile' => '휴대폰',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('copy_payment_link')
                    ->label('결제링크')
                    ->icon('heroicon-o-link')
                    ->color('primary')
                    ->visible(fn($record) => $record->payment_status === 'pending')
                    ->modalContent(function ($record) {
                        $paymentUrl = route('payment', ['paymentId' => $record->id]);
                        return view('filament.copy-payment-link', [
                            'paymentUrl' => $paymentUrl
                        ]);
                    })
                    ->modalWidth('md')
                    ->modalCancelAction(false)
                    ->modalSubmitAction(false),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn($record) => $record->payment_status === 'pending'),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn($record) => $record->payment_status === 'pending'),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(fn($records) => $records->filter(fn($record) => $record->payment_status === 'pending')->each->delete()),
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
