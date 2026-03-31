<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PaymentsExport implements FromCollection, WithHeadings, WithMapping
{
    protected ?string $startDate;
    protected ?string $endDate;

    public function __construct(?string $startDate = null, ?string $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $query = Payment::query()
            ->with(['user', 'student.user']);

        if (auth()->user()->role === 'general') {
            $teacher = auth()->user()->userable;
            $classroomIds = $teacher->classrooms->pluck('id');
            $query->whereHas('student.classrooms', function ($q) use ($classroomIds) {
                $q->whereIn('classroom_id', $classroomIds);
            });
        }

        if ($this->startDate) {
            $query->whereDate('payments.created_at', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('payments.created_at', '<=', $this->endDate);
        }

        return $query->orderBy('payments.created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return ['ID', '생성자', '학생', '금액', '결제상태', '청구이름', '결제방법', '결제일시', '취소일시', '생성일'];
    }

    public function map($payment): array
    {
        $statusMap = [
            'pending' => '대기중',
            'paid' => '결제완료',
            'cancelled' => '취소',
            'completed' => '완료',
            'failed' => '실패',
        ];

        return [
            $payment->id,
            $payment->user?->name ?? '-',
            $payment->student?->user?->name ?? '-',
            $payment->amount,
            $statusMap[$payment->payment_status] ?? $payment->payment_status,
            $payment->billing_name ?? '-',
            $payment->payment_method ?? '-',
            $payment->approved_at?->format('Y-m-d H:i:s') ?? '-',
            $payment->cancelled_at?->format('Y-m-d H:i:s') ?? '-',
            $payment->created_at?->format('Y-m-d H:i:s') ?? '-',
        ];
    }
}
