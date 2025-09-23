<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="/manifest_parent.json">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    @stack('scripts')

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Livewire Styles -->
    @livewireStyles
</head>

<body>
    <script>
        // 로그인 성공으로 세션이 생성된 경우, 로컬스토리지에도 동기화하여 PWA 자동 로그인에 사용
        (function () {
            try {
                var phone = @json(session('parent_phone'));
                if (phone) {
                    localStorage.setItem('parent_phone', phone);
                }
            } catch (e) { }
        })();

        // Service Worker 메시지 리스너 (포그라운드 상태에서 알림 클릭 처리)
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.addEventListener('message', (event) => {
                if (event.data && event.data.action === 'navigate') {
                    const targetUrl = event.data.url;

                    // 현재 페이지가 목표 페이지와 다르면 이동
                    if (window.location.pathname !== targetUrl) {
                        window.location.href = targetUrl;
                    }
                }

                // Service Worker에서 온 뱃지 업데이트 메시지 처리 (백그라운드 상태)
                if (event.data && event.data.type === 'UPDATE_BADGE') {
                    console.log('[BADGE_UPDATE] Service Worker에서 뱃지 업데이트 요청:', event.data);

                    const notificationType = event.data.notificationType;
                    if (notificationType) {
                        try {
                            let notifications = JSON.parse(localStorage.getItem('parentNotifications') || '{}');
                            console.log('[BADGE_UPDATE] 기존 localStorage:', notifications);

                            notifications[notificationType] = true;
                            localStorage.setItem('parentNotifications', JSON.stringify(notifications));

                            console.log('[BADGE_UPDATE] 업데이트된 localStorage:', notifications);

                            // 뱃지 업데이트
                            updateNavigationBadges();
                        } catch (e) {
                            console.error('[BADGE_UPDATE] localStorage 에러:', e);
                        }
                    }
                }

                // FCM 메시지 처리 (포그라운드 상태에서 받은 알림)
                if (event.data && event.data.type === 'FCM_MESSAGE') {
                    console.log('[FOREGROUND] FCM 메시지 수신:', event.data);

                    const payload = event.data.payload;
                    const title = event.data.title || payload.data?.title;
                    const body = event.data.body || payload.data?.body;
                    const notificationType = payload.data?.type;

                    console.log('[FOREGROUND] 알림 타입:', notificationType);
                    console.log('[FOREGROUND] 전체 payload:', payload);

                    // 알림 뱃지를 위한 localStorage 설정 (포그라운드 상태)
                    if (notificationType) {
                        try {
                            let notifications = JSON.parse(localStorage.getItem('parentNotifications') || '{}');
                            console.log('[FOREGROUND] 기존 localStorage:', notifications);

                            notifications[notificationType] = true;
                            localStorage.setItem('parentNotifications', JSON.stringify(notifications));

                            console.log('[FOREGROUND] 업데이트된 localStorage:', notifications);

                            // 뱃지 업데이트
                            updateNavigationBadges();
                        } catch (e) {
                            console.error('[FOREGROUND] localStorage 에러:', e);
                            // localStorage 에러 발생해도 알림은 계속 표시
                        }
                    } else {
                        console.log('[FOREGROUND] 알림 타입이 없어서 localStorage 업데이트 안함');
                    }

                    // 포그라운드에서도 브라우저 알림 표시
                    if (Notification.permission === 'granted') {
                        const notification = new Notification(title, {
                            body: body,
                            icon: '/icon-parent-192x192.png',
                            badge: '/icon-parent-192x192.png',
                            tag: 'perfect-study-foreground',
                            requireInteraction: true,
                            data: {
                                type: notificationType,
                                url: getTargetUrl(notificationType)
                            }
                        });

                        // 포그라운드 알림 클릭 이벤트
                        notification.onclick = function(event) {
                            event.preventDefault();

                            const targetUrl = getTargetUrl(notificationType);

                            // 포커스 후 페이지 이동
                            window.focus();
                            if (window.location.pathname !== targetUrl) {
                                window.location.href = targetUrl;
                            }

                            notification.close();
                        };

                        // 5초 후 자동으로 알림 닫기
                        setTimeout(() => {
                            notification.close();
                        }, 5000);
                    }
                }

                // 알림 타입에 따른 URL 결정 함수
                function getTargetUrl(notificationType) {
                    if (notificationType === 'attendance') {
                        return '/parent/attendance';
                    } else if (notificationType === 'payment') {
                        return '/parent/payment';
                    }
                    return '/parent';
                }
            });
        }

        // 네비게이션 뱃지 업데이트 함수
        function updateNavigationBadges() {
            console.log('[BADGE] updateNavigationBadges 함수 실행됨');

            // 잠시 대기 후 실행 (DOM이 완전히 로드될 때까지)
            setTimeout(function() {
                try {
                    let notifications = JSON.parse(localStorage.getItem('parentNotifications') || '{}');
                    console.log('[BADGE] localStorage에서 읽은 알림 상태:', notifications);

                    // 모든 네비게이션 링크 찾기
                    const allLinks = document.querySelectorAll('nav a');
                    console.log('[BADGE] 찾은 네비게이션 링크 수:', allLinks.length);

                    allLinks.forEach(function(link, index) {
                        console.log(`[BADGE] 링크 ${index}: ${link.href}`);

                        // 기존 뱃지 제거
                        const existingBadge = link.querySelector('.notification-badge');
                        if (existingBadge) {
                            console.log(`[BADGE] 기존 뱃지 제거: ${link.href}`);
                            existingBadge.remove();
                        }

                        // 출결현황 링크인지 확인
                        if (link.href && link.href.includes('attendance') && notifications.attendance) {
                            console.log('[BADGE] 출결현황 뱃지 추가');
                            const badge = document.createElement('div');
                            badge.className = 'notification-badge';
                            badge.style.cssText = `
                                position: absolute;
                                top: -2px;
                                right: -2px;
                                width: 8px;
                                height: 8px;
                                background-color: #ef4444;
                                border-radius: 50%;
                                z-index: 9999;
                            `;

                            link.style.position = 'relative';
                            link.appendChild(badge);
                            console.log('[BADGE] 출결현황 뱃지 DOM에 추가 완료');
                        }

                        // 결제 링크인지 확인
                        if (link.href && link.href.includes('payment') && notifications.payment) {
                            console.log('[BADGE] 결제 뱃지 추가');
                            const badge = document.createElement('div');
                            badge.className = 'notification-badge';
                            badge.style.cssText = `
                                position: absolute;
                                top: -2px;
                                right: -2px;
                                width: 8px;
                                height: 8px;
                                background-color: #ef4444;
                                border-radius: 50%;
                                z-index: 9999;
                            `;

                            link.style.position = 'relative';
                            link.appendChild(badge);
                            console.log('[BADGE] 결제 뱃지 DOM에 추가 완료');
                        }
                    });
                    console.log('[BADGE] 모든 링크 처리 완료');
                } catch (e) {
                    console.error('[BADGE] 에러 발생:', e);
                }
            }, 100);
        }

        // 페이지 로드 시 뱃지 업데이트
        document.addEventListener('DOMContentLoaded', function() {
            updateNavigationBadges();
        });
    </script>
    <div class="sticky top-0 z-10">
        <livewire:parent.header />
        <livewire:parent.navigation />
    </div>
    <!-- Page Content -->
    <main class="h-full">
       {{ $slot }}
    </main>

    <!-- Livewire Scripts -->
    @livewireScripts
    <!-- Alpine.js -->
    {{-- <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}
</body>

</html>
