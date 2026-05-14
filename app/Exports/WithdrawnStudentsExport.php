<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WithdrawnStudentsExport implements FromCollection, WithHeadings, WithMapping
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
        $query = Student::query()
            ->with(['user', 'gradeSystem', 'school'])
            ->where('status', 'withdrawn');

        if ($this->startDate) {
            $query->whereDate('withdrawn_at', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('withdrawn_at', '<=', $this->endDate);
        }

        return $query->orderBy('withdrawn_at', 'desc')->get();
    }

    public function headings(): array
    {
        return ['이름', '전화번호', '학년', '학교', '퇴원사유', '퇴원일자', '등록일'];
    }

    public function map($student): array
    {
        $reasonMap = [
            'poor_performance' => '성적부진',
            'change_of_atmosphere' => '분위기전환',
            'teacher_mismatch' => '선생님맞지않음',
            'academy_atmosphere' => '학원분위기안좋음',
            'relocation' => '이사',
            'family_circumstances' => '가정형편',
            'friend_conflict' => '친구와의불화',
            'gave_up_studying' => '공부포기',
            'other' => '기타',
        ];

        $reason = $reasonMap[$student->withdrawal_reason] ?? ($student->withdrawal_reason ?? '-');
        if ($student->withdrawal_reason === 'other' && $student->withdrawal_reason_detail) {
            $reason = "기타: {$student->withdrawal_reason_detail}";
        }

        return [
            $student->user?->name ?? '-',
            $student->user?->phone ?? '-',
            $student->gradeSystem?->display_name ?? '-',
            $student->school?->name ?? '-',
            $reason,
            $student->withdrawn_at?->format('Y-m-d') ?? '-',
            $student->created_at?->format('Y-m-d') ?? '-',
        ];
    }
}
