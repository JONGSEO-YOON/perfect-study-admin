<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class WeeklyTestReport extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'report' => 'array',
        ];
    }

    public static function getFormattedWeeklyReport(
        Student $student,
        string $dateFrom,
        string $dateUntil,
        int $classroomId
    ): Collection {
        if (!$student || !$classroomId || !$dateFrom || !$dateUntil) {
            return collect();
        }

        // 날짜 문자열을 Carbon 인스턴스로 변환
        $startDate = explode('/', $dateFrom)[0];
        $endDate = explode('/', $dateUntil)[1];
        $startCarbon = Carbon::parse($startDate);
        $endCarbon = Carbon::parse($endDate);

        // 시작 주와 끝 주 계산
        $startYear = $startCarbon->year;
        $startWeek = $startCarbon->isoWeek();
        $endYear = $endCarbon->year;
        $endWeek = $endCarbon->isoWeek();

        // 모든 보고서 조회
        $reports = static::where('student_id', $student->id)
            ->where('classroom_id', $classroomId)
            ->where(function ($query) use ($startYear, $startWeek, $endYear, $endWeek) {
                if ($startYear === $endYear) {
                    $query->where('year', $startYear)
                        ->whereBetween('week', [$startWeek, $endWeek]);
                } else {
                    $query->where(function ($q) use ($startYear, $startWeek, $endYear, $endWeek) {
                        $q->where(function ($q1) use ($startYear, $startWeek) {
                            $q1->where('year', $startYear)
                                ->where('week', '>=', $startWeek);
                        })->orWhere(function ($q2) use ($endYear, $endWeek) {
                            $q2->where('year', $endYear)
                                ->where('week', '<=', $endWeek);
                        });
                    });
                }
            })
            ->get()
            ->groupBy(function ($report) {
                return $report->year . '-' . $report->week;
            });

        // 모든 주차 생성
        $allWeeks = collect();
        $currentDate = $startCarbon->copy();

        while ($currentDate <= $endCarbon) {
            $year = $currentDate->year;
            $week = $currentDate->isoWeek();
            $key = $year . '-' . $week;

            if (!$allWeeks->has($key)) {
                $weekDate = Carbon::now()->setISODate($year, $week, 1);
                $weekReports = $reports->get($key, collect());

                $allWeeks[$key] = [
                    'year' => $year,
                    'week' => $week,
                    'week_label' => sprintf(
                        '%d년 %d월 %d주차',
                        $weekDate->format('y'),
                        $weekDate->format('n'),
                        floor(($weekDate->format('d') - 1) / 7) + 1
                    ),
                    'week_range' => static::getWeekRange($year, $week),
                    'test_report' => static::formatReport($weekReports->firstWhere('type', 'test')),
                    'homework_report' => static::formatReport($weekReports->firstWhere('type', 'homework')),
                    'attendance_report' => static::formatReport($weekReports->firstWhere('type', 'attendance')),
                    'comment_report' => $weekReports->firstWhere('type', 'comment')?->report['comment'] ?? ''
                ];
            }

            $currentDate->addWeek();
        }

        return $allWeeks->sortBy(['year', 'week'])->values();
    }

    protected static function getWeekRange($year, $week): string
    {
        $date = Carbon::now();
        $date->setISODate($year, $week);
        $startOfWeek = $date->startOfWeek()->format('Y-m-d');
        $endOfWeek = $date->endOfWeek()->format('Y-m-d');
        return "$startOfWeek ~ $endOfWeek";
    }

    protected static function formatReport($report): ?array
    {
        if (!$report) {
            return null;
        }

        if ($report->type === 'attendance') {
            return collect($report->report)
                ->sortBy('date')
                ->values()
                ->all();
        }

        return collect($report->report)
            ->sortBy('test_sheet_id')
            ->map(function ($test) {
                return [
                    'date' => $test['date'],
                    'test_sheet_id' => $test['test_sheet_id'] ?? 0,
                    'name' => $test[isset($test['test_name']) ? 'test_name' : 'homework_name'],
                    'scopes' => $test['scopes'],
                    'total' => $test['total'],
                    'by_types' => collect($test['by_types'])->sortBy('name')->values()->all()
                ];
            })
            ->values()
            ->all();
    }

    /**
     * 24시간 형식의 시간을 오전/오후 형식으로 변환
     */
    public static function formatTimeToKorean($time)
    {
        if (empty($time)) {
            return '';
        }

        // 시간 부분만 추출 (초 제거)
        $timeParts = explode(':', $time);
        $hour = (int) $timeParts[0];
        $minute = $timeParts[1] ?? '00';

        if ($hour < 12) {
            $period = '오전';
            $displayHour = $hour === 0 ? 12 : $hour;
        } else {
            $period = '오후';
            $displayHour = $hour === 12 ? 12 : $hour - 12;
        }

        return $period . ' ' . $displayHour . ':' . $minute;
    }

    /**
     * 특정 학생의 오늘 출석 리포트 데이터를 포맷된 형태로 반환
     */
    public static function getTodayAttendanceReportForStudent($studentId)
    {
        $attendances = [];

        $weeklyTestReports = static::where('student_id', $studentId)
            ->where('type', 'attendance')
            ->whereDate('created_at', now())
            ->get();

        foreach ($weeklyTestReports as $weeklyTestReport) {
            $reportData = $weeklyTestReport->report;
            foreach ($reportData as $report) {
                if (isset($report['check_in_time']) && $report['check_in_time'] !== null) {
                    $attendances[] = [
                        'title' => '(정규)등원',
                        'time' => static::formatTimeToKorean($report['check_in_time']),
                        'color' => '#06b6d4',
                    ];
                }
                if (isset($report['check_out_time']) && $report['check_out_time'] !== null) {
                    $attendances[] = [
                        'title' => '(정규)하원',
                        'time' => static::formatTimeToKorean($report['check_out_time']),
                        'color' => '#22d3ee',
                    ];
                }
            }
        }

        return $attendances;
    }
}
