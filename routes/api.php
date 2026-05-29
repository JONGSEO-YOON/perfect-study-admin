<?php

use App\Http\Controllers\SignupController;
use App\Services\FcmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::post('/signup', [SignupController::class, 'signup']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/pdf-conversion-progress/{attachment}', function ($attachment) {
    return Cache::get("pdf_conversion_{$attachment}", [
        'progress' => 0,
        'currentPage' => 0,
        'totalPages' => 0,
        'material_id' => null,
        'is_public' => false
    ]);
});

Route::post('/fcm-token', function (Request $request) {
    try {
        $request->validate([
            'token' => 'required|string',
            'parent_phone' => 'required|string'
        ]);

        $token = $request->token;
        $parentPhone = $request->parent_phone;

        // 같은 토큰이 다른 전화번호로 저장돼 있던 경우 정리 (기기 공유/재로그인)
        \App\Models\FcmToken::where('token', $token)
            ->where('parent_phone', '!=', $parentPhone)
            ->delete();

        // 토큰 기준 upsert.
        //  - 한 학부모가 여러 기기(폰/태블릿)를 쓰는 경우를 지원한다.
        //  - 기존처럼 전화번호로 전부 지우고 새로 만들면, 진입 시 자동 갱신할 때마다
        //    다른 기기의 유효한 토큰을 지워버려 푸시가 안 가는 문제가 생긴다.
        //  - 만료/무효 토큰은 FcmService 가 전송 실패 시 자동 삭제한다.
        \App\Models\FcmToken::updateOrCreate(
            ['token' => $token],
            ['parent_phone' => $parentPhone]
        );

        return response()->json([
            'success' => true,
            'message' => 'FCM 토큰이 성공적으로 저장되었습니다.',
            'data' => [
                'parent_phone' => $parentPhone,
                'token' => $token
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'FCM 토큰 저장 중 오류가 발생했습니다.',
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::post('/send-push-notification', function (Request $request) {
    try {
        $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
            'parent_phone' => 'nullable|string',
            'send_to_all' => 'nullable|boolean'
        ]);

        $fcmService = new FcmService();
        $title = $request->title;
        $body = $request->body;
        $data = $request->data ?? [];
        
        $results = [];
        
        if ($request->has('parent_phone') && $request->parent_phone) {
            // 특정 부모에게 전송
            $results = $fcmService->sendToParent($request->parent_phone, $title, $body, $data);
        } else {
            // 모든 부모에게 전송
            $results = $fcmService->sendToAllParents($title, $body, $data);
        }

        $successCount = count(array_filter($results));
        $totalCount = count($results);

        return response()->json([
            'success' => true,
            'message' => "푸시 알림이 전송되었습니다. ({$successCount}/{$totalCount})",
            'data' => [
                'success_count' => $successCount,
                'total_count' => $totalCount
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => '푸시 알림 전송 중 오류가 발생했습니다.',
            'error' => $e->getMessage()
        ], 500);
    }
});
