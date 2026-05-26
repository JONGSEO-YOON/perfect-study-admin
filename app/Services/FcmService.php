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
    private $serviceAccountJson;
    private $projectId;

    public function __construct()
    {
        $this->projectId = config('services.fcm.project_id', 'perfact-study');

        // 1) 환경변수 FIREBASE_CREDENTIALS_JSON 우선 (배포 환경에 안전)
        $envJson = env('FIREBASE_CREDENTIALS_JSON');
        if ($envJson) {
            $decoded = json_decode($envJson, true);
            if (is_array($decoded)) {
                $this->serviceAccountJson = $decoded;
                return;
            }
        }

        // 2) 가능한 파일 위치들을 순서대로 탐색
        $candidates = array_filter([
            env('FIREBASE_CREDENTIALS_PATH'),
            base_path('perfact-study-firebase-adminsdk-fbsvc-1b82c3585c.json'),
            base_path('firebase-credentials.json'),
            storage_path('app/firebase/credentials.json'),
            storage_path('app/firebase-credentials.json'),
        ]);

        foreach ($candidates as $path) {
            if ($path && file_exists($path)) {
                $this->serviceAccountPath = $path;
                return;
            }
        }
    }

    /**
     * Google Access Token 생성
     */
    private function getAccessToken()
    {
        try {
            if (!$this->serviceAccountPath && !$this->serviceAccountJson) {
                throw new Exception(
                    'Firebase 서비스 계정 키가 설정되지 않았습니다. ' .
                    '다음 중 하나로 설정해 주세요: ' .
                    '(1) .env 의 FIREBASE_CREDENTIALS_JSON 에 JSON 문자열 직접 입력, ' .
                    '(2) .env 의 FIREBASE_CREDENTIALS_PATH 에 파일 경로 입력, ' .
                    '(3) ' . base_path('firebase-credentials.json') . ' 에 파일 업로드'
                );
            }

            $client = new GoogleClient();

            if ($this->serviceAccountJson) {
                $client->setAuthConfig($this->serviceAccountJson);
            } else {
                $client->setAuthConfig($this->serviceAccountPath);
            }

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

            // data-only 메시지로 변경 (중복 알림 방지)
            $message = [
                'message' => [
                    'token' => $token,
                    'data' => array_map('strval', array_merge($data, [
                        'title' => $title,
                        'body' => $body,
                        'messageId' => 'msg_' . time() . '_' . uniqid()
                    ])),
                    'webpush' => [
                        'headers' => [
                            'Urgency' => 'high'
                        ],
                        'fcm_options' => [
                            'link' => '/parent'
                        ]
                    ]
                ]
            ];

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

            if ($httpCode >= 200 && $httpCode < 300) {
                Log::info('FCM 전송 성공', ['token_prefix' => substr($token, 0, 12), 'title' => $title]);
                return true;
            }

            // FCM 에러 응답 파싱
            $errorBody = json_decode($res, true);
            $errorCode = $errorBody['error']['details'][0]['errorCode'] ?? null;
            $errorStatus = $errorBody['error']['status'] ?? null;

            Log::error('FCM 메시지 전송 실패', [
                'http_code' => $httpCode,
                'error_code' => $errorCode,
                'error_status' => $errorStatus,
                'token_prefix' => substr($token, 0, 12),
                'response' => $res,
            ]);

            // 무효 토큰(만료/미등록/잘못된 형식)이면 DB 에서 자동 삭제
            $invalidTokenCodes = ['UNREGISTERED', 'INVALID_ARGUMENT', 'INVALID_REGISTRATION'];
            $invalidStatuses = ['NOT_FOUND', 'INVALID_ARGUMENT', 'UNREGISTERED'];
            if (in_array($errorCode, $invalidTokenCodes, true)
                || in_array($errorStatus, $invalidStatuses, true)
                || $httpCode === 404) {
                FcmToken::where('token', $token)->delete();
                Log::warning('FCM 무효 토큰 삭제됨', ['token_prefix' => substr($token, 0, 12)]);
            }

            return false;
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
