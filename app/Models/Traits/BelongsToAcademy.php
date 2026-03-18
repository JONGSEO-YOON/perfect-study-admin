<?php

namespace App\Models\Traits;

use App\Models\Academy;
use App\Models\Scopes\AcademyScope;

/**
 * 학원 소속 trait
 *
 * 이 trait를 사용하는 모델은:
 * 1. academy() 관계를 갖습니다
 * 2. AcademyScope가 자동 적용되어 학원별 필터링됩니다
 * 3. 생성 시 현재 사용자의 academy_id가 자동 설정됩니다
 */
trait BelongsToAcademy
{
    public static function bootBelongsToAcademy(): void
    {
        // Global Scope 등록
        static::addGlobalScope(new AcademyScope);

        // 생성 시 자동으로 academy_id 설정
        static::creating(function ($model) {
            if (!$model->academy_id && auth()->check()) {
                $model->academy_id = auth()->user()->academy_id;
            }
        });
    }

    public function academy()
    {
        return $this->belongsTo(Academy::class);
    }
}
