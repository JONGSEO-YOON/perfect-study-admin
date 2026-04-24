<?php

namespace App\Models;

use App\Models\Traits\BelongsToAcademy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Classroom extends Model
{
    use BelongsToAcademy;
    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'timetable' => 'array',
            'target_grades' => 'array',
        ];
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subTeacher()
    {
        return $this->belongsTo(Teacher::class, 'sub_teacher_id');
    }

    /**
     * Get the students that belong to the classroom.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class)
            ->wherePivotNull('deleted_at')
            ->withTimestamps();
    }

    public function allStudentsIncludingRemoved(): BelongsToMany
    {
        return $this->belongsToMany(Student::class)
            ->withTimestamps()
            ->withPivot('deleted_at');
    }

    protected static function booted()
    {
        static::addGlobalScope('teacher_filter', function (Builder $builder) {
            if (
                auth()->check()
                && auth()->user()->userable instanceof \App\Models\Teacher
                && !auth()->user()->isRoleAbove('manager', true)
            ) {
                $builder->where(function ($q) {
                    $q->where('teacher_id', auth()->user()->userable->id)
                        ->orWhere('sub_teacher_id', auth()->user()->userable->id);
                });
            }
        });
    }
}
