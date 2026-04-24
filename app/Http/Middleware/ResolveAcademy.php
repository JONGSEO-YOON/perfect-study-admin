<?php

namespace App\Http\Middleware;

use App\Models\Academy;
use App\Models\Student;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 서브도메인/세션/사용자에서 학원을 식별하는 미들웨어
 *
 * 우선순위:
 * 1. 서브도메인 (test-academy.perfectstudy.co.kr) - 명시적이므로 최우선
 * 2. 부모님 앱: 세션 parent_academy_id (헤더에서 학원 선택한 값)
 * 3. 인증된 사용자의 academy_id (학생/강사 로그인 상태)
 * 4. 부모님 세션 (parent_phone) → 학생 lookup → 첫 번째 학생의 academy
 * 5. 세션 academy_slug (/a/{slug} 경로 접속 시)
 * 6. 기본 (academy_id = 1, 퍼펙트 스터디)
 */
class ResolveAcademy
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $baseDomain = config('app.domain', 'perfectstudy.co.kr');

        $academy = null;

        // 1. 서브도메인에서 학원 식별 (가장 명시적)
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

        // 2. 부모님 앱이 명시적으로 선택한 학원 (헤더 셀렉트)
        if (!$academy && session('parent_academy_id')) {
            $academy = Academy::find(session('parent_academy_id'));
        }

        // 3. 인증된 사용자의 academy (학생/강사)
        if (!$academy && auth()->check() && auth()->user()->academy_id) {
            $academy = Academy::find(auth()->user()->academy_id);
        }

        // 4. 부모님 세션 (parent_phone) → 첫 번째 학생의 academy
        if (!$academy && session('parent_phone')) {
            $parentPhone = session('parent_phone');
            $student = Student::where('phone_father', $parentPhone)
                ->orWhere('phone_mother', $parentPhone)
                ->first();
            if ($student && $student->academy_id) {
                $academy = Academy::find($student->academy_id);
            }
        }

        // 5. 세션 academy_slug (/a/{slug} 경로 접속 시)
        if (!$academy && session('academy_slug')) {
            $academy = Academy::where('slug', session('academy_slug'))
                ->where('is_active', true)
                ->first();
        }

        // 6. 기본 도메인 → 퍼펙트 스터디
        if (!$academy) {
            $academy = Academy::find(1);
        }

        // 요청 전체에서 접근 가능하도록 공유
        app()->instance('current_academy', $academy);
        view()->share('currentAcademy', $academy);

        return $next($request);
    }
}
