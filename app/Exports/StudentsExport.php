<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentsExport implements FromCollection, WithHeadings, WithMapping
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
            ->where('status', '!=', 'withdrawn');

        if (!auth()->user()->isRoleAbove('manager', true)) {
            $query->whereHas('classrooms', function ($q) {
                $q->where('classrooms.teacher_id', auth()->user()->userable->id)
                    ->orWhere('classrooms.sub_teacher_id', auth()->user()->userable->id);
            });
        }

        if ($this->startDate) {
            $query->whereDate('students.created_at', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('students.created_at', '<=', $this->endDate);
        }

        return $query->orderBy('students.created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return ['이름', '전화번호', '학년', '학교', '상태', '등록일'];
    }

    public function map($student): array
    {
        $statusMap = [
            'enrolled' => '재원',
            'pending' => '승인대기',
            'withdrawn' => '퇴원',
        ];

        return [
            $student->user?->name ?? '-',
            $student->user?->phone ?? '-',
            $student->gradeSystem?->display_name ?? '-',
            $student->school?->name ?? '-',
            $statusMap[$student->status] ?? $student->status,
            $student->created_at?->format('Y-m-d') ?? '-',
        ];
    }
}
