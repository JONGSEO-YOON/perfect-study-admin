<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * 학원별 데이터 자동 필터링 Global Scope
 *
 * root_admin은 모든 학원 데이터를 조회 가능.
 * 그 외 사용자는 자신의 academy_id 데이터만 조회.
 */
class AcademyScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (!auth()->check()) {
            return;
        }

        $user = auth()->user();

        // root_admin은 모든 학원 조회 가능
        if ($user->role === 'root_admin') {
            return;
        }

        // 나머지 사용자는 자기 학원만
        if ($user->academy_id) {
            $builder->where($model->getTable() . '.academy_id', $user->academy_id);
        }
    }
}
