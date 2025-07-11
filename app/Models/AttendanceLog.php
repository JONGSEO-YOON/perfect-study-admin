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
}
