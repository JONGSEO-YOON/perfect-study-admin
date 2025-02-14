<?php

namespace App\Models;

use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

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

    public function material()
    {
        return $this->belongsTo(Material::class);
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

        static::addGlobalScope('material_visibility', function (Builder $builder) {
            if (!auth()->check()) {
                return;
            }

            if (!auth()->user()->userable instanceof \App\Models\Teacher) {
                return;
            }

            // $builder->where(function ($query) {
            //     $query->whereNull('material_id')
            //         ->orWhereHas('material', function ($query) {
            //             $query->visible();
            //         });
            // });
            $builder->where(function ($query) {
                // material이 있는 경우
                $query->where(function ($q) {
                    $q->whereNotNull('material_id')
                        ->whereHas('material', function ($q) {
                            $q->visible();
                        });
                })
                    // material이 없는 경우
                    ->orWhere(function ($q) {
                        $q->whereNull('material_id')
                            ->where(function ($q) {
                                $q->where('user_id', auth()->id())
                                    ->orWhere('is_public', true)
                                    ->when(auth()->user()->isRoleAbove('manager', true), function ($q) {
                                        $q->orWhereRaw('1 = 1');
                                    });
                            });
                    })
                    // 부모가 있는 경우는 검증하지 않음
                    ->orWhere(function ($q) {
                        $q->orWhereExists(function ($query) {
                            $query->from('questions as parent_q')
                                ->whereColumn('parent_q.id', 'questions.parent_question_id');
                        });
                    });
            });
        });
    }

    /**
     * 현재 사용자가 이 문제를 편집할 수 있는지 확인
     */
    public function getIsEditableAttribute(): bool
    {
        return auth()->user()->isRoleAbove('manager') ||
            $this->user_id === auth()->id();
    }
}
