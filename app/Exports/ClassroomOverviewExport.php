<?php

namespace App\Exports;

use App\Models\Classroom;
use App\Models\GradeSystem;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClassroomOverviewExport implements FromArray, WithStyles, WithEvents
{
    protected array $rows = [];
    protected int $headerRow = 1;

    public function array(): array
    {
        $dayLabels = [
            'mon' => '월', 'tue' => '화', 'wed' => '수',
            'thu' => '목', 'fri' => '금', 'sat' => '토', 'sun' => '일',
        ];

        $gradeMap = GradeSystem::orderBy('sequential_order')->pluck('display_name', 'id')->toArray();

        $this->rows[] = ['반 이름', '학년', '레벨', '담임', '부담임', '시간표', '학생 수', '학생 명단', '비고'];

        $classrooms = Classroom::with(['teacher.user', 'subTeacher.user', 'students.user'])
            ->orderBy('name')
            ->get();

        foreach ($classrooms as $classroom) {
            $grades = collect($classroom->target_grades ?? [])
                ->map(fn($id) => $gradeMap[$id] ?? $id)
                ->join(', ');

            $timetableLines = [];
            if ($classroom->timetable) {
                $dayOrder = array_keys($dayLabels);
                $sorted = collect($classroom->timetable)
                    ->sortBy(fn($v, $k) => array_search($k, $dayOrder));
                foreach ($sorted as $day => $times) {
                    $dayLabel = $dayLabels[$day] ?? $day;
                    $timetableLines[] = "{$dayLabel} " . ($times['start'] ?? '') . '~' . ($times['end'] ?? '');
                }
            }

            $students = $classroom->students
                ->filter(fn($s) => $s->status === 'enrolled')
                ->sortBy(fn($s) => $s->user->name ?? '')
                ->values();

            $studentList = $students->map(fn($s) => $s->user?->name ?? '')->filter()->join(', ');

            $this->rows[] = [
                $classroom->name,
                $grades,
                $classroom->target_level,
                $classroom->teacher?->user?->name ?? '-',
                $classroom->subTeacher?->user?->name ?? '-',
                implode("\n", $timetableLines),
                $students->count() . '명',
                $studentList,
                $classroom->remark ?? '',
            ];
        }

        return $this->rows;
    }

    public function styles(Worksheet $sheet)
    {
        // 헤더 스타일
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF6366F1']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $rowCount = count($this->rows);
                $highestColumn = $sheet->getHighestColumn();

                // 모든 셀에 테두리 + 자동 줄바꿈
                $range = "A1:{$highestColumn}{$rowCount}";
                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFCBD5E1'],
                        ],
                    ],
                    'alignment' => [
                        'wrapText' => true,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // 컬럼 너비 자동 + 일부 수동
                foreach (range('A', $highestColumn) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
                $sheet->getColumnDimension('F')->setAutoSize(false);
                $sheet->getColumnDimension('F')->setWidth(20);
                $sheet->getColumnDimension('H')->setAutoSize(false);
                $sheet->getColumnDimension('H')->setWidth(60);
                $sheet->getColumnDimension('I')->setAutoSize(false);
                $sheet->getColumnDimension('I')->setWidth(30);
            },
        ];
    }
}
