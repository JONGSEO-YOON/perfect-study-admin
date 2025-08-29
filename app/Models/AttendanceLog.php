<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    protected $fillable = [
        'student_id',
        'classroom_id',
        'type',
        'is_late',
        'memo',
    ];

    protected $casts = [
        'is_late' => 'boolean',
    ];

    /**
     * 학생과의 관계
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * 교실과의 관계
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * 등원인지 확인
     */
    public function isCheckIn(): bool
    {
        return $this->type === 'in';
    }

    /**
     * 하원인지 확인
     */
    public function isCheckOut(): bool
    {
        return $this->type === 'out';
    }

    /**
     * created_at을 한국어 형식으로 변환
     */
    public function getFormattedTimeAttribute(): string
    {
        return $this->created_at->format('H:i');
    }

    /**
     * created_at을 한국어 오전/오후 형식으로 변환
     */
    public function getKoreanTimeAttribute(): string
    {
        $hour = $this->created_at->hour;
        $minute = $this->created_at->format('i');

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
     * 특정 학생의 오늘 출석 데이터를 포맷된 형태로 반환
     */
    public static function getTodayAttendanceForStudent($studentId)
    {
        $attendances = [];

        $attendanceLogs = static::where('student_id', $studentId)
            ->whereDate('created_at', now())
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($attendanceLogs as $attendanceLog) {
            $title = $attendanceLog->type === 'in' ? '등원' : '하원';
            $color = $attendanceLog->type === 'in' ? '#06b6d4' : '#22d3ee';
            
            // 정규/보충 구분
            if ($attendanceLog->classroom_id) {
                $title = '(정규)' . $title;
                $color = $attendanceLog->type === 'in' ? '#8b5cf6' : '#a78bfa';
            } else {
                $title = '(보충)' . $title;
            }
            
            if ($attendanceLog->is_late) {
                $title .= ' (지각)';
                $color = '#f59e0b';
            }

            $attendances[] = [
                'title' => $title,
                'time' => $attendanceLog->korean_time,
                'color' => $color,
            ];
        }

        return $attendances;
    }

    /**
     * 특정 학생의 날짜 범위별 출석 데이터를 포맷된 형태로 반환
     */
    public static function getAttendanceForStudentByDateRange($studentId, $startDate, $endDate)
    {
        $attendances = [];

        $attendanceLogs = static::where('student_id', $studentId)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($attendanceLogs as $attendanceLog) {
            $title = $attendanceLog->type === 'in' ? '등원' : '하원';
            $color = $attendanceLog->type === 'in' ? '#06b6d4' : '#22d3ee';
            
            // 정규/보충 구분
            if ($attendanceLog->classroom_id) {
                $title = '(정규)' . $title;
                $color = $attendanceLog->type === 'in' ? '#8b5cf6' : '#a78bfa';
            } else {
                $title = '(보충)' . $title;
            }
            
            if ($attendanceLog->is_late) {
                $title .= ' (지각)';
                $color = '#f59e0b';
            }

            $attendances[] = [
                'date' => $attendanceLog->created_at->format('Y-m-d'),
                'title' => $title,
                'time' => $attendanceLog->korean_time,
                'color' => $color,
            ];
        }

        return $attendances;
    }

    /**
     * 특정 학생의 날짜 범위별 출석 데이터를 날짜별로 그룹화하여 반환
     */
    public static function getAttendancesByDateRange($studentId, $startDate, $endDate)
    {
        $attendances = [];

        $attendanceLogs = static::where('student_id', $studentId)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($attendanceLogs as $attendanceLog) {
            $dateKey = $attendanceLog->created_at->format('Y-m-d');
            $date = $attendanceLog->created_at->format('n월 j일');
            $dayOfWeek = $attendanceLog->created_at->locale('ko')->dayName;

            if (!isset($attendances[$dateKey])) {
                $attendances[$dateKey] = [
                    'date' => $date,
                    'day_of_week' => $dayOfWeek,
                    'records' => []
                ];
            }

            $title = $attendanceLog->type === 'in' ? '등원' : '하원';
            $color = $attendanceLog->type === 'in' ? '#06b6d4' : '#22d3ee';
            
            // 정규/보충 구분
            if ($attendanceLog->classroom_id) {
                $title = '(정규)' . $title;
                $color = $attendanceLog->type === 'in' ? '#8b5cf6' : '#a78bfa';
            } else {
                $title = '(보충)' . $title;
            }
            
            if ($attendanceLog->is_late) {
                $title .= ' (지각)';
                $color = '#f59e0b';
            }

            $attendances[$dateKey]['records'][] = [
                'title' => $title,
                'time' => $attendanceLog->korean_time,
                'color' => $color,
            ];
        }

        // 날짜 역순으로 정렬
        krsort($attendances);
        return $attendances;
    }

    /**
     * 주차별 정규 출결 데이터 조회 (성적표용)
     */
    public static function getRegularAttendanceByWeek($studentId, $classroomId, $year, $week)
    {
        $startOfWeek = now()->setISODate($year, $week)->startOfWeek();
        $endOfWeek = now()->setISODate($year, $week)->endOfWeek();

        return static::where('student_id', $studentId)
            ->where('classroom_id', $classroomId)
            ->whereBetween('created_at', [
                $startOfWeek->format('Y-m-d H:i:s'),
                $endOfWeek->format('Y-m-d H:i:s')
            ])
            ->orderBy('created_at', 'asc')
            ->get();
    }
}
