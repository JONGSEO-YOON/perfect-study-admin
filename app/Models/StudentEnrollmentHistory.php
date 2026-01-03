<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentEnrollmentHistory extends Model
{
    use HasFactory;

    protected $table = 'student_enrollment_history';

    protected $fillable = [
        'student_id',
        'action',
        'reason',
        'reason_detail',
        'homeroom_teacher_id',
        'action_date',
        'memo',
        'processed_by',
    ];

    protected function casts(): array
    {
        return [
            'action_date' => 'date',
        ];
    }

    const ACTION_ENROLL = 'enroll';
    const ACTION_WITHDRAW = 'withdraw';
    const ACTION_RE_ENROLL = 're_enroll';

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'homeroom_teacher_id');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            self::ACTION_ENROLL => '입원',
            self::ACTION_WITHDRAW => '퇴원',
            self::ACTION_RE_ENROLL => '재입원',
            default => $this->action,
        };
    }

    public function getReasonLabelAttribute(): ?string
    {
        if (!$this->reason) {
            return null;
        }
        return Student::WITHDRAWAL_REASONS[$this->reason] ?? $this->reason;
    }
}
