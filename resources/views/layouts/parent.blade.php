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
