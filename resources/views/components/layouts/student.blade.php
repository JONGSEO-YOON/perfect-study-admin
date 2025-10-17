<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    @stack('scripts')

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Livewire Styles -->
    @livewireStyles
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 토글 버튼과 사이드바 요소 선택
            const toggleButtons = document.querySelectorAll('.menu-button');
            const sidebar = document.querySelector('aside');

            // 초기 상태 설정 (모바일에서는 숨김)
            if (window.innerWidth < 1024) {
                sidebar.classList.add('hidden');
            }

            // 버튼 클릭 이벤트 처리
            for (const toggleButton of toggleButtons) {
                toggleButton.addEventListener('click', function() {
                    sidebar.classList.toggle('hidden');
                });
            }

            // 화면 크기 변경 시 처리
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 1024) {
                    sidebar.classList.remove('hidden');
                } else {
                    sidebar.classList.add('hidden');
                }
            });
        });
    </script>
</head>

<body>
    <!-- Page Content -->
    <main class="h-full">
        <div class="h-full w-full flex flex-row">
            <aside
                class="w-[280px] xl:w-[320px] h-full border-r px-5 flex-col py-4 lg:flex
           fixed top-0 left-0 right-0 bottom-0  bg-white
           lg:relative 
           hidden
           z-50
            ">
                <div class="flex flex-row items-center justify-between">
                    <img src="/logo.png" class="w-2/3" />
                    <button class="lg:hidden menu-button">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-6">
                            <path
                                d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                        </svg>
                    </button>
                </div>
                <div class="flex flex-col mt-8 gap-y-0.5 flex-1">
                    <a href="/"
                        class="flex items-center gap-x-5 font-semibold p-4 transition-all hover:bg-[#F6F8FF] rounded-lg {{ request()->is('/') ? 'bg-[#F6F8FF] text-[#8570C2]' : '' }}">
                        <svg width="16" height="18" viewBox="0 0 16 18" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M0 18V6L8 0L16 6V18H10V11H6V18H0Z" fill="currentColor" />
                        </svg>
                        홈
                    </a>
                    <a href="/lectures"
                        class="flex items-center gap-x-5 font-semibold p-4 transition-all hover:bg-[#F6F8FF] rounded-lg {{ request()->is('lectures*') ? 'bg-[#F6F8FF] text-[#8570C2]' : '' }}">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M18.0031 12.8229H10.3844C11.2489 12.7486 12.0504 12.356 12.6226 11.7267C13.1947 11.0973 13.4934 10.2796 13.4566 9.4434C13.4198 8.6072 13.0503 7.81682 12.4249 7.23649C11.7996 6.65615 10.9664 6.33048 10.0985 6.32714H7.60981C7.98188 5.84467 8.248 5.29409 8.3919 4.70906C8.53579 4.12403 8.55444 3.51684 8.44671 2.92462C8.33897 2.3324 8.10711 1.76759 7.76531 1.26473C7.4235 0.761867 6.97893 0.33152 6.45878 0L18.0031 0C18.533 0.000757029 19.0409 0.204279 19.4153 0.565875C19.7897 0.92747 20 1.41758 20 1.92857V10.8986C20 11.9614 19.1053 12.8243 18.0031 12.8243V12.8229ZM3.92415 6.33C4.28892 6.33818 4.6517 6.27597 4.99117 6.14701C5.33065 6.01805 5.63996 5.82495 5.90094 5.57906C6.16192 5.33317 6.3693 5.03945 6.51089 4.71517C6.65247 4.39088 6.72541 4.04257 6.72541 3.69071C6.72541 3.33886 6.65247 2.99055 6.51089 2.66626C6.3693 2.34198 6.16192 2.04826 5.90094 1.80237C5.63996 1.55648 5.33065 1.36338 4.99117 1.23442C4.6517 1.10546 4.28892 1.04324 3.92415 1.05143C3.20932 1.06747 2.52937 1.35258 2.02969 1.84579C1.53001 2.33901 1.25026 3.00118 1.25026 3.69071C1.25026 4.38025 1.53001 5.04242 2.02969 5.53564C2.52937 6.02885 3.20932 6.31396 3.92415 6.33ZM11.6199 9.58143C11.6199 8.77 10.9384 8.11286 10.0985 8.11286H3.92563C2.88449 8.11286 1.88599 8.51171 1.14979 9.22167C0.413592 9.93163 0 10.8945 0 11.8986V13.62C0 14.38 0.639953 14.9957 1.42804 14.9957H1.68284L2.10059 18.7243C2.13965 19.0744 2.31141 19.3982 2.5829 19.6336C2.85438 19.8691 3.20647 19.9996 3.57159 20H4.31524C4.6726 19.9999 5.01785 19.8751 5.28737 19.6488C5.55688 19.4225 5.73248 19.1099 5.78179 18.7686L6.89727 11.0486H10.097C10.937 11.0486 11.6184 10.3914 11.6184 9.58143H11.6199Z"
                                fill="currentColor" />
                        </svg>
                        강의 시청
                    </a>
                    <a href="/report-card"
                        class="flex items-center gap-x-5 font-semibold p-4 transition-all hover:bg-[#F6F8FF] rounded-lg {{ request()->is('report-card*') ? 'bg-[#F6F8FF] text-[#8570C2]' : '' }}">
                        <svg width="18" height="20" viewBox="0 0 18 20" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M0 9C0 5.25 -5.96046e-08 3.375 0.955 2.061C1.26306 1.63667 1.63595 1.26344 2.06 0.955C3.375 -5.96046e-08 5.251 0 9 0C12.749 0 14.625 -5.96046e-08 15.939 0.955C16.3634 1.26336 16.7366 1.6366 17.045 2.061C18 3.375 18 5.251 18 9V11C18 14.75 18 16.625 17.045 17.939C16.7366 18.3634 16.3634 18.7366 15.939 19.045C14.625 20 12.749 20 9 20C5.251 20 3.375 20 2.061 19.045C1.6366 18.7366 1.26336 18.3634 0.955 17.939C-5.96046e-08 16.625 0 14.749 0 11V9Z"
                                fill="currentColor" />
                            <path
                                d="M12 11L11.143 9.00004M11.143 9.00004L9.592 5.38204C9.493 5.15004 9.26 5.00004 9 5.00004C8.8746 4.99861 8.75155 5.03406 8.64612 5.10197C8.5407 5.16988 8.45755 5.26727 8.407 5.38204L6.857 9.00004M11.143 9.00004H6.857M6 11L6.857 9.00004M5 15H13"
                                stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        성적표
                    </a>

                    <a href="/notices"
                        class="flex items-center gap-x-5 font-semibold p-4 transition-all hover:bg-[#F6F8FF] rounded-lg {{ request()->is('notices*') ? 'bg-[#F6F8FF] text-[#8570C2]' : '' }}">
                        <svg width="20" height="17" viewBox="0 0 20 17" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M17 5V11C18.7 11 20 9.7 20 8C20 6.3 18.7 5 17 5ZM9 4H2C0.9 4 0 4.9 0 6V10C0 11.1 0.9 12 2 12H3V15C3 16.1 3.9 17 5 17H7V12H9L13 16H15V0H13L9 4Z"
                                fill="currentColor" />
                        </svg>
                        공지사항
                    </a>
                    <a href="/password-change"
                        class="flex items-center gap-x-5 font-semibold p-4 transition-all hover:bg-[#F6F8FF] rounded-lg {{ request()->is('password-change*') ? 'bg-[#F6F8FF] text-[#8570C2]' : '' }}">

                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                            <path fill-rule="evenodd"
                                d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z"
                                clip-rule="evenodd" />
                        </svg>

                        비밀번호 변경
                    </a>
                    <div class="flex-1"></div>

                    <a href="/logout"
                        class="flex  mt-10 items-center gap-x-5 font-semibold p-4  transition-all hover:bg-[#F6F8FF] rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                            <path fill-rule="evenodd"
                                d="M3 4.25A2.25 2.25 0 0 1 5.25 2h5.5A2.25 2.25 0 0 1 13 4.25v2a.75.75 0 0 1-1.5 0v-2a.75.75 0 0 0-.75-.75h-5.5a.75.75 0 0 0-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 0 0 .75-.75v-2a.75.75 0 0 1 1.5 0v2A2.25 2.25 0 0 1 10.75 18h-5.5A2.25 2.25 0 0 1 3 15.75V4.25Z"
                                clip-rule="evenodd" />
                            <path fill-rule="evenodd"
                                d="M19 10a.75.75 0 0 0-.75-.75H8.704l1.048-.943a.75.75 0 1 0-1.004-1.114l-2.5 2.25a.75.75 0 0 0 0 1.114l2.5 2.25a.75.75 0 1 0 1.004-1.114l-1.048-.943h9.546A.75.75 0 0 0 19 10Z"
                                clip-rule="evenodd" />
                        </svg>

                        로그아웃
                    </a>
                </div>
            </aside>
            <div class="flex-1 flex flex-col">
                <div class="px-5 items-center flex-row py-4 border-b flex lg:hidden">
                    <a href="#" class="menu-button">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 17L13 17L5 17ZM5 12L19 12L5 12ZM5 7L13 7L5 7Z" fill="#383838" />
                            <path d="M5 17L13 17M5 12L19 12M5 7L13 7" stroke="#1E1E1E" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </a>
                    <img src="/logo.png" class="h-[40px] ml-4" />
                </div>
                <div class="flex-1 flex flex-col h-0">
                    {{ $slot }}
                </div>
            </div>
        </div>
        {{-- 푸터 --}}
        <footer class="w-full border-t px-5 py-6" style="background-color: #f3f4f6;">
            <div class="flex flex-col md:flex-row gap-6 md:gap-16">
                <div class="flex-shrink-0">
                    <img src="/logo.png" class="w-32 md:w-40" alt="퍼펙트스터디" />
                </div>
                <div class="text-sm text-gray-600 flex-1">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-1">
                        <div class="space-y-1">
                            <p><span class="font-semibold">상호:</span> 퍼펙트스터디</p>
                            <p><span class="font-semibold">대표자명:</span> 류성문</p>
                            <p><span class="font-semibold">사업자등록번호:</span> 598-06-02832</p>
                        </div>
                        <div class="space-y-1">
                            <p><span class="font-semibold">사업장주소:</span> 서울시 양천구 신목로 12길 22, 104동 504호(롯데캐슬)</p>
                            <p><span class="font-semibold">유선전화번호:</span> 010-9313-1786</p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </main>

    <!-- Livewire Scripts -->
    @livewireScripts

    <!-- Alpine.js -->
    {{-- <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}
</body>

</html>
