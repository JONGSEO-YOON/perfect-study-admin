<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;

/**
 * 학원별 데이터 자동 필터링 Global Scope
 *
 * root_admin은 모든 학원 데이터를 조회 가능.
 * 그 외 사용자는 자신의 academy_id 데이터만 조회.
 *
 * 주의: $user->role 대신 DB 직접 조회를 사용하여 순환 참조를 방지.
 */
class AcademyScope implements Scope
{
    private static ?bool $isRootAdmin = null;
    private static ?int $academyId = null;

    public function apply(Builder $builder, Model $model): void
    {
        if (!auth()->check()) {
            return;
        }

        $user = auth()->user();

        // 캐시된 값 사용 (한 요청에서 여러 번 호출되므로)
        if (self::$isRootAdmin === null) {
            self::$academyId = $user->academy_id;

            // userable 관계를 로드하지 않고 DB에서 직접 role 확인
            if ($user->userable_type === 'App\\Models\\Teacher' && $user->userable_id) {
                $role = DB::table('teachers')->where('id', $user->userable_id)->value('role');
                self::$isRootAdmin = ($role === 'root_admin');
            } else {
                self::$isRootAdmin = false;
            }
        }

        // root_admin은 모든 학원 조회 가능
        if (self::$isRootAdmin) {
            return;
        }

        // 나머지 사용자는 자기 학원만
        if (self::$academyId) {
            $builder->where($model->getTable() . '.academy_id', self::$academyId);
        }
    }
}
