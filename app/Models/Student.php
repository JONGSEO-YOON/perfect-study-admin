<?php

namespace App\Models;

use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Student extends Model
{
    use HasFactory, HasUser;

    const STATUS_ENROLLED = 'enrolled';
    const STATUS_PENDING = 'pending';
    const STATUS_WITHDRAWN = 'withdrawn';

    const WITHDRAWAL_REASONS = [
        'poor_performance' => '성적부진',
        'change_of_atmosphere' => '분위기전환',
        'teacher_mismatch' => '선생님맞지않음',
        'academy_atmosphere' => '학원분위기안좋음',
        'relocation' => '이사',
        'other' => '기타',
    ];

    protected function casts(): array
    {
        return [
            'sms_targets' => 'array',
            'initially_attended_at' => 'date',
            'sms_agree' => 'boolean',
            'withdrawn_at' => 'date',
        ];
    }

    public function scopeEnrolled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ENROLLED);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeWithdrawn(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_WITHDRAWN);
    }

    public function scopeNotWithdrawn(Builder $query): Builder
    {
        return $query->where('status', '!=', self::STATUS_WITHDRAWN);
    }

    public function isEnrolled(): bool
    {
        return $this->status === self::STATUS_ENROLLED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isWithdrawn(): bool
    {
        return $this->status === self::STATUS_WITHDRAWN;
    }

    public function getWithdrawalReasonLabelAttribute(): ?string
    {
        if (!$this->withdrawal_reason) {
            return null;
        }
        return self::WITHDRAWAL_REASONS[$this->withdrawal_reason] ?? $this->withdrawal_reason;
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ENROLLED => '재원',
            self::STATUS_PENDING => '승인예정',
            self::STATUS_WITHDRAWN => '퇴원',
            default => '재원',
        };
    }

    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'homeroom_teacher_id');
    }

    public function enrollmentHistory(): HasMany
    {
        return $this->hasMany(StudentEnrollmentHistory::class)->orderBy('action_date', 'desc');
    }

    public function withdraw(array $data): bool
    {
        $this->status = self::STATUS_WITHDRAWN;
        $this->withdrawn_at = $data['withdrawn_at'] ?? now();
        $this->withdrawal_reason = $data['withdrawal_reason'] ?? null;
        $this->withdrawal_reason_detail = $data['withdrawal_reason_detail'] ?? null;
        $this->homeroom_teacher_id = $data['homeroom_teacher_id'] ?? null;

        if ($this->save()) {
            StudentEnrollmentHistory::create([
                'student_id' => $this->id,
                'action' => 'withdraw',
                'reason' => $this->withdrawal_reason,
                'reason_detail' => $this->withdrawal_reason_detail,
                'homeroom_teacher_id' => $this->homeroom_teacher_id,
                'action_date' => $this->withdrawn_at,
                'processed_by' => auth()->id(),
            ]);
            return true;
        }
        return false;
    }

    public function reEnroll(?string $memo = null): bool
    {
        $previousStatus = $this->status;
        
        $this->status = self::STATUS_ENROLLED;
        $this->withdrawn_at = null;
        $this->withdrawal_reason = null;
        $this->withdrawal_reason_detail = null;

        if ($this->save()) {
            StudentEnrollmentHistory::create([
                'student_id' => $this->id,
                'action' => 're_enroll',
                'action_date' => now(),
                'memo' => $memo,
                'processed_by' => auth()->id(),
            ]);
            return true;
        }
        return false;
    }

    public function gradeSystem()
    {
        return $this->belongsTo(GradeSystem::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the classrooms that the student belongs to.
     */
    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class)
            ->withTimestamps();
    }

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function latestCheckIn()
    {
        return $this->hasOne(AttendanceLog::class)
            ->whereNotNull('check_in_time')
            ->whereDate('created_at', now()->format('Y-m-d'))
            ->latest();
    }

    public function latestCheckOut()
    {
        return $this->hasOne(AttendanceLog::class)
            ->whereNotNull('check_out_time')
            ->whereDate('created_at', now()->format('Y-m-d'))
            ->latest();
    }

    public function attendanceLogsForDate($date)
    {
        return $this->hasMany(AttendanceLog::class)
            ->whereDate('created_at', $date);
    }

    public function canEdit($user)
    {
        if (
            auth()->user()->isRoleAbove('admin', true)
            || !auth()->user()->userable instanceof \App\Models\Teacher
        ) {
            return true;
        }
    }
}
