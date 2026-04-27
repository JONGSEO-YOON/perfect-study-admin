<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class Material extends Model
{
    use HasFactory, SoftDeletes;

    public function academy()
    {
        return $this->belongsTo(Academy::class);
    }

    protected static function boot()
    {
        parent::boot();

        // 학원별 필터링 (자기 학원 + 공유받은 학원의 교재)
        static::addGlobalScope('academy_with_sharing', function (Builder $builder) {
            if (!auth()->check()) return;

            $user = auth()->user();

            // root_admin은 전체 조회
            if ($user->role === 'root_admin') return;

            // 본점(academy_id=1, '퍼펙트 스터디')의 admin은 전체 학원 교재 조회 가능
            // → 다른 학원이 만든 교재를 본점에서 확인하고 본점으로 복사 가능
            if ($user->role === 'admin' && $user->academy_id === 1) {
                return;
            }

            $academyId = $user->academy_id;
            if (!$academyId) return;

            $builder->where(function ($q) use ($academyId) {
                // 자기 학원 교재
                $q->where('materials.academy_id', $academyId)
                    // 다른 학원이 공유해준 교재
                    ->orWhereHas('visibleAcademies', function ($sub) use ($academyId) {
                        $sub->where('academy_id', $academyId);
                    });
            });
        });

        // 생성 시 자동으로 academy_id 설정
        static::creating(function ($model) {
            if (!$model->academy_id && auth()->check()) {
                $model->academy_id = auth()->user()->academy_id;
            }
        });

        // deleting 이벤트 등록
        static::deleting(function ($material) {
            // 1. 연관된 질문들 삭제
            $questionIds = $material->questions()->pluck('id')->toArray();

            if (!empty($questionIds)) {
                // Direct delete without complex conditions
                DB::table('questions')->whereIn('id', $questionIds)->delete();
                // Or with Eloquent: Question::whereIn('id', $questionIds)->delete();
            }

            // 2. folder인 경우 하위 항목들 재귀적 삭제
            if ($material->type === 'folder') {
                // 하위 material들 조회
                $children = $material->children()->get();

                // 각 하위 material에 대해 재귀적으로 삭제 수행
                foreach ($children as $child) {
                    $child->delete();
                }
            }
        });
    }

    // 관계 정의
    public function parent()
    {
        return $this->belongsTo(Material::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Material::class, 'parent_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    /**
     * 현재 사용자가 볼 수 있는 자료만 조회하는 스코프
     */
    public function scopeVisible(Builder $query): Builder
    {
        $userId = auth()->id();

        if (auth()->user()->isRoleAbove('manager', true)) {
            return $query;
        }

        return $query->where(function ($query) use ($userId) {
            $query->Where('user_id', $userId)  // 자신이 만든 자료
                ->orWhereHas('visibleUsers', function ($query) use ($userId) {
                    $query->where('users.id', $userId);
                });
        });
    }

    /**
     * 자료를 볼 수 있는 사용자들과의 관계
     */
    public function visibleUsers()
    {
        return $this->belongsToMany(User::class, 'material_user_visibility')
            ->withTimestamps();
    }

    /**
     * 교재를 공유받은 학원들과의 관계
     */
    public function visibleAcademies()
    {
        return $this->belongsToMany(Academy::class, 'material_academy_visibility')
            ->withTimestamps();
    }

    /**
     * 현재 사용자가 편집 가능한 자료만 조회하는 스코프
     */
    public function scopeEditable(Builder $query): Builder
    {
        return $query->where(function ($query) {
            $query->where('user_id', auth()->id())
                ->when(auth()->user()->isRoleAbove('manager', true), function ($query) {
                    $query->orWhereRaw('1 = 1');
                });
        });
    }

    /**
     * 현재 사용자가 이 자료를 편집할 수 있는지 확인
     */
    public function getIsEditableAttribute(): bool
    {

        return auth()->user()->isRoleAbove('manager', true) ||
            $this->user_id === auth()->id();
    }
}
