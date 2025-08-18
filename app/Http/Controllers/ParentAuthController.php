<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;

class ParentAuthController extends Controller
{
    public function restore(Request $request)
    {
        $phone = (string) $request->input('phone');

        if ($phone === '') {
            return response()->json(['ok' => false, 'message' => '전화번호가 필요합니다.'], 422);
        }

        $studentExists = Student::where('phone_father', $phone)
            ->orWhere('phone_mother', $phone)
            ->exists();

        if (! $studentExists) {
            return response()->json(['ok' => false, 'message' => '학생을 찾을 수 없습니다.'], 404);
        }

        session(['parent_phone' => $phone]);

        return response()->json(['ok' => true]);
    }

    public function restoreAndRedirect(Request $request)
    {
        $phone = (string) $request->query('phone', '');

        if ($phone === '') {
            return redirect()->route('parent.login');
        }

        $studentExists = Student::where('phone_father', $phone)
            ->orWhere('phone_mother', $phone)
            ->exists();

        if (! $studentExists) {
            return redirect()->route('parent.login');
        }

        session(['parent_phone' => $phone]);

        return redirect()->route('parent.home');
    }

    public function logout(Request $request)
    {
        // 서버 세션 초기화
        $request->session()->forget('parent_phone');
        $request->session()->save();

        // 로컬스토리지도 제거시키고 로그인 페이지로 이동시키는 간단한 HTML 반환
        return response()->make(
            '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
                . '</head><body>'
                . '<script>try{localStorage.removeItem("parent_phone");}catch(e){}window.location.replace("/parent");</script>'
                . '<noscript><a href="/parent">로그인 페이지로 이동</a></noscript>'
                . '</body></html>',
            200,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    }
}
