<div class="min-h-screen flex flex-col">
    <!-- Success/Error Messages -->
    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 mx-2">
            {{ session('success') }}
        </div>
    @endif
    
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 mx-2">
            {{ session('error') }}
        </div>
    @endif

    <!-- Main Content Area -->
    <main class="flex-1 max-w-6xl mx-auto px-2 sm:px-4 lg:px-6 py-2 sm:py-4 w-full">
        <!-- Welcome Section with Attendance -->
        <div class="bg-violet-50 rounded-lg shadow-sm p-3 sm:p-4 mb-4">
            <div class=" flex justify-between items-start">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-stone-900">{{ $student?->user?->name }} 학생</h2>
                    <p class="text-stone-600 text-sm sm:text-base">학부모님 반갑습니다.</p>
                </div>
            </div>
            <div class="mt-3 sm:mt-4 border-t-[1px] border-violet-600 py-2 sm:py-3">
                <div class="flex flex-col items-start text-violet-600">
                    <span class="text-sm sm:text-base font-bold">{{ now()->locale('ko')->isoFormat('M월 D일 dddd') }}</span> 

                    @if (count($attendances) > 0)
                        @foreach ($attendances as $attendance)
                            <span class="text-sm sm:text-base text-violet-600">{{ $attendance['title'] }} - {{ $attendance['time'] }}</span>
                        @endforeach
                    @else
                        <span class="text-lg sm:text-xl font-semibold">오늘의 출석 기록이 없습니다.</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Learning Status -->
        <div class="bg-fuchsia-50 rounded-lg shadow-sm p-3 sm:p-4 mb-4">
            @if ($weeklyReport && ($weeklyReport['test_report'] || $weeklyReport['homework_report'] || $weeklyReport['comment_report']))
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-stone-900">학습현황</h3>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-stone-600">{{ $weeklyReport['week_label'] }}</span>
                        @if ($weeklyReport['test_report'] || $weeklyReport['homework_report'])
                            <a href="{{ route('parent.report-detail', ['weekKey' => urlencode($weeklyReport['week_key'])]) }}" class="text-sm text-fuchsia-600 flex items-center" wire:navigate>
                                성적표 상세
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>

                @if ($weeklyReport['test_report'])
                    <!-- 주간테스트 요약 -->
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-stone-700 mb-2">주간테스트</h4>
                        @foreach ($weeklyReport['test_report'] as $test)
                            <div class="mb-3 p-3 bg-fuchsia-100 rounded-lg">
                                <div class="text-xs font-medium text-fuchsia-800 mb-2">{{ $test['name'] }}</div>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    @php
                                        $total = $test['total'] ?? null;
                                    @endphp
                                    @if ($total)
                                        <div class="text-center">
                                            <p class="text-stone-600 mb-1">개인점수</p>
                                            <p class="font-bold text-stone-900">{{ number_format($total['personal_score']) }}/{{ number_format($total['total_questions'] ?? 0) }}</p>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-stone-600 mb-1">반평균</p>
                                            <p class="font-bold text-stone-900">{{ number_format($total['classroom_average']) }}</p>
                                        </div>
                                        {{-- <div class="text-center">
                                            <p class="text-stone-600 mb-1">반별 등수</p>
                                            <p class="font-bold text-stone-900">{{ $total['classroom_rank'] }}등 ({{ $total['classroom_students_count'] ?? 0 }})</p>
                                        </div> --}}
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if ($weeklyReport['homework_report'])
                    <!-- 주간숙제 요약 -->
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-stone-700 mb-2">주간숙제</h4>
                        @foreach ($weeklyReport['homework_report'] as $homework)
                            <div class="mb-3 p-3 bg-violet-100 rounded-lg">
                                <div class="text-xs font-medium text-violet-800 mb-2">{{ $homework['name'] }}</div>
                                <div class="grid grid-cols-3 gap-4 text-sm">
                                    @php
                                        $total = $homework['total'] ?? null;
                                    @endphp
                                    @if ($total)
                                        <div class="text-center">
                                            <p class="text-stone-600 mb-1">이행도</p>
                                            <p class="font-bold text-stone-900">{{ number_format($total['attempt_rate'], 1) }}%</p>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-stone-600 mb-1">정답률</p>
                                            <p class="font-bold text-stone-900">{{ number_format($total['correct_rate'], 1) }}%</p>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-stone-600 mb-1">문제 수</p>
                                            <p class="font-bold text-stone-900">{{ $total['correct_count'] }}/{{ $total['total_count'] }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if ($weeklyReport['comment_report'])
                    <!-- 강사 코멘트 -->
                    <div class="border-t pt-4 border-fuchsia-600">
                        <h4 class="text-sm font-medium text-stone-700 mb-2">강사 코멘트</h4>
                        <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-2 border border-purple-200">
                            <p class="text-stone-800 text-sm leading-relaxed">{{ $weeklyReport['comment_report'] }}</p>
                        </div>
                    </div>
                @endif
            @else
                <!-- 성적표가 없을 때 -->
                <div class="flex flex-col items-center justify-center py-8 sm:py-12">
                    <div class="mt-4 sm:mt-6 text-center">
                        <h4 class="text-lg sm:text-xl font-bold text-fuchsia-600">
                            이번 주차 성적표 없음
                        </h4>
                        <p class="text-sm text-stone-500 mt-2">이번 주에는 성적표 데이터가 없습니다.</p>
                    </div>
                </div>
            @endif
        </div>
        @if (count($studentNotices) > 0)
        <!-- Student Notices -->
        <div class="bg-amber-50 rounded-lg shadow-sm p-3 sm:p-4 mb-4">
            <h3 class="text-base sm:text-lg font-bold text-stone-900 mb-3 sm:mb-4">학생 공지</h3>
            <div class="space-y-2 sm:space-y-3">
                @foreach ($studentNotices as $studentNotice)
                    <div class="flex items-center tracking-tight gap-2">
                        <a href="{{ route('parent.student-notice', $studentNotice->id) }}" class="text-sm sm:text-base text-stone-700 flex items-center gap-2">
                            {{ $studentNotice->created_at->format('Y-m-d') }}
                            @if ($studentNotice->only_parent)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-amber-500 text-white">학부모</span>
                            @endif
                            {{ $studentNotice->title }}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
        @if (count($notices) > 0)
        <!-- Announcements -->
        <div class="bg-stone-100 rounded-lg shadow-sm p-3 sm:p-4">
            <h3 class="text-base sm:text-lg font-bold text-stone-900 mb-3 sm:mb-4">공지사항</h3>
            <div class="space-y-2 sm:space-y-3">
                @foreach ($notices as $notice)
                    <div class="flex items-center tracking-tight">
                        <a href="{{ route('parent.notice', $notice->id) }}" class="text-sm sm:text-base text-stone-700"> {{ $notice->created_at->format('Y-m-d') }} {{ $notice->title }}</a>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
        <!-- Footer Actions -->
        <div class="max-w-6xl mx-auto px-2 sm:px-4 lg:px-6 pb-6 mt-16 w-full space-y-3">
            <button id="notificationButton" wire:click="enableNotifications" class="w-full py-3 text-center bg-violet-500 hover:bg-violet-600 text-white rounded-lg font-semibold" style="display: none;">알림 켜기</button>
            <button id="installButton" class="w-full py-3 text-center bg-purple-500 hover:bg-purple-600 text-white rounded-lg font-semibold" style="display: none;">홈 화면에 설치</button>
            <button wire:click="logout" class="w-full py-3 text-center hover:bg-stone-100 border border-violet-500 rounded-lg text-violet-500 font-semibold">로그아웃</button>
        </div>
    </main>
</div>

@script
<script>
    console.log('스크립트 블록 실행됨');

    // PWA 모드 확인 함수
    function isPWAMode() {
        // standalone 모드 (홈화면에서 실행)
        if (window.matchMedia('(display-mode: standalone)').matches) {
            return true;
        }

        // iOS Safari PWA 확인
        if (window.navigator.standalone === true) {
            return true;
        }

        // Android Chrome PWA 확인
        if (document.referrer.includes('android-app://')) {
            return true;
        }

        return false;
    }

    // FCM 알림 상태 확인 (실제 브라우저 권한 + 서버 토큰 등록 여부)
    async function isFcmAlreadyEnabled() {
        // 1. 알림 권한이 granted 상태여야 함
        if (typeof Notification === 'undefined' || Notification.permission !== 'granted') {
            return false;
        }

        // 2. localStorage에 토큰이 저장되어 있어야 함
        const enabled = localStorage.getItem('fcm_enabled') === 'true';
        const token = localStorage.getItem('fcm_token');
        if (!enabled || !token) {
            return false;
        }

        // 3. service worker가 실제로 등록되어 있어야 함
        if (!('serviceWorker' in navigator)) {
            return false;
        }
        try {
            const registration = await navigator.serviceWorker.getRegistration('/firebase-messaging-sw.js');
            if (!registration || !registration.active) {
                return false;
            }
        } catch (e) {
            return false;
        }

        return true;
    }

    // PWA 모드 확인 후 버튼 표시/숨김 + 토큰 자동 갱신
    (async () => {
        const notificationButton = document.getElementById('notificationButton');
        const installButton = document.getElementById('installButton');

        if (!isPWAMode()) {
            console.log('브라우저 모드 - 설치 버튼 표시');
            notificationButton.style.display = 'none';
            installButton.style.display = 'block';
            return;
        }

        console.log('PWA 모드 감지');
        installButton.style.display = 'none';

        // 알림 권한이 이미 허용되어 있으면, 진입할 때마다 토큰을 조용히 재발급/재저장한다.
        //  - FCM 토큰은 주기적으로 회전(rotate)되므로, "알림 켜기" 버튼을 누른 그 순간에만 저장하면
        //    며칠 뒤 서버 토큰이 만료되어 학부모에게 푸시가 전혀 가지 않게 된다.
        //  - 진입 시 자동 갱신하면 토큰이 항상 최신으로 유지되고, "알림 켜기" 버튼이 다시
        //    노출되는(설정이 유지되지 않는) 현상도 함께 해결된다.
        if (typeof Notification !== 'undefined' && Notification.permission === 'granted') {
            notificationButton.style.display = 'none';
            try {
                const token = await initializeFCM(); // 권한이 granted 면 프롬프트가 뜨지 않음
                if (token) {
                    localStorage.setItem('fcm_enabled', 'true');
                    localStorage.setItem('fcm_token', token);
                    console.log('FCM 토큰 자동 갱신 완료');
                } else {
                    notificationButton.style.display = 'block';
                }
            } catch (e) {
                console.error('FCM 토큰 자동 갱신 실패:', e);
                // 갱신 실패 시 사용자가 수동으로 다시 켤 수 있도록 버튼 노출
                notificationButton.style.display = 'block';
            }
        } else {
            // 권한 미요청/거부 상태 → "알림 켜기" 버튼 노출
            console.log('알림 권한 없음 - 알림 버튼 표시');
            notificationButton.style.display = 'block';
        }
    })();

    // iOS 확인 함수
    function isIos() {
        return /iPad|iPhone|iPod/.test(navigator.userAgent);
    }

    // PWA 설치 프롬프트
    let deferredPrompt;
    window.addEventListener('beforeinstallprompt', async (e) => {
        e.preventDefault();
        deferredPrompt = e;
    });

    // 설치 버튼 클릭 이벤트
    document.getElementById('installButton').addEventListener('click', () => {
        // iOS Safari의 경우
        if (isIos() && !isPWAMode()) {
            alert('Safari에서 하단 공유 버튼을 클릭한 후, [홈 화면에 추가] 버튼을 선택하세요');
            return;
        }

        // PWA 설치 프롬프트
        if (deferredPrompt) {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('사용자가 설치를 수락했습니다.');
                    document.getElementById('installButton').style.display = 'none';
                } else {
                    console.log('사용자가 설치를 거부했습니다.');
                }
                deferredPrompt = null;
            });
        } else {
            // 이벤트가 준비되지 않은 경우, 사용자에게 수동 설치를 안내
            alert('브라우저 메뉴에서 홈 화면에 추가를 진행해 주세요.');
        }
    });
    
    // Service Worker 등록 함수
    async function registerServiceWorker() {
        console.log('registerServiceWorker 함수 호출됨');
        
        if ('serviceWorker' in navigator) {
            console.log('Service Worker 지원됨');
            try {
                const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
                console.log('Service Worker 등록 성공:', registration);
                
                await navigator.serviceWorker.ready;
                console.log('Service Worker 완전히 준비됨');
                
                return registration;
            } catch (error) {
                console.error('Service Worker 등록 실패:', error);
                throw error;
            }
        } else {
            console.log('Service Worker 지원되지 않음');
            throw new Error('Service Worker가 지원되지 않습니다.');
        }
    }
    
    // 즉시 실행
    // registerServiceWorker().catch(console.error);
    // 로그아웃 시 로컬스토리지 정리
    $wire.on('clear-local-storage', () => {
        try {
            localStorage.removeItem('parent_phone');
        } catch (e) {
            // 무시
        }
    });

    // FCM 초기화 함수들
    async function initializeFCM() {
        try {
            console.log('1. Firebase SDK import 시작');
            // Firebase SDK 동적 import
            const { initializeApp } = await import('https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js');
            const { getMessaging, getToken, isSupported } = await import('https://www.gstatic.com/firebasejs/10.8.0/firebase-messaging.js');
            console.log('2. Firebase SDK import 완료');
            
            const firebaseConfig = {
                apiKey: "AIzaSyBVxK6CCtCGABUWqCBt3DqAo_yF6bQ5m94",
                authDomain: "perfact-study.firebaseapp.com",
                projectId: "perfact-study",
                storageBucket: "perfact-study.appspot.com",
                messagingSenderId: "847897044429",
                appId: "1:847897044429:web:7b566a588c912882ba1084"
            };
            
            console.log('3. Firebase App 초기화 시작');
            const firebaseApp = initializeApp(firebaseConfig);
            console.log('4. Firebase App 초기화 완료');
            
            // FCM 지원 확인
            console.log('5. FCM 지원 확인 시작');
            const supported = await isSupported();
            console.log('6. FCM 지원 확인 결과:', supported);
            if (!supported) {
                throw new Error('FCM이 지원되지 않는 브라우저입니다.');
            }
            
            console.log('7. messaging 객체 생성 시작');
            const messaging = getMessaging(firebaseApp);
            console.log('8. messaging 객체 생성 완료');
            
            // 알림 권한 요청
            console.log('9. 알림 권한 요청 시작');
            const permission = await Notification.requestPermission();
            console.log('10. 알림 권한 결과:', permission);
            if (permission !== 'granted') {
                throw new Error('알림 권한이 허용되지 않았습니다.');
            }
            
            // Service Worker 등록 (register 는 이미 등록된 경우 동일한 registration 반환)
            console.log('11. Service Worker 등록 시작');
            await navigator.serviceWorker.register('/firebase-messaging-sw.js');
            console.log('12. Service Worker 등록 완료');

            // 항상 active 상태가 될 때까지 대기 (no active service worker 오류 방지)
            // navigator.serviceWorker.ready 는 active worker 가 있을 때만 resolve 됨
            console.log('13. Service Worker ready 대기 시작');
            const registration = await navigator.serviceWorker.ready;
            console.log('14. Service Worker ready 완료:', registration);

            if (!registration.active) {
                throw new Error('Service Worker 가 활성화되지 않았습니다. 페이지를 새로고침 한 뒤 다시 시도해주세요.');
            }

            // FCM 토큰 생성 (serviceWorkerRegistration 명시적으로 전달)
            console.log('22. FCM 토큰 생성 시작');
            const currentToken = await getToken(messaging, {
                vapidKey: 'BB4OAtniiO1lEmvxHzpLlpn9zQuCJ0Sc9uIEfanUmpPBWAkJEYYIc5bsWz5A0mylGxWw3vgpHBUxIwcKpLqwYTk',
                serviceWorkerRegistration: registration
            });
            console.log('23. FCM 토큰 생성 완료');
            
            if (currentToken) {
                console.log('24. FCM 토큰:', currentToken);
                
                // 서버에 토큰 전송
                console.log('25. 서버에 토큰 전송 시작');
                await sendTokenToServer(currentToken);
                console.log('26. 서버에 토큰 전송 완료');
                
                return currentToken;
            } else {
                console.log('24. FCM 토큰을 생성할 수 없습니다.');
                return null;
            }
        } catch (error) {
            console.error('FCM 초기화 실패:', error);
            throw error;
        }
    }
    
    // 서버에 토큰 전송
    async function sendTokenToServer(token) {
        const parentPhone = localStorage.getItem('parent_phone');
        
        if (!parentPhone) {
            throw new Error('부모 전화번호를 찾을 수 없습니다. 다시 로그인해주세요.');
        }
        
        const response = await fetch('/api/fcm-token', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                token: token,
                parent_phone: parentPhone
            })
        });
        
        if (!response.ok) {
            throw new Error('토큰 전송 실패');
        }
        
        console.log('FCM 토큰이 서버에 저장되었습니다.');
    }

    // Livewire 이벤트로 FCM 초기화
    $wire.on('enable-fcm', async () => {
        console.log('enable-fcm 이벤트 수신됨');
        try {
            console.log('FCM 초기화 시작');
            const token = await initializeFCM();
            console.log('FCM 초기화 완료, 토큰:', token);

            if (token) {
                // 로컬 스토리지에 알림 상태 저장
                localStorage.setItem('fcm_enabled', 'true');
                localStorage.setItem('fcm_token', token);

                // 알림 버튼 숨김
                const notificationButton = document.getElementById('notificationButton');
                if (notificationButton) {
                    notificationButton.style.display = 'none';
                }

                // Livewire에 성공 알림
                console.log('fcmEnabled 호출');
                $wire.call('fcmEnabled');
            } else {
                throw new Error('토큰 생성에 실패했습니다.');
            }
        } catch (error) {
            console.error('FCM 초기화 오류:', error);
            // 오류 발생 시 로컬 상태도 초기화 (다음 시도를 위해)
            localStorage.removeItem('fcm_enabled');
            localStorage.removeItem('fcm_token');
            // Livewire에 오류 알림
            $wire.call('fcmError', error.message);
        }
    });
</script>
@endscript
