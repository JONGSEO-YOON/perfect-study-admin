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

                // FCM 메시지 처리 (포그라운드 상태에서 받은 알림)
                if (event.data && event.data.type === 'FCM_MESSAGE') {
                    const payload = event.data.payload;
                    const title = event.data.title || payload.data?.title;
                    const body = event.data.body || payload.data?.body;
                    const notificationType = payload.data?.type;

                    // 알림 뱃지를 위한 localStorage 설정 (포그라운드 상태)
                    if (notificationType) {
                        try {
                            let notifications = JSON.parse(localStorage.getItem('parentNotifications') || '{}');
                            notifications[notificationType] = true;
                            localStorage.setItem('parentNotifications', JSON.stringify(notifications));

                            // 뱃지 업데이트
                            updateNavigationBadges();
                        } catch (e) {
                            // localStorage 에러 발생해도 알림은 계속 표시
                        }
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
            try {
                let notifications = JSON.parse(localStorage.getItem('parentNotifications') || '{}');

                // 출결현황 뱃지 처리
                const attendanceLink = document.querySelector('a[href*="attendance"]');
                if (attendanceLink) {
                    // 기존 뱃지 제거
                    const existingBadge = attendanceLink.querySelector('.notification-badge');
                    if (existingBadge) {
                        existingBadge.remove();
                    }

                    // 새 알림이 있으면 뱃지 추가
                    if (notifications.attendance) {
                        const badge = document.createElement('span');
                        badge.className = 'notification-badge';
                        badge.style.cssText = 'position: absolute; top: -4px; right: -4px; width: 12px; height: 12px; background-color: #ef4444; border-radius: 50%; border: 2px solid white;';

                        attendanceLink.style.position = 'relative';
                        attendanceLink.appendChild(badge);
                    }
                }

                // 결제 뱃지 처리
                const paymentLink = document.querySelector('a[href*="payment"]');
                if (paymentLink) {
                    // 기존 뱃지 제거
                    const existingBadge = paymentLink.querySelector('.notification-badge');
                    if (existingBadge) {
                        existingBadge.remove();
                    }

                    // 새 알림이 있으면 뱃지 추가
                    if (notifications.payment) {
                        const badge = document.createElement('span');
                        badge.className = 'notification-badge';
                        badge.style.cssText = 'position: absolute; top: -4px; right: -4px; width: 12px; height: 12px; background-color: #ef4444; border-radius: 50%; border: 2px solid white;';

                        paymentLink.style.position = 'relative';
                        paymentLink.appendChild(badge);
                    }
                }
            } catch (e) {
                // 뱃지 업데이트 실패해도 무시
            }
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
