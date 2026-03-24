<?php

namespace App\Http\Middleware;

use App\Models\Academy;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 서브도메인 또는 세션에서 학원을 식별하는 미들웨어
 *
 * 우선순위:
 * 1. 서브도메인 (test-academy.perfectstudy.co.kr)
 * 2. 세션 (academy_slug) - /a/{slug} 경로로 접속 시 설정됨
 * 3. 기본 (academy_id = 1, 퍼펙트 스터디)
 */
class ResolveAcademy
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $baseDomain = config('app.domain', 'perfectstudy.co.kr');

        $academy = null;

        // 1. 서브도메인에서 학원 식별
        if (str_ends_with($host, '.' . $baseDomain)) {
            $subdomain = str_replace('.' . $baseDomain, '', $host);

            if ($subdomain !== 'www' && $subdomain !== '') {
                $academy = Academy::where('slug', $subdomain)
                    ->where('is_active', true)
                    ->first();

                if (!$academy) {
                    abort(404, '학원을 찾을 수 없습니다.');
                }
            }
        }

        // 2. 세션에서 학원 식별 (/a/{slug} 경로로 접속 시)
        if (!$academy && session('academy_slug')) {
            $academy = Academy::where('slug', session('academy_slug'))
                ->where('is_active', true)
                ->first();
        }

        // 3. 기본 도메인 → 퍼펙트 스터디
        if (!$academy) {
            $academy = Academy::find(1);
        }

        // 요청 전체에서 접근 가능하도록 공유
        app()->instance('current_academy', $academy);
        view()->share('currentAcademy', $academy);

        return $next($request);
    }
}
