<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Services\FcmService;
use App\Models\Student;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['order_id'] = 'ps-' . $data['student_id'] . date('YmdHis');

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->sendPaymentNotificationToParent();
    }

    protected function sendPaymentNotificationToParent(): void
    {
        try {
            $payment = $this->record;
            $student = Student::with('user')->find($payment->student_id);

            if (!$student || !$student->user) {
                Log::info("결제 알림: 학생 정보를 찾을 수 없습니다. (결제 ID: {$payment->id})");
                return;
            }

            $fcmService = app(FcmService::class);

            // 부모 연락처 수집
            $parentPhones = [];
            if ($student->phone_father) {
                $parentPhones[] = $student->phone_father;
            }
            if ($student->phone_mother) {
                $parentPhones[] = $student->phone_mother;
            }

            $uniqueParentPhones = array_unique($parentPhones);

            if (empty($uniqueParentPhones)) {
                Log::info("결제 알림: 학생 {$student->user->name}의 부모 연락처가 없습니다.");
                return;
            }

            // 알림 내용 구성
            $title = "💳 {$student->user->name} 학생 결제 알림";
            $body = "{$student->user->name} 학생의 새로운 결제가 생성되었습니다. 결제명: {$payment->billing_name} / 금액: " . number_format($payment->amount) . "원";

            foreach ($uniqueParentPhones as $parentPhone) {
                $fcmService->sendToParent(
                    $parentPhone,
                    $title,
                    $body,
                    [
                        'type' => 'payment',
                        'title' => $title,
                        'body' => $body,
                        'payment_id' => $payment->id,
                    ]
                );
            }
        } catch (\Exception $e) {
            // 알림 전송 실패해도 결제 생성은 계속 진행
            Log::error("결제 알림 전송 중 오류: " . $e->getMessage(), [
                'payment_id' => $this->record->id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }
}
