<?php

namespace App\Models;

use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory, HasUser;

    protected $with = ['questionType', 'choices'];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'metadata' => 'array',
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

    public function parentQuestion()
    {
        return $this->belongsTo(Question::class, 'parent_question_id');
    }

    public function childQuestions()
    {
        return $this->hasMany(Question::class, 'parent_question_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // user_id 설정
            if (!$model->user_id && auth()->check()) {
                $model->user_id = auth()->id();
            }

            // material_id가 있고 seq가 설정되지 않은 경우에만 seq 설정
            if ($model->material_id && !$model->seq) {
                $maxSeq = static::where('material_id', $model->material_id)->max('seq') ?? 0;
                $model->seq = $maxSeq + 1;
            }
        });
    }
}
