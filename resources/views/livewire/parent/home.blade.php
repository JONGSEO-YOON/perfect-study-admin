<div class="min-h-screen bg-gray-50">

    <!-- Main Content Area -->
    <main class="max-w-6xl mx-auto px-2 sm:px-4 lg:px-6 py-2 sm:py-4">
        <!-- Welcome Section with Attendance -->
        <div class="bg-gradient-to-br from-violet-400 to-fuchsia-600 rounded-lg shadow-sm p-3 sm:p-4 mb-4">
            <div class=" flex justify-between items-start">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-violet-900">{{ $student?->user?->name }} 학생</h2>
                    <p class="text-white text-sm sm:text-base">학부모님 반갑습니다.</p>
                </div>
            </div>
            <div class="mt-3 sm:mt-4 border-t-[1px] border-white py-2 sm:py-3">
                <div class="flex flex-col items-start text-white">
                    <span class="text-sm sm:text-base font-bold">5월 7일 수요일</span> 

                    @if (count($attendances) > 0)
                        @foreach ($attendances as $attendance)
                            <span class="text-sm sm:text-base text-white">{{ $attendance['title'] }} - {{ $attendance['time'] }}</span>
                        @endforeach
                    @else
                        <span class="text-lg sm:text-xl font-semibold">오늘의 출석 기록이 없습니다.</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Learning Status -->
        <div class="bg-fuchsia-50 rounded-lg shadow-sm p-3 sm:p-4 mb-4">
            <div class="flex flex-col items-center justify-center py-8 sm:py-12">
           
                
                <!-- Text with gradient -->
                <div class="mt-4 sm:mt-6 text-center">
                    <h4 class="text-lg sm:text-xl font-bold bg-gradient-to-r from-fuchsia-600 to-violet-600 bg-clip-text text-transparent">
                        성적표 준비중...
                    </h4>
                </div>
                
              
            </div>
            {{-- <div class="flex justify-between items-center mb-4">
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
            </div> --}}
        </div>
        @if (count($notices) > 0)
        <!-- Announcements -->
        <div class="bg-stone-100 rounded-lg shadow-sm p-3 sm:p-4">
            <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-3 sm:mb-4">공지사항</h3>
            <div class="space-y-2 sm:space-y-3">
                @foreach ($notices as $notice)
                    <div class="flex items-center tracking-tight">
                        <a href="{{ route('parent.notice', $notice->id) }}" class="text-sm sm:text-base text-gray-700"> {{ $notice->created_at->format('Y-m-d') }} {{ $notice->title }}</a>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </main>
</div>
