<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSharingRule extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_allowed' => 'boolean',
        'exam_year' => 'integer',
        'exam_month' => 'integer',
    ];

    public function academy()
    {
        return $this->belongsTo(Academy::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
