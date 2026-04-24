<?php

namespace App\Exports;

use App\Models\Student;
use App\Models\AttendanceLog;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class MonthlyAttendanceExport implements FromCollection, WithHeadings, WithTitle
{
    protected int $year;
    protected int $month;
    protected ?int $classroomId;

    public function __construct(int $year, int $month, ?int $classroomId = null)
    {
        $this->year = $year;
        $this->month = $month;
        $this->classroomId = $classroomId;
    }

    public function title(): string
    {
        return "{$this->year}년 {$this->month}월 출결현황";
    }

    public function headings(): array
    {
        $daysInMonth = Carbon::create($this->year, $this->month, 1)->daysInMonth;

        $headings = ['이름'];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($this->year, $this->month, $day);
            $dayNames = ['일', '월', '화', '수', '목', '금', '토'];
            $headings[] = $day . '(' . $dayNames[$date->dayOfWeek] . ')';
        }
        $headings[] = '출석';
        $headings[] = '결석';
        $headings[] = '지각';

        return $headings;
    }

    public function collection()
    {
        $query = Student::with('user')->where('status', '!=', 'withdrawn');

        if ($this->classroomId) {
            $query->whereHas('classrooms', fn($q) => $q->where('classrooms.id', $this->classroomId));
        } elseif (!auth()->user()->isRoleAbove('manager', true)) {
            $query->whereHas('classrooms', fn($q) =>
                $q->where('classrooms.teacher_id', auth()->user()->userable->id)
            );
        }

        $students = $query->orderBy('id')->get();

        $startDate = Carbon::create($this->year, $this->month, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->endOfDay();
        $daysInMonth = $startDate->daysInMonth;

        $logs = AttendanceLog::whereIn('student_id', $students->pluck('id'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->groupBy('student_id');

        $rows = collect();

        foreach ($students as $student) {
            $studentLogs = $logs->get($student->id, collect())->groupBy(fn($log) => $log->created_at->day);

            $row = [$student->user?->name ?? '-'];
            $attendCount = 0;
            $absentCount = 0;
            $lateCount = 0;

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $dayLogs = $studentLogs->get($day);
                if (!$dayLogs || $dayLogs->isEmpty()) {
                    $row[] = '';
                } else {
                    $log = $dayLogs->first();
                    if ($log->is_absent) {
                        $row[] = '결석';
                        $absentCount++;
                    } elseif ($log->is_late) {
                        $row[] = '지각';
                        $lateCount++;
                        $attendCount++;
                    } else {
                        $checkIn = $log->check_in_time ? $log->check_in_time->format('H:i') : '';
                        $row[] = $checkIn ?: '출석';
                        $attendCount++;
                    }
                }
            }

            $row[] = $attendCount;
            $row[] = $absentCount;
            $row[] = $lateCount;

            $rows->push($row);
        }

        return $rows;
    }
}
