<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Exports\PaymentsExport;
use App\Filament\Resources\PaymentResource;
use App\Models\Classroom;
use App\Models\GradeSystem;
use App\Models\Payment;
use App\Models\PaymentSchedule;
use App\Models\Student;
use App\Services\FcmService;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('엑셀 다운로드')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('success')
                ->modalHeading('결제 목록 엑셀 다운로드')
                ->modalSubmitActionLabel('다운로드')
                ->modalWidth('md')
                ->form([
                    Grid::make(2)->schema([
                        DatePicker::make('start_date')
                            ->label('생성일 시작'),
                        DatePicker::make('end_date')
                            ->label('생성일 종료'),
                    ]),
                ])
                ->action(function (array $data) {
                    $filename = '결제목록_' . now()->format('Ymd_His') . '.xlsx';
                    return Excel::download(
                        new PaymentsExport($data['start_date'] ?? null, $data['end_date'] ?? null),
                        $filename
                    );
                }),

            Actions\CreateAction::make()
                ->label('개별 결제 생성'),

            Actions\Action::make('bulk_create')
                ->label('일괄 결제 생성')
                ->icon('heroicon-m-user-group')
                ->color('success')
                ->modalHeading('일괄 결제 생성')
                ->modalDescription('반별 또는 학년별로 결제를 일괄 생성합니다.')
                ->modalSubmitActionLabel('생성')
                ->modalWidth('lg')
                ->form([
                    Select::make('target_type')
                        ->label('대상 유형')
                        ->options([
                            'classroom' => '반별',
                            'grade' => '학년별',
                        ])
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(fn(\Filament\Forms\Set $set) => $set('target_ids', [])),
                    Select::make('target_ids')
                        ->label(fn(Get $get) => match ($get('target_type')) {
                            'classroom' => '반 선택',
                            'grade' => '학년 선택',
                            default => '대상 선택',
                        })
                        ->multiple()
                        ->options(function (Get $get) {
                            return match ($get('target_type')) {
                                'classroom' => Classroom::orderBy('name')->pluck('name', 'id')->toArray(),
                                'grade' => GradeSystem::orderBy('id')->pluck('name', 'id')->toArray(),
                                default => [],
                            };
                        })
                        ->required()
                        ->searchable()
                        ->preload()
                        ->visible(fn(Get $get) => filled($get('target_type'))),
                    TextInput::make('amount')
                        ->label('금액')
                        ->required()
                        ->numeric()
                        ->prefix('₩'),
                    TextInput::make('billing_name')
                        ->label('청구 이름')
                        ->required(),
                    Textarea::make('billing_memo')
                        ->label('청구 메모')
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    $studentIds = $this->resolveStudentIds($data['target_type'], $data['target_ids']);

                    if (empty($studentIds)) {
                        Notification::make()->title('대상 학생이 없습니다.')->warning()->send();
                        return;
                    }

                    $count = 0;
                    $fcmService = app(FcmService::class);

                    foreach ($studentIds as $studentId) {
                        try {
                            $payment = Payment::create([
                                'user_id' => auth()->id(),
                                'student_id' => $studentId,
                                'order_id' => 'ps-' . $studentId . date('YmdHis') . $count,
                                'amount' => $data['amount'],
                                'billing_name' => $data['billing_name'],
                                'billing_memo' => $data['billing_memo'] ?? null,
                                'payment_status' => 'pending',
                            ]);

                            $this->sendNotification($fcmService, $payment, $studentId);
                            $count++;
                        } catch (\Exception $e) {
                            Log::error("일괄 결제 생성 실패: 학생 {$studentId}", ['error' => $e->getMessage()]);
                        }
                    }

                    Notification::make()
                        ->title("{$count}건의 결제가 생성되었습니다.")
                        ->success()
                        ->send();
                }),

            Actions\Action::make('payment_schedules')
                ->label('예약 알림 관리')
                ->icon('heroicon-m-bell-alert')
                ->color('warning')
                ->modalHeading('예약 알림 관리')
                ->modalDescription('매월 지정한 날짜에 자동으로 결제를 생성하고 알림을 보냅니다.')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('닫기')
                ->modalWidth('5xl')
                ->modalContent(function () {
                    $schedules = PaymentSchedule::with('user')
                        ->orderBy('created_at', 'desc')
                        ->get();
                    return view('filament.payment-schedules', ['schedules' => $schedules]);
                }),

            Actions\Action::make('edit_schedule')
                ->hidden()
                ->modalHeading('예약 알림 수정')
                ->modalSubmitActionLabel('저장')
                ->modalWidth('lg')
                ->fillForm(function (array $arguments) {
                    $schedule = PaymentSchedule::find($arguments['schedule_id']);
                    return [
                        'schedule_id' => $schedule->id,
                        'target_type' => $schedule->target_type,
                        'target_ids' => $schedule->target_ids,
                        'amount' => $schedule->amount,
                        'billing_name' => $schedule->billing_name,
                        'billing_memo' => $schedule->billing_memo,
                        'send_day' => $schedule->send_day,
                    ];
                })
                ->form([
                    \Filament\Forms\Components\Hidden::make('schedule_id'),
                    Select::make('target_type')
                        ->label('대상 유형')
                        ->options([
                            'student' => '개별 학생',
                            'classroom' => '반별',
                            'grade' => '학년별',
                        ])
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(fn(\Filament\Forms\Set $set) => $set('target_ids', [])),
                    Select::make('target_ids')
                        ->label(fn(Get $get) => match ($get('target_type')) {
                            'student' => '학생 선택',
                            'classroom' => '반 선택',
                            'grade' => '학년 선택',
                            default => '대상 선택',
                        })
                        ->multiple()
                        ->options(function (Get $get) {
                            return match ($get('target_type')) {
                                'student' => Student::whereHas('user', fn($q) => $q->where('is_active', true))
                                    ->with('user')->get()
                                    ->mapWithKeys(fn($s) => [$s->id => $s->user->name])->toArray(),
                                'classroom' => Classroom::orderBy('name')->pluck('name', 'id')->toArray(),
                                'grade' => GradeSystem::orderBy('id')->pluck('name', 'id')->toArray(),
                                default => [],
                            };
                        })
                        ->required()
                        ->searchable()
                        ->preload()
                        ->visible(fn(Get $get) => filled($get('target_type'))),
                    TextInput::make('amount')
                        ->label('금액')
                        ->required()
                        ->numeric()
                        ->prefix('₩'),
                    TextInput::make('billing_name')
                        ->label('청구 이름')
                        ->required(),
                    Textarea::make('billing_memo')
                        ->label('청구 메모')
                        ->rows(2),
                    TextInput::make('send_day')
                        ->label('매월 발송일')
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(28)
                        ->helperText('1~28일 중 선택 (매월 해당 날짜에 자동 발송)'),
                ])
                ->action(function (array $data) {
                    $schedule = PaymentSchedule::find($data['schedule_id']);
                    $schedule->update([
                        'target_type' => $data['target_type'],
                        'target_ids' => $data['target_ids'],
                        'amount' => $data['amount'],
                        'billing_name' => $data['billing_name'],
                        'billing_memo' => $data['billing_memo'] ?? null,
                        'send_day' => $data['send_day'],
                        'next_send_at' => now()->day((int) $data['send_day'])->startOfDay(),
                    ]);

                    Notification::make()
                        ->title('예약 알림이 수정되었습니다.')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('create_schedule')
                ->label('예약 추가')
                ->icon('heroicon-m-plus-circle')
                ->color('warning')
                ->modalHeading('예약 알림 추가')
                ->modalSubmitActionLabel('저장')
                ->modalWidth('lg')
                ->form([
                    Select::make('target_type')
                        ->label('대상 유형')
                        ->options([
                            'student' => '개별 학생',
                            'classroom' => '반별',
                            'grade' => '학년별',
                        ])
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(fn(\Filament\Forms\Set $set) => $set('target_ids', [])),
                    Select::make('target_ids')
                        ->label(fn(Get $get) => match ($get('target_type')) {
                            'student' => '학생 선택',
                            'classroom' => '반 선택',
                            'grade' => '학년 선택',
                            default => '대상 선택',
                        })
                        ->multiple()
                        ->options(function (Get $get) {
                            return match ($get('target_type')) {
                                'student' => Student::whereHas('user', fn($q) => $q->where('is_active', true))
                                    ->with('user')->get()
                                    ->mapWithKeys(fn($s) => [$s->id => $s->user->name])->toArray(),
                                'classroom' => Classroom::orderBy('name')->pluck('name', 'id')->toArray(),
                                'grade' => GradeSystem::orderBy('id')->pluck('name', 'id')->toArray(),
                                default => [],
                            };
                        })
                        ->required()
                        ->searchable()
                        ->preload()
                        ->visible(fn(Get $get) => filled($get('target_type'))),
                    TextInput::make('amount')
                        ->label('금액')
                        ->required()
                        ->numeric()
                        ->prefix('₩'),
                    TextInput::make('billing_name')
                        ->label('청구 이름')
                        ->required(),
                    Textarea::make('billing_memo')
                        ->label('청구 메모')
                        ->rows(2),
                    TextInput::make('send_day')
                        ->label('매월 발송일')
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(28)
                        ->helperText('1~28일 중 선택 (매월 해당 날짜에 자동 발송)'),
                ])
                ->action(function (array $data) {
                    PaymentSchedule::create([
                        'user_id' => auth()->id(),
                        'target_type' => $data['target_type'],
                        'target_ids' => $data['target_ids'],
                        'amount' => $data['amount'],
                        'billing_name' => $data['billing_name'],
                        'billing_memo' => $data['billing_memo'] ?? null,
                        'send_day' => $data['send_day'],
                        'is_active' => true,
                        'next_send_at' => now()->day((int) $data['send_day'])->startOfDay(),
                    ]);

                    Notification::make()
                        ->title('예약 알림이 등록되었습니다.')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function openEditScheduleModal(int $scheduleId): void
    {
        // 외부 payment_schedules 모달 닫기 (있으면)
        try {
            $this->unmountAction();
        } catch (\Throwable $e) {
            // 무시: unmountAction이 모든 상황에서 안전하지 않을 수 있음
        }

        // 수정 모달 마운트
        $this->mountAction('edit_schedule', ['schedule_id' => $scheduleId]);
    }

    public function deletePaymentSchedule(int $scheduleId): void
    {
        $schedule = PaymentSchedule::find($scheduleId);
        if (!$schedule) {
            Notification::make()
                ->title('예약 알림을 찾을 수 없습니다.')
                ->danger()
                ->send();
            return;
        }

        $schedule->delete();

        Notification::make()
            ->title('예약 알림이 삭제되었습니다.')
            ->success()
            ->send();
    }

    public function togglePaymentSchedule(int $scheduleId): void
    {
        $schedule = PaymentSchedule::find($scheduleId);
        if (!$schedule) {
            Notification::make()
                ->title('예약 알림을 찾을 수 없습니다.')
                ->danger()
                ->send();
            return;
        }

        $schedule->update(['is_active' => !$schedule->is_active]);

        $label = $schedule->is_active ? '활성화' : '비활성화';
        Notification::make()
            ->title("예약 알림이 {$label}되었습니다.")
            ->success()
            ->send();
    }

    protected function resolveStudentIds(string $type, array $ids): array
    {
        return match ($type) {
            'classroom' => Student::whereHas('classrooms', fn($q) => $q->whereIn('classroom_id', $ids))
                ->whereHas('user', fn($q) => $q->where('is_active', true))
                ->pluck('id')->toArray(),
            'grade' => Student::whereIn('grade_system_id', $ids)
                ->whereHas('user', fn($q) => $q->where('is_active', true))
                ->pluck('id')->toArray(),
            default => [],
        };
    }

    protected function sendNotification(FcmService $fcmService, Payment $payment, int $studentId): void
    {
        try {
            $student = Student::with('user')->find($studentId);
            if (!$student?->user) return;

            $parentPhones = array_unique(array_filter([
                $student->phone_father,
                $student->phone_mother,
            ]));

            $title = "💳 {$student->user->name} 학생 결제 알림";
            $body = "{$student->user->name} 학생의 새로운 결제가 생성되었습니다. 결제명: {$payment->billing_name} / 금액: " . number_format($payment->amount) . "원";

            foreach ($parentPhones as $phone) {
                $fcmService->sendToParent($phone, $title, $body, [
                    'type' => 'payment',
                    'title' => $title,
                    'body' => $body,
                    'payment_id' => $payment->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("결제 알림 전송 실패: 학생 {$studentId}", ['error' => $e->getMessage()]);
        }
    }
}
