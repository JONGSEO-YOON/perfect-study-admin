<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
