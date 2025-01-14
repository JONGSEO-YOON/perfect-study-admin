<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Material extends Model
{
    use HasFactory, SoftDeletes;

    protected static function boot()
    {
        parent::boot();

        // deleting 이벤트 등록
        static::deleting(function ($material) {
            // 1. 연관된 질문들 삭제
            $material->questions()->delete();

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
