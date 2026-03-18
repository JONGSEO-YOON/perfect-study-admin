<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSchedule extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'target_ids' => 'array',
            'is_active' => 'boolean',
            'last_sent_at' => 'datetime',
            'next_send_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 대상 학생 ID 목록 반환
     */
    public function getStudentIds(): array
    {
        return match ($this->target_type) {
            'student' => $this->target_ids,
            'classroom' => Student::whereHas('classrooms', fn($q) => $q->whereIn('classroom_id', $this->target_ids))
                ->whereHas('user', fn($q) => $q->where('is_active', true))
                ->pluck('id')->toArray(),
            'grade' => Student::whereIn('grade_system_id', $this->target_ids)
                ->whereHas('user', fn($q) => $q->where('is_active', true))
                ->pluck('id')->toArray(),
            default => [],
        };
    }

    public function getTargetLabel(): string
    {
        return match ($this->target_type) {
            'student' => '개별 학생',
            'classroom' => '반별',
            'grade' => '학년별',
            default => '-',
        };
    }
}
