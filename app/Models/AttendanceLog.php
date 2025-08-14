<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    protected $fillable = [
        'student_id',
        'attendance_date',
        'check_in_time',
        'check_out_time',
    ];

    // protected $casts = [
    //     'attendance_date' => 'date',
    //     'check_in_time' => 'datetime',
    //     'check_out_time' => 'datetime',
    // ];

    /**
     * 학생과의 관계
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * 출석했는지 확인
     */
    public function isPresent(): bool
    {
        return !is_null($this->check_in_time);
    }

    /**
     * 하원했는지 확인
     */
    public function isCheckedOut(): bool
    {
        return !is_null($this->check_out_time);
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
     * 특정 학생의 오늘 출석 데이터를 포맷된 형태로 반환
     */
    public static function getTodayAttendanceForStudent($studentId)
    {
        $attendances = [];

        $attendanceLogs = static::where('student_id', $studentId)
            ->whereDate('attendance_date', now())
            ->orderBy('attendance_date', 'asc')
            ->get();

        foreach ($attendanceLogs as $attendanceLog) {
            if ($attendanceLog->check_in_time) {
                $attendances[] = [
                    'title' => '(보충)등원',
                    'time' => static::formatTimeToKorean($attendanceLog->check_in_time),
                    'color' => '#8b5cf6',
                ];
            }
            if ($attendanceLog->check_out_time) {
                $attendances[] = [
                    'title' => '(보충)하원',
                    'time' => static::formatTimeToKorean($attendanceLog->check_out_time),
                    'color' => '#a78bfa',
                ];
            }
        }

        return $attendances;
    }
}
