<?php

namespace App\Services;

use App\Models\FcmToken;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class FcmService
{
    private $fcmTokens;
    private $serviceAccountPath;
    private $projectId;

    public function __construct()
    {
        $this->serviceAccountPath = base_path('perfact-study-firebase-adminsdk-fbsvc-1b82c3585c.json');
        $this->projectId = 'perfact-study';
    }

    /**
     * Google Access Token 생성
     */
    private function getAccessToken()
    {
        try {
            $client = new GoogleClient();
            $client->setAuthConfig($this->serviceAccountPath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

            $accessToken = $client->fetchAccessTokenWithAssertion();

            if (isset($accessToken['error'])) {
                throw new Exception('Failed to generate access token: ' . $accessToken['error_description']);
            }

            return $accessToken['access_token'];
        } catch (Exception $e) {
            Log::error('FCM Access Token 생성 실패: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 단일 기기에 푸시 알림 전송
     */
    public function sendToToken($token, $title, $body, $data = [])
    {
        try {
            $accessToken = $this->getAccessToken();
            $message = [
                'message' => [
                    'token' => $token,
                    // 'notification' => [
                    //     'title' => $title,
                    //     'body' => $body
                    // ],
                    // 'data' => array_map('strval', $data),
                    'webpush' => [
                        'headers' => [
                            'Urgency' => 'high'
                        ],
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                            'icon' => '/icon-parent-192x192.png',
                            'badge' => '/icon-parent-192x192.png',
                            'tag' => 'perfect-study-notification',
                            'requireInteraction' => true
                        ]
                    ]
                ]
            ];
            Log::info('FCM 메시지 data: ' . json_encode($data));

            $headers = [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            Log::info('FCM Response Status: ' . $httpCode);
            Log::info('FCM Response Body: ' . $res);

            if ($httpCode >= 200 && $httpCode < 300) {
                Log::info('FCM 메시지 전송 성공: ' . $res);
                return true;
            } else {
                Log::error('FCM 메시지 전송 실패: ' . $res);
                return false;
            }
        } catch (Exception $e) {
            Log::error('FCM 전송 오류: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 특정 부모에게 푸시 알림 전송
     */
    public function sendToParent($phone, $title, $body, $data = [])
    {
        $tokens = FcmToken::forParent($phone)
            ->pluck('token')
            ->toArray();

        $results = [];
        foreach ($tokens as $token) {
            $results[] = $this->sendToToken($token, $title, $body, $data);
        }

        return $results;
    }

    /**
     * 모든 학부모에게 푸시 알림 전송
     */
    public function sendToAllParents($title, $body, $data = [])
    {
        $tokens = FcmToken::allParents()
            ->pluck('token')
            ->toArray();

        $results = [];
        foreach ($tokens as $token) {
            $results[] = $this->sendToToken($token, $title, $body, $data);
        }

        return $results;
    }

    /**
     * 토큰 검증 및 정리
     */
    public function cleanupInvalidTokens()
    {
        $tokens = FcmToken::all();

        foreach ($tokens as $fcmToken) {
            // 테스트 메시지 전송으로 토큰 유효성 확인
            $result = $this->sendToToken(
                $fcmToken->token,
                'Test',
                'Token validation test',
                ['test' => 'true']
            );

            if (!$result) {
                $fcmToken->delete();
                Log::info('비활성 토큰 삭제됨: ' . $fcmToken->token);
            }
        }
    }
}
