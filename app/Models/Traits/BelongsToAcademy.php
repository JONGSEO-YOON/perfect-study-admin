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
        // 우선순위:
        //   1. 모델에 이미 명시적으로 set되어 있으면 그대로
        //   2. 현재 접속한 학원(서브도메인 기반) → root_admin이 다른 학원 작업 시 이게 핵심
        //   3. 인증된 사용자의 academy_id (메인 도메인 일반 사용)
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

    public function academy()
    {
        return $this->belongsTo(Academy::class);
    }
}
