<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Lecture extends Model
{
    use HasFactory;

    protected $casts = [
        'display' => 'boolean',
        'published_at' => 'datetime',
        'expired_at' => 'datetime',
        'target_grades' => 'array',
        'target_levels' => 'array',
        'target_classrooms' => 'array',
        'target_students' => 'array',
        'lecture_info' => 'array',
        'attachments' => 'array',
        'links' => 'array',
        'scopes' => 'array',
    ];

    // 강의를 등록한 사용자와의 관계
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->user_id && auth()->check()) {
                $model->user_id = auth()->id();
            }
        });
    }

    /**
     * 학생에게 해당되는 강의만 조회하는 스코프
     */
    public function scopeAvailableFor(Builder $query, Student $student): Builder
    {
        $teacherIds = $student->classrooms()
            ->with('teacher.user')
            ->get()
            ->pluck('teacher.user.id')
            ->unique()
            ->values()
            ->all();

        return $query->where(function ($query) use ($student, $teacherIds) {
            // 출제자가 학생의 강사인 경우만
            $query->whereIn('user_id', $teacherIds);

            // target_group별 조건 체크
            $query->where(function ($q) use ($student) {
                // 학년 대상
                $q->where(function ($subQ) use ($student) {
                    $targetGrades = $student->classrooms()
                        ->get()
                        ->pluck('target_grades')
                        ->flatten()
                        ->unique()
                        ->values();

                    if ($targetGrades->isNotEmpty()) {
                        $subQ->where('target_group', 'grade')
                            ->where(function ($jsonQ) use ($targetGrades) {
                                foreach ($targetGrades as $gradeId) {
                                    $jsonQ->orWhereJsonContains('target_grades', $gradeId)
                                        ->orWhereJsonContains('target_grades', (string)$gradeId);
                                }
                            });
                    }
                })
                    // 반 대상
                    ->orWhere(function ($subQ) use ($student) {
                        $classroomIds = $student->classrooms->pluck('id');
                        $subQ->where('target_group', 'classroom')
                            ->where(function ($jsonQ) use ($classroomIds) {
                                foreach ($classroomIds as $id) {
                                    $jsonQ->orWhereJsonContains('target_classrooms', $id)
                                        ->orWhereJsonContains('target_classrooms', (string)$id);
                                }
                            });
                    })
                    // 학생 대상
                    ->orWhere(function ($subQ) use ($student) {
                        $subQ->where('target_group', 'student')
                            ->where(function ($jsonQ) use ($student) {
                                $jsonQ->whereJsonContains('target_students', $student->user->id)
                                    ->orWhereJsonContains('target_students', (string)$student->user->id);
                            });
                    });

                // 레벨 대상
                if ($student->classrooms->isNotEmpty()) {
                    $q->orWhere(function ($subQ) use ($student) {
                        $classrooms = $student->classrooms;
                        $classroomLevels = $classrooms->pluck('target_level')->filter();
                        $classroomGrades = $classrooms->pluck('target_grades')
                            ->flatten()
                            ->filter()
                            ->unique()
                            ->values();

                        if ($classroomLevels->isNotEmpty() && $classroomGrades->isNotEmpty()) {
                            $subQ->where('target_group', 'level')
                                ->where(function ($jsonQ) use ($classroomLevels) {
                                    foreach ($classroomLevels as $level) {
                                        $jsonQ->orWhereJsonContains('target_levels', $level);
                                    }
                                })
                                ->where(function ($jsonQ) use ($classroomGrades) {
                                    foreach ($classroomGrades as $gradeId) {
                                        $jsonQ->orWhereJsonContains('target_grades', $gradeId)
                                            ->orWhereJsonContains('target_grades', (string)$gradeId);
                                    }
                                });
                        }
                    });
                }
            });
        });
    }

    /**
     * 공개 상태이고 공개 기간 내인 강의만 조회하는 스코프
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('display', true)
            ->where(function ($query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expired_at')
                    ->orWhere('expired_at', '>=', now());
            });
    }

    public function videoViewHistories()
    {
        return $this->hasMany(LectureVideoViewHistory::class);
    }
}
