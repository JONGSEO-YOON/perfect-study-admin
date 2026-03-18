<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\PaymentSchedule;
use App\Models\Student;
use App\Services\FcmService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendScheduledPayments extends Command
{
    protected $signature = 'payments:send-scheduled';
    protected $description = '예약된 결제 알림을 발송합니다';

    public function handle()
    {
        $today = now()->day;

        $schedules = PaymentSchedule::where('is_active', true)
            ->where('send_day', $today)
            ->where(function ($q) {
                $q->whereNull('last_sent_at')
                    ->orWhereMonth('last_sent_at', '!=', now()->month)
                    ->orWhereYear('last_sent_at', '!=', now()->year);
            })
            ->get();

        $this->info("발송 대상 예약: {$schedules->count()}건");

        foreach ($schedules as $schedule) {
            $this->processSchedule($schedule);
        }

        $this->info('완료');
    }

    protected function processSchedule(PaymentSchedule $schedule): void
    {
        $studentIds = $schedule->getStudentIds();
        $this->info("예약 #{$schedule->id}: 학생 " . count($studentIds) . "명 대상");

        $fcmService = app(FcmService::class);

        foreach ($studentIds as $studentId) {
            try {
                $student = Student::with('user')->find($studentId);
                if (!$student || !$student->user) continue;

                // 결제 생성
                $payment = Payment::create([
                    'user_id' => $schedule->user_id,
                    'student_id' => $studentId,
                    'order_id' => 'ps-' . $studentId . date('YmdHis'),
                    'amount' => $schedule->amount,
                    'billing_name' => $schedule->billing_name,
                    'billing_memo' => $schedule->billing_memo,
                    'payment_status' => 'pending',
                ]);

                // FCM 알림 발송
                $parentPhones = array_unique(array_filter([
                    $student->phone_father,
                    $student->phone_mother,
                ]));

                $title = "💳 {$student->user->name} 학생 결제 알림";
                $body = "{$student->user->name} 학생의 새로운 결제가 생성되었습니다. 결제명: {$schedule->billing_name} / 금액: " . number_format($schedule->amount) . "원";

                foreach ($parentPhones as $phone) {
                    $fcmService->sendToParent($phone, $title, $body, [
                        'type' => 'payment',
                        'title' => $title,
                        'body' => $body,
                        'payment_id' => $payment->id,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("예약 결제 생성 실패: 학생 {$studentId}", ['error' => $e->getMessage()]);
                $this->error("학생 {$studentId} 실패: {$e->getMessage()}");
            }
        }

        $schedule->update([
            'last_sent_at' => now(),
            'next_send_at' => now()->addMonth()->day($schedule->send_day)->startOfDay(),
        ]);
    }
}
