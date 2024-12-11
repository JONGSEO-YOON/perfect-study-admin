<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TestSheet extends Model
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
        'scopes' => 'array',
        'questions' => 'array',
        'tags' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
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

    public function getTargetGradeNamesAttribute($value)
    {
        $grades = [];
        foreach ($this->target_grades as $grade) {
            $_grade = GradeSystem::find($grade);
            $grades[] = $_grade->display_name;
        }
        return implode(', ', $grades);
    }

    public function userAnswers()
    {
        return $this->hasMany(TestSheetAnswer::class)
            ->where('user_id', auth()->id());
    }

    public function latestUserAnswer()
    {
        return $this->hasOne(TestSheetAnswer::class)
            ->where('user_id', auth()->id())
            ->latest();
    }


    /**
     * 학생에게 해당되는 시험지만 조회하는 스코프
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
                $this->addTargetGroupConditions($q, $student);
            });
        });
    }

    /**
     * 진행중 시험지 조회 스코프
     */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where(function ($query) {
            $query->where('status', 'progress');
        });
    }


    /**
     * 진행중이거나 완료된 시험지 조회 스코프
     * completed 상태의 경우 사용자의 답안이 있는 것만 조회
     */
    public function scopeInProgressOrCompleted(Builder $query): Builder
    {
        return $query->where(function ($query) {
            $query->where('status', 'progress')
                ->orWhere(function ($q) {
                    $q->where('status', 'completed')
                        ->whereHas('userAnswers');
                });
        });
    }

    /**
     * target_group 조건들을 쿼리에 추가
     */
    protected function addTargetGroupConditions($query, Student $student): void
    {
        // 학년 대상
        $query->where(function ($subQ) use ($student) {
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
                    ->where(function ($jsonQ) {
                        $jsonQ->whereJsonContains('target_students', auth()->id())
                            ->orWhereJsonContains('target_students', (string)auth()->id());
                    });
            });

        // 레벨 대상
        if ($student->classrooms->isNotEmpty()) {
            $query->orWhere(function ($subQ) use ($student) {
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
    }
}
