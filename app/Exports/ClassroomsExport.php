<?php

namespace App\Exports;

use App\Models\Classroom;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClassroomsExport implements FromCollection, WithHeadings, WithMapping
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
        $query = Classroom::query()
            ->with(['teacher.user'])
            ->withCount('students');

        if ($this->startDate) {
            $query->whereDate('started_at', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('started_at', '<=', $this->endDate);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return ['반이름', '담임', '학생수', '개설일'];
    }

    public function map($classroom): array
    {
        return [
            $classroom->name,
            $classroom->teacher?->user?->name ?? '-',
            $classroom->students_count,
            $classroom->started_at ? \Carbon\Carbon::parse($classroom->started_at)->format('Y-m-d') : '-',
        ];
    }
}
