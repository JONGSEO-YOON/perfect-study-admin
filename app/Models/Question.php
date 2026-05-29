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
            'exam_score' => 'decimal:2',
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

    /**
     * 주어진 문제 id 들을 이미 출제된 모든 시험지의 questions JSON 스냅샷에서 제거한다.
     * 문제를 단건 삭제할 때(모델 deleted 이벤트)뿐 아니라, 교재 삭제처럼
     * 쿼리빌더로 일괄 삭제(이벤트 미발생)하는 경로에서도 직접 호출해야
     * 학생 사이트에 삭제된 문제가 잔존 노출되지 않는다.
     */
    public static function purgeFromTestSheets(array $questionIds): void
    {
        $questionIds = array_values(array_unique(array_filter(array_map('intval', $questionIds))));
        if (empty($questionIds)) {
            return;
        }

        try {
            $idSet = array_flip($questionIds);

            \App\Models\TestSheet::withoutGlobalScopes()
                ->where(function ($q) use ($questionIds) {
                    foreach ($questionIds as $id) {
                        // MySQL JSON 은 보통 콜론 뒤 공백 없이 저장되지만 두 형태 모두 매치.
                        // 값 뒤에는 항상 ',' 또는 '}' 가 오므로 다른 id 의 접두사 오매치는 없다.
                        $q->orWhere('questions', 'like', '%"id":' . $id . ',%')
                            ->orWhere('questions', 'like', '%"id":' . $id . '}%')
                            ->orWhere('questions', 'like', '%"id": ' . $id . ',%')
                            ->orWhere('questions', 'like', '%"id": ' . $id . '}%');
                    }
                })
                ->chunkById(50, function ($testSheets) use ($idSet) {
                    foreach ($testSheets as $ts) {
                        $questions = $ts->questions ?? [];
                        $filtered = array_values(array_filter($questions, function ($q) use ($idSet) {
                            $qid = is_array($q) ? ($q['id'] ?? null) : null;
                            return $qid === null || !isset($idSet[(int) $qid]);
                        }));

                        if (count($filtered) !== count($questions)) {
                            $ts->timestamps = false;
                            $ts->update(['questions' => $filtered]);
                            $ts->timestamps = true;
                        }
                    }
                });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('시험지 questions JSON 정리 실패', [
                'question_ids' => $questionIds,
                'error' => $e->getMessage(),
            ]);
        }
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

        // 문제가 삭제되면 이미 출제된 시험지의 questions JSON 에서도 제거
        // (그렇지 않으면 학생 사이트에 삭제된 문제가 계속 노출됨)
        static::deleted(function ($model) {
            self::purgeFromTestSheets([$model->id]);
        });

        static::addGlobalScope('material_visibility', function (Builder $builder) {
            if (!auth()->check()) {
                return;
            }

            if (!auth()->user()->userable instanceof \App\Models\Teacher) {
                return;
            }

            // 같은 반의 담임/부담임이 출제한 문제도 서로 볼 수 있어야 함.
            // 현재 사용자가 담임/부담임인 반 목록을 구하고,
            // 그 반의 (담임 + 부담임) 의 user_id 집합을 만든다.
            $teacher = auth()->user()->userable;
            $coTeacherUserIds = [auth()->id()];
            if ($teacher) {
                $classroomIds = \App\Models\Classroom::withoutGlobalScopes()
                    ->where(function ($q) use ($teacher) {
                        $q->where('teacher_id', $teacher->id)
                          ->orWhere('sub_teacher_id', $teacher->id);
                    })
                    ->pluck('id');

                if ($classroomIds->isNotEmpty()) {
                    $coTeacherIds = \App\Models\Classroom::withoutGlobalScopes()
                        ->whereIn('id', $classroomIds)
                        ->get(['teacher_id', 'sub_teacher_id'])
                        ->flatMap(fn($c) => [$c->teacher_id, $c->sub_teacher_id])
                        ->filter()
                        ->unique()
                        ->values();

                    if ($coTeacherIds->isNotEmpty()) {
                        $coTeacherUserIds = \App\Models\User::where('userable_type', \App\Models\Teacher::class)
                            ->whereIn('userable_id', $coTeacherIds)
                            ->pluck('id')
                            ->all();
                    }
                }
            }

            $builder->where(function ($query) use ($coTeacherUserIds) {
                // material이 있는 경우
                $query->where(function ($q) {
                    $q->whereNotNull('material_id')
                        ->whereHas('material', function ($q) {
                            $q->visible();
                        });
                })
                    // material이 없는 경우
                    ->orWhere(function ($q) use ($coTeacherUserIds) {
                        $q->whereNull('material_id')
                            ->where(function ($q) use ($coTeacherUserIds) {
                                $q->whereIn('user_id', $coTeacherUserIds) // 본인 + 같은 반 동료(담임/부담임)
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
