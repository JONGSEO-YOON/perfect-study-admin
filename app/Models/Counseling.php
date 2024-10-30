<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Counseling extends Model
{
    use HasFactory;


    protected $casts = [
        'counseled_at' => 'datetime',
        'planned_start_at' => 'date',
        'planned_end_at' => 'date',
        'confirmed' => 'boolean',
    ];

    // 상담자 (교사)
    public function counselor()
    {
        return $this->belongsTo(Teacher::class, 'counselor_id');
    }

    // 상담 신청 학생
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // 상담 생성자
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->requester_id && auth()->check()) {
                $model->requester_id = auth()->id();
            }
        });
    }
}
