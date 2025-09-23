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

                    // 알림 뱃지를 위한 localStorage 설정
                    if (notificationType) {
                        let notifications = {};
                        try {
                            notifications = JSON.parse(localStorage.getItem('parentNotifications') || '{}');
                        } catch (e) {
                            notifications = {};
                        }

                        // 해당 타입에 새 알림 추가
                        notifications[notificationType] = true;
                        localStorage.setItem('parentNotifications', JSON.stringify(notifications));

                        // 네비게이션 뱃지 업데이트
                        updateNavigationBadges();
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
            let notifications = {};
            try {
                notifications = JSON.parse(localStorage.getItem('parentNotifications') || '{}');
            } catch (e) {
                notifications = {};
            }

            // 각 네비게이션 링크에 뱃지 추가/제거
            const navLinks = {
                'attendance': document.querySelector('a[href*="attendance"]'),
                'payment': document.querySelector('a[href*="payment"]')
            };

            Object.keys(navLinks).forEach(type => {
                const link = navLinks[type];
                if (!link) return;

                // 기존 뱃지 제거
                const existingBadge = link.querySelector('.notification-badge');
                if (existingBadge) {
                    existingBadge.remove();
                }

                // 새 알림이 있으면 뱃지 추가
                if (notifications[type]) {
                    const badge = document.createElement('span');
                    badge.className = 'notification-badge absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full';
                    badge.style.cssText = 'position: absolute; top: -4px; right: -4px; width: 12px; height: 12px; background-color: #ef4444; border-radius: 50%; border: 2px solid white;';

                    // 링크를 relative position으로 설정
                    link.style.position = 'relative';
                    link.appendChild(badge);
                }
            });
        }

        // 페이지 로드 시 뱃지 업데이트
        document.addEventListener('DOMContentLoaded', function() {
            updateNavigationBadges();
        });

        // 페이지 방문 시 해당 알림 제거
        function clearNotificationForCurrentPage() {
            const currentPath = window.location.pathname;
            let notificationType = null;

            if (currentPath.includes('/parent/attendance')) {
                notificationType = 'attendance';
            } else if (currentPath.includes('/parent/payment')) {
                notificationType = 'payment';
            }

            if (notificationType) {
                let notifications = {};
                try {
                    notifications = JSON.parse(localStorage.getItem('parentNotifications') || '{}');
                } catch (e) {
                    notifications = {};
                }

                // 해당 타입 알림 제거
                delete notifications[notificationType];
                localStorage.setItem('parentNotifications', JSON.stringify(notifications));

                // 뱃지 업데이트
                updateNavigationBadges();
            }
        }

        // 페이지 로드 시 현재 페이지 알림 제거
        clearNotificationForCurrentPage();

        // Livewire 네비게이션 시에도 알림 제거
        document.addEventListener('livewire:navigated', function() {
            clearNotificationForCurrentPage();
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
