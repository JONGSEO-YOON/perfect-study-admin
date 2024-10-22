<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $casts = [
        'sms_targets' => 'array'
    ];

    public function gradeSystem()
    {
        return $this->belongsTo(GradeSystem::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function user()
    {
        return $this->morphOne(User::class, 'userable');
    }
}
