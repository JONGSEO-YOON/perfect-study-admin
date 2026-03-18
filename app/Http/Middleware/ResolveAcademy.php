<?php

namespace App\Http\Middleware;

use App\Models\Academy;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 서브도메인에서 학원을 식별하는 미들웨어
 *
 * - perfectstudy.co.kr → academy_id = 1 (기본)
 * - test-academy.perfectstudy.co.kr → slug = test-academy
 * - www.perfectstudy.co.kr → 기본
 * - localhost → 기본 (개발환경)
 */
class ResolveAcademy
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $baseDomain = config('app.domain', 'perfectstudy.co.kr');

        $academy = null;

        // 서브도메인 추출
        if (str_ends_with($host, '.' . $baseDomain)) {
            $subdomain = str_replace('.' . $baseDomain, '', $host);

            // www는 기본 도메인 취급
            if ($subdomain !== 'www' && $subdomain !== '') {
                $academy = Academy::where('slug', $subdomain)
                    ->where('is_active', true)
                    ->first();

                if (!$academy) {
                    abort(404, '학원을 찾을 수 없습니다.');
                }
            }
        }

        // 기본 도메인이거나 서브도메인 없음 → 퍼펙트 스터디
        if (!$academy) {
            $academy = Academy::find(1);
        }

        // 요청 전체에서 접근 가능하도록 공유
        app()->instance('current_academy', $academy);
        view()->share('currentAcademy', $academy);

        return $next($request);
    }
}
