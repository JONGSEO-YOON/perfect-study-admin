<?php

namespace App\Models;

use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Question extends Model
{
    use HasFactory, HasUser;

    public function academy()
    {
        return $this->belongsTo(Academy::class);
    }

    protected static function booted(): void
    {
        // 생성 시 자동으로 academy_id 설정
        // 우선순위: 1) 명시적으로 set된 값, 2) 현재 접속 학원, 3) 인증된 사용자의 academy_id
        static::creating(function ($model) {
            if (!$model->academy_id) {
                if (app()->has('current_academy') && app('current_academy')) {
                    $model->academy_id = app('current_academy')->id;
                } elseif (auth()->check()) {
                    $model->academy_id = auth()->user()->academy_id;
                }
            }
        });
    }

    protected $with = ['questionType', 'choices'];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'metadata' => 'array',
            'exam_year' => 'integer',
            'exam_month' => 'integer',
            'exam_score' => 'integer',
            'exam_question_number' => 'integer',
            'exam_semester' => 'integer',
        ];
    }

    /**
     * 수정 권한: root_admin은 모든 문제, 나머지는 자기가 만든 문제 또는 자기 학원 교재 문제만
     */
    public function getIsEditableAttribute(): bool
    {
        if (!auth()->check()) return false;

        // root_admin: 모든 문제 수정 가능
        if (auth()->user()->role === 'root_admin') return true;

        // 자기가 만든 문제
        if ($this->user_id === auth()->id()) return true;

        // 자기 학원의 교재에 속한 문제 (manager 이상)
        if ($this->material_id && auth()->user()->isRoleAbove('manager', true)) {
            return $this->academy_id === auth()->user()->academy_id;
        }

        return false;
    }

    public function questionType()
    {
        return $this->belongsTo(QuestionCategory::class, 'question_type_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
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
            // GlobalScope를 우회하여 같은 교재의 모든 문제 중 max(seq) 조회
            if ($model->material_id && !$model->seq) {
                $maxSeq = static::withoutGlobalScopes()
                    ->where('material_id', $model->material_id)
                    ->max('seq') ?? 0;
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

}
