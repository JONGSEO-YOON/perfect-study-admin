# FCM 푸시 알림 설정 가이드

학부모용 PWA에서 FCM 푸시 알림을 받기 위한 설정이 완료되었습니다.

## 🚀 설정된 파일들

### 1. Firebase Service Worker
- **파일**: `/public/firebase-messaging-sw.js`
- **역할**: 백그라운드 메시지 수신 및 알림 표시

### 2. Firebase 설정 모듈
- **파일**: `/resources/js/firebase-config.js`
- **역할**: FCM 초기화, 토큰 생성, 포그라운드 메시지 처리

### 3. 데이터베이스 테이블
- **마이그레이션**: `database/migrations/2025_08_21_170413_create_fcm_tokens_table.php`
- **모델**: `app/Models/FcmToken.php`
- **역할**: FCM 토큰 저장 및 관리

### 4. FCM 서비스 클래스
- **파일**: `app/Services/FcmService.php`
- **역할**: 푸시 알림 전송 로직

### 5. API 엔드포인트
- **FCM 토큰 저장**: `POST /api/fcm-token`
- **푸시 알림 전송**: `POST /api/send-push-notification`

## ⚙️ 추가 설정이 필요한 항목

### 1. Firebase 프로젝트 설정
현재 임시 값들을 실제 Firebase 프로젝트 값으로 교체해야 합니다:

#### `/public/firebase-messaging-sw.js` 파일 수정:
```javascript
const firebaseConfig = {
    apiKey: "실제-API-키",
    authDomain: "perfact-study.firebaseapp.com",
    projectId: "perfact-study",
    storageBucket: "perfact-study.appspot.com", 
    messagingSenderId: "실제-SENDER-ID",
    appId: "실제-APP-ID"
};
```

#### `/resources/js/firebase-config.js` 파일 수정:
```javascript
const firebaseConfig = {
    // 위와 동일한 값으로 설정
};

const VAPID_KEY = "실제-VAPID-키";
```

### 2. Firebase Console 설정
1. [Firebase Console](https://console.firebase.google.com)에서 프로젝트 설정 이동
2. **프로젝트 설정 > 일반 > 웹 앱**에서 설정 값 확인
3. **클라우드 메시징**에서 VAPID 키 생성
4. 웹 푸시 인증서 키 페어 생성

### 3. 데이터베이스 마이그레이션 실행
```bash
php artisan migrate
```

## 🔧 사용 방법

### 1. 학부모 앱에서 알림 활성화
학부모가 로그인 페이지에서 **"알림 켜기"** 버튼을 클릭하면:
1. 알림 권한 요청
2. FCM 토큰 생성
3. 서버에 토큰 저장
4. 포그라운드 메시지 리스너 활성화

### 2. 관리자에서 푸시 알림 전송
API 엔드포인트를 사용해 푸시 알림을 전송할 수 있습니다:

```javascript
// 모든 학부모에게 전송
fetch('/api/send-push-notification', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': 'csrf-token'
    },
    body: JSON.stringify({
        title: '새 공지사항',
        body: '중요한 공지사항이 등록되었습니다.',
        user_type: 'parent'
    })
});
```

### 3. 특정 사용자에게 전송
```javascript
fetch('/api/send-push-notification', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': 'csrf-token'
    },
    body: JSON.stringify({
        title: '개별 알림',
        body: '학습 리포트가 준비되었습니다.',
        user_type: 'parent',
        user_id: 123,
        data: {
            type: 'report',
            report_id: '456'
        }
    })
});
```

## 🎯 알림 타입별 사용 예시

### 1. 공지사항 알림
```javascript
{
    title: '새 공지사항',
    body: '퍼펙트 스터디에서 중요한 공지사항을 확인하세요.',
    user_type: 'parent',
    data: {
        type: 'notice',
        notice_id: '123'
    }
}
```

### 2. 성적표 알림
```javascript
{
    title: '성적표 완성',
    body: '자녀의 새로운 성적표가 준비되었습니다.',
    user_type: 'parent',
    user_id: 456,
    data: {
        type: 'report_card',
        student_id: '789'
    }
}
```

### 3. 출석 알림
```javascript
{
    title: '출석 확인',
    body: '자녀가 수업에 참석했습니다.',
    user_type: 'parent',
    user_id: 456,
    data: {
        type: 'attendance',
        student_id: '789',
        class_id: '101'
    }
}
```

## 🛡️ 보안 고려사항

1. **Firebase 서비스 계정 키**: `perfact-study-firebase-adminsdk-fbsvc-1b82c3585c.json` 파일은 서버에만 두고 Git에 커밋하지 마세요.

2. **VAPID 키**: 웹 푸시 인증을 위해 Firebase Console에서 생성한 키를 안전하게 관리하세요.

3. **토큰 정리**: 비활성 토큰들을 정기적으로 정리하는 스케줄러를 설정하세요.

## 📋 테스트 방법

1. **로컬 개발**: 
   - HTTPS가 필요하므로 로컬에서는 `ngrok` 등을 사용
   - 또는 Laravel Valet, Laravel Herd 사용

2. **토큰 확인**:
   ```bash
   php artisan tinker
   \App\Models\FcmToken::all()
   ```

3. **테스트 알림 전송**:
   ```bash
   # Postman이나 curl을 사용해 API 엔드포인트 테스트
   curl -X POST https://your-domain.com/api/send-push-notification \
   -H "Content-Type: application/json" \
   -d '{"title":"테스트","body":"테스트 메시지","user_type":"parent"}'
   ```

## 🔄 다음 단계

1. Firebase 프로젝트 설정 완료
2. 실제 환경에서 HTTPS 설정
3. 데이터베이스 마이그레이션 실행
4. 관리자 패널에서 푸시 알림 전송 UI 구현
5. 사용자별 알림 설정 기능 추가

이제 PWA에서 FCM 푸시 알림을 받을 수 있는 기본 구조가 완성되었습니다!