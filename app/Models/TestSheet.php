<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
