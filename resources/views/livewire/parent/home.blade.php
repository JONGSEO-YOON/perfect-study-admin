<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo and Brand -->
                <div class="flex items-center ">
                    <img src="{{ asset('logo.png') }}" alt="퍼펙트 스터디" class="h-10">
             
                </div>


                <!-- User Menu -->
                <div class="flex items-center">

                    <!-- Children Select -->
                    <div class="flex items-center">
              

                        <select class="block py-2 border-0 focus:outline-none focus:ring-violet-500 focus:border-violet-500 font-semibold text-sm text-violet-600">
                            <option value="">자녀 선택</option>
                            <option value="1" selected>김철수</option>
                            <option value="2">김영희</option>
                            <option value="3">김민수</option>
                        </select>
                    </div>
                </div>

            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="bg-white border-b border-stone-100 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-around ">
                <a href="#" class="py-2 px-4 border-b-2 border-violet-500 text-violet-600 font-medium text-sm">
                    소식
                </a>
                <a href="#" class="py-2 px-4 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm">
                    출결현황
                </a>
                <a href="#" class="py-2 px-4 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm">
                    성적표
                </a>
                <a href="#" class="py-2 px-4 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm">
                    결제
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Welcome Section with Attendance -->
        <div class="bg-gradient-to-br from-violet-400 to-fuchsia-600 rounded-lg shadow-sm p-4 mb-3">
            <div class=" flex justify-between items-start">
                <div>
                    <h2 class="text-lg font-bold text-violet-900">김철수 학생</h2>
                    <p class="text-white text-sm">학부모님 반갑습니다.</p>
                </div>
            </div>
            <div class="mt-4 border-t-[1px] border-white  py-3">
                <div class="flex flex-col items-start text-white">
                    <span class="text-sm">5월 7일 수요일</span> 
                    <span class="text-lg font-semibold">오늘의 출석 기록이 없습니다.</span>
                </div>
            </div>
        </div>

        <!-- Learning Status -->
        <div class="bg-fuchsia-50 rounded-lg shadow-sm p-4 mb-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">학습현황</h3>
                <div class="flex items-center space-x-2">
                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded-full">B</span>
                    <span class="text-sm text-gray-600">5월 1주차</span>
                    <a href="#" class="text-sm text-purple-600 flex items-center">
                        성적표 상세
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>
            
            <!-- Score Table -->
            <div class="grid grid-cols-3 gap-4 mb-4 text-sm">
                <div class="text-center">
                    <p class="text-gray-600 mb-1">개인점수</p>
                    <p class="font-bold text-gray-900">12/20</p>
                </div>
                <div class="text-center">
                    <p class="text-gray-600 mb-1">반평균</p>
                    <p class="font-bold text-gray-900">10</p>
                </div>
                <div class="text-center">
                    <p class="text-gray-600 mb-1">레벨평균</p>
                    <p class="font-bold text-gray-900">10</p>
                </div>
            </div>
            
            <div class="grid grid-cols-3 gap-4 mb-4 text-sm">
                <div class="text-center">
                    <p class="text-gray-600 mb-1">반별 등수</p>
                    <p class="font-bold text-gray-900">2등 (5)</p>
                </div>
                <div class="text-center">
                    <p class="text-gray-600 mb-1">학년평균</p>
                    <p class="font-bold text-gray-900">10</p>
                </div>
                <div class="text-center">
                    <p class="text-gray-600 mb-1">학년 등수</p>
                    <p class="font-bold text-gray-900">2등 (5)</p>
                </div>
            </div>
            
            <hr class="my-4">
            
            <!-- Instructor Comment -->
            <div>
                <p class="text-sm font-medium text-gray-900 mb-2">강사 코멘트</p>
                <p class="text-sm text-gray-500">입력된 코멘트가 없습니다.</p>
            </div>
        </div>

        <!-- Announcements -->
        <div class="bg-stone-50 rounded-lg shadow-sm p-4">
            <h3 class="text-lg font-bold text-gray-900 mb-4">공지사항</h3>
            <div class="space-y-3">
                <div class="flex items-center tracking-tight">
                    <p class="text-sm text-gray-700">2025-05-28 학부모 서비스가 오픈되었습니다.</p>
                </div>
                <div class="flex items-center tracking-tight">
                    <p class="text-sm text-gray-700">2025-05-28 학부모 서비스가 오픈되었습니다.</p>
                </div>
                <div class="flex items-center tracking-tight">
                    <p class="text-sm text-gray-700">2025-05-28 학부모 서비스가 오픈되었습니다.</p>
                </div>
            </div>
        </div>
    </main>
</div>
