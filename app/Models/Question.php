<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'tags' => 'array',
        ];
    }

    public function questionType()
    {
        return $this->belongsTo(QuestionCategory::class, 'question_type_id');
    }

    public function choices()
    {
        return $this->hasMany(QuestionChoice::class);
    }
}
