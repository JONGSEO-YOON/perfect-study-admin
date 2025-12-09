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
use Filament\Tables\Filters\Filter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\DatePicker;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    // protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = '결제';

    protected static ?string $modelLabel = '결제';

    protected static ?string $pluralModelLabel = '결제';

    protected static ?string $navigationGroup = '결제';

    // protected static ?int $navigationSort = 5;

    public static function canViewAny(): bool
    {
        return auth()->user()->role == 'root_admin';
    }

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
                            function ($query) {
                                $query->with('user');

                                // 일반강사인 경우 자기 교실의 학생들만 필터링
                                if (auth()->user()->role === 'general') {
                                    $teacher = auth()->user()->userable;
                                    $classroomIds = $teacher->classrooms->pluck('id');
                                    $query->whereHas('classrooms', function ($query) use ($classroomIds) {
                                        $query->whereIn('classroom_id', $classroomIds);
                                    });
                                }

                                return $query;
                            }
                        )
                        ->getOptionLabelFromRecordUsing(fn($record) => $record->user->name ?? '')
                        ->getSearchResultsUsing(function (string $search) {
                            $query = \App\Models\Student::whereHas('user', function ($query) use ($search) {
                                $query->where('name', 'like', "%{$search}%");
                            });

                            // 일반강사인 경우 자기 교실의 학생들만 필터링
                            if (auth()->user()->role === 'general') {
                                $teacher = auth()->user()->userable;
                                $classroomIds = $teacher->classrooms->pluck('id');
                                $query->whereHas('classrooms', function ($query) use ($classroomIds) {
                                    $query->whereIn('classroom_id', $classroomIds);
                                });
                            }

                            return $query->with('user')
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
            ->modifyQueryUsing(function ($query) {
                // 일반강사인 경우 자기 교실의 학생들의 결제만 조회
                if (auth()->user()->role === 'general') {
                    $teacher = auth()->user()->userable;
                    $classroomIds = $teacher->classrooms->pluck('id');
                    $query->whereHas('student.classrooms', function ($query) use ($classroomIds) {
                        $query->whereIn('classroom_id', $classroomIds);
                    });
                }
                return $query;
            })
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
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('생성일')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('수정일')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filters([
                Filter::make('teacher_student')
                    ->form([
                        Forms\Components\Select::make('teacher')
                            ->label('담임')
                            ->options(function () {
                                $query = \App\Models\Teacher::with('user');

                                // 일반강사인 경우 자기 자신만 표시
                                if (auth()->user()->role === 'general') {
                                    $teacher = auth()->user()->userable;
                                    $query->where('id', $teacher->id);
                                }

                                return $query->get()->mapWithKeys(function ($teacher) {
                                    $label = $teacher->user?->name ?? '이름 없음';
                                    return [$teacher->id => $label];
                                });
                            })
                            ->placeholder('전체')
                            ->reactive()
                            ->afterStateUpdated(fn(Forms\Set $set) => $set('student', null)),
                        Forms\Components\Select::make('student')
                            ->label('학생')
                            ->options(function (Forms\Get $get) {
                                $teacherId = $get('teacher');

                                $query = \App\Models\Student::query();

                                if ($teacherId) {
                                    // 선택된 선생님의 교실에 속한 학생만 표시
                                    $classroomIds = \App\Models\Classroom::where('teacher_id', $teacherId)->pluck('id');
                                    $query->whereHas('classrooms', function ($q) use ($classroomIds) {
                                        $q->whereIn('classroom_id', $classroomIds);
                                    });
                                } else {
                                    // 선생님이 선택되지 않은 경우, 일반강사는 자기 교실 학생만
                                    if (auth()->user()->role === 'general') {
                                        $teacher = auth()->user()->userable;
                                        $classroomIds = $teacher->classrooms->pluck('id');
                                        $query->whereHas('classrooms', function ($q) use ($classroomIds) {
                                            $q->whereIn('classroom_id', $classroomIds);
                                        });
                                    }
                                }

                                return $query->with('user')
                                    ->get()
                                    ->pluck('user.name', 'id');
                            })
                            ->placeholder('전체 학생')
                            ->searchable(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['teacher'],
                                fn($query, $teacher) => $query->whereHas('student.classrooms', function ($q) use ($teacher) {
                                    $q->where('teacher_id', $teacher);
                                })
                            )
                            ->when(
                                $data['student'],
                                fn($query, $student) => $query->where('student_id', $student)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['teacher'] ?? null) {
                            $teacher = \App\Models\Teacher::with('user')->find($data['teacher']);
                            $indicators['teacher'] = '선생님: ' . ($teacher->user->name ?? '');
                        }

                        if ($data['student'] ?? null) {
                            $student = \App\Models\Student::with('user')->find($data['student']);
                            $indicators['student'] = '학생: ' . ($student->user->name ?? '');
                        }

                        return $indicators;
                    }),
                SelectFilter::make('payment_status')
                    ->label('결제 상태')
                    ->options([
                        'pending' => '대기중',
                        'paid' => '결제완료',
                        'cancelled' => '취소',
                        // 'completed' => '완료',
                    ]),
                Filter::make('created_from')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('시작날짜'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['created_from'],
                            fn($query, $date) => $query->whereDate('created_at', '>=', $date),
                        );
                    })
                    ->label('시작날짜'),
                Filter::make('created_until')
                    ->form([
                        DatePicker::make('created_until')
                            ->label('종료날짜'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['created_until'],
                            fn($query, $date) => $query->whereDate('created_at', '<=', $date),
                        );
                    })
                    ->label('종료날짜'),
            ])
            ->actions([
                Tables\Actions\Action::make('copy_payment_link')
                    ->label('결제링크')
                    ->icon('heroicon-o-link')
                    ->color('primary')
                    ->visible(fn($record) => !in_array($record->payment_status, ['paid', 'cancelled']))
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
                Tables\Actions\Action::make('cancel_payment')
                    ->label('결제취소')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->payment_status === 'paid')
                    ->requiresConfirmation()
                    ->modalHeading('결제 취소')
                    ->modalDescription('정말로 이 결제를 취소하시겠습니까? 취소된 결제는 되돌릴 수 없습니다.')
                    ->modalSubmitActionLabel('취소')
                    ->action(function ($record) {
                        try {
                            $tossController = new \App\Http\Controllers\TossPaymentController();
                            $response = $tossController->cancelPayment($record->payment_key, '관리자 요청');

                            $record->markAsCancelled(
                                '관리자 요청',
                                json_encode(['response' => $response])
                            );

                            \Filament\Notifications\Notification::make()
                                ->title('결제가 성공적으로 취소되었습니다.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('결제 취소 실패')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
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
