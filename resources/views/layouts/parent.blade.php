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
                console.log('=== 포그라운드에서 Service Worker 메시지 받음 ===');
                console.log('Event data:', event.data);

                if (event.data && event.data.action === 'navigate') {
                    const targetUrl = event.data.url;
                    const notificationType = event.data.type;

                    console.log('알림 타입:', notificationType);
                    console.log('이동할 URL:', targetUrl);
                    console.log('현재 URL:', window.location.pathname);

                    // 현재 페이지가 목표 페이지와 다르면 이동
                    if (window.location.pathname !== targetUrl) {
                        console.log('페이지 이동 실행:', targetUrl);
                        window.location.href = targetUrl;
                    } else {
                        console.log('이미 목표 페이지에 있음');
                    }
                }

                // FCM 메시지 처리 (포그라운드 상태에서 받은 알림)
                if (event.data && event.data.type === 'FCM_MESSAGE') {
                    const payload = event.data.payload;
                    const title = event.data.title || payload.data?.title;
                    const body = event.data.body || payload.data?.body;
                    const notificationType = payload.data?.type;

                    console.log('포그라운드 FCM 메시지:', title, body);

                    // 포그라운드에서도 브라우저 알림 표시
                    if (Notification.permission === 'granted') {
                        console.log('포그라운드 상태 - 브라우저 알림 표시');

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
                            console.log('포그라운드 알림 클릭됨');
                            event.preventDefault();

                            const targetUrl = getTargetUrl(notificationType);
                            console.log('이동할 URL:', targetUrl);

                            // 포커스 후 페이지 이동
                            window.focus();
                            if (window.location.pathname !== targetUrl) {
                                window.location.href = targetUrl;
                            }

                            notification.close();
                        };

                        // 3초 후 자동으로 알림 닫기 (선택사항)
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
