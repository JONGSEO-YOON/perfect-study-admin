<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSharingRule extends Model
{
    protected $guarded = [];

    public function academy()
    {
        return $this->belongsTo(Academy::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
