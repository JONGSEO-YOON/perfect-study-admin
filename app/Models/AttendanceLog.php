<?php

namespace App\Models;

use App\Models\Traits\BelongsToAcademy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    use BelongsToAcademy;

    protected $fillable = [
        'student_id',
        'classroom_id',
        'is_late',
        'is_absent',
        'memo',
        'is_supplementary',
        'check_in_time',
        'check_out_time',
        'academy_id',
    ];

    protected $casts = [
        'is_late' => 'boolean',
        'is_absent' => 'boolean',
        'is_supplementary' => 'boolean',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
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
        return !is_null($this->check_in_time);
    }

    /**
     * 하원인지 확인
     */
    public function isCheckOut(): bool
    {
        return !is_null($this->check_out_time);
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
            // 결석인 경우 결석 기록 추가
            if ($attendanceLog->is_absent) {
                $attendances[] = [
                    'title' => '결석',
                    'time' => '',
                    'color' => '#ef4444',
                ];
                continue;
            }

            // 등원/하원 시간에 따라 이벤트 생성
            if ($attendanceLog->check_in_time) {
                $title = '등원';
                $color = '#06b6d4';

                // 정규/보충 구분
                if ($attendanceLog->is_supplementary) {
                    $title = '(보충)' . $title;
                } else {
                    $title = '(정규)' . $title;
                    $color = '#8b5cf6';
                }

                if ($attendanceLog->is_late) {
                    $title .= ' (지각)';
                    $color = '#f59e0b';
                }

                $attendances[] = [
                    'title' => $title,
                    'time' => $attendanceLog->check_in_time->format('H:i'),
                    'color' => $color,
                ];
            }

            if ($attendanceLog->check_out_time) {
                $title = '하원';
                $color = '#22d3ee';

                // 정규/보충 구분
                if ($attendanceLog->is_supplementary) {
                    $title = '(보충)' . $title;
                } else {
                    $title = '(정규)' . $title;
                    $color = '#a78bfa';
                }

                $attendances[] = [
                    'title' => $title,
                    'time' => $attendanceLog->check_out_time->format('H:i'),
                    'color' => $color,
                ];
            }
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
            // 결석인 경우 결석 기록 추가
            if ($attendanceLog->is_absent) {
                $attendances[] = [
                    'date' => $attendanceLog->created_at->format('Y-m-d'),
                    'title' => '결석',
                    'time' => '',
                    'color' => '#ef4444',
                ];
                continue;
            }

            // 등원/하원 시간에 따라 이벤트 생성
            if ($attendanceLog->check_in_time) {
                $title = '등원';
                $color = '#06b6d4';

                // 정규/보충 구분
                if ($attendanceLog->is_supplementary) {
                    $title = '(보충)' . $title;
                } else {
                    $title = '(정규)' . $title;
                    $color = '#8b5cf6';
                }

                if ($attendanceLog->is_late) {
                    $title .= ' (지각)';
                    $color = '#f59e0b';
                }

                $attendances[] = [
                    'date' => $attendanceLog->created_at->format('Y-m-d'),
                    'title' => $title,
                    'time' => $attendanceLog->check_in_time->format('H:i'),
                    'color' => $color,
                ];
            }

            if ($attendanceLog->check_out_time) {
                $title = '하원';
                $color = '#22d3ee';

                // 정규/보충 구분
                if ($attendanceLog->is_supplementary) {
                    $title = '(보충)' . $title;
                } else {
                    $title = '(정규)' . $title;
                    $color = '#a78bfa';
                }

                $attendances[] = [
                    'date' => $attendanceLog->created_at->format('Y-m-d'),
                    'title' => $title,
                    'time' => $attendanceLog->check_out_time->format('H:i'),
                    'color' => $color,
                ];
            }
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

            // 결석인 경우 결석 기록 추가
            if ($attendanceLog->is_absent) {
                $attendances[$dateKey]['records'][] = [
                    'title' => '결석',
                    'time' => '',
                    'color' => '#ef4444',
                    'memo' => $attendanceLog->memo,
                ];
                continue;
            }

            // 등원/하원 시간에 따라 이벤트 생성
            if ($attendanceLog->check_in_time) {
                $title = '등원';
                $color = '#06b6d4';

                // 정규/보충 구분
                if ($attendanceLog->is_supplementary) {
                    $title = '(보충)' . $title;
                } else {
                    $title = '(정규)' . $title;
                    $color = '#8b5cf6';
                }

                if ($attendanceLog->is_late) {
                    $title .= ' (지각)';
                    $color = '#f59e0b';
                }

                $attendances[$dateKey]['records'][] = [
                    'title' => $title,
                    'time' => $attendanceLog->check_in_time->format('H:i'),
                    'color' => $color,
                    'memo' => $attendanceLog->memo,
                ];
            }

            if ($attendanceLog->check_out_time) {
                $title = '하원';
                $color = '#22d3ee';

                // 정규/보충 구분
                if ($attendanceLog->is_supplementary) {
                    $title = '(보충)' . $title;
                } else {
                    $title = '(정규)' . $title;
                    $color = '#a78bfa';
                }

                $attendances[$dateKey]['records'][] = [
                    'title' => $title,
                    'time' => $attendanceLog->check_out_time->format('H:i'),
                    'color' => $color,
                    'memo' => $attendanceLog->memo,
                ];
            }
        }

        // 날짜 역순으로 정렬
        krsort($attendances);
        return $attendances;
    }

    /**
     * 주차별 출결 데이터 조회 (성적표용) - 정규 및 보충 모두 포함
     */
    public static function getRegularAttendanceByWeek($studentId, $year, $week)
    {
        $startOfWeek = now()->setISODate($year, $week)->startOfWeek();
        $endOfWeek = now()->setISODate($year, $week)->endOfWeek();

        return static::where('student_id', $studentId)
            ->whereBetween('created_at', [
                $startOfWeek->format('Y-m-d H:i:s'),
                $endOfWeek->format('Y-m-d H:i:s')
            ])
            ->orderBy('created_at', 'asc')
            ->get();
    }
}
