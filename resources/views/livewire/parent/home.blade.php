<div class="min-h-screen flex flex-col">

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
            @if ($weeklyReport['test_report'] || $weeklyReport['homework_report'] || $weeklyReport['comment_report'])
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-stone-900">학습현황</h3>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-stone-600">{{ $weeklyReport['week_label'] }}</span>
                        <a href="{{ route('parent.report') }}" class="text-sm text-fuchsia-600 flex items-center">
                            성적표 상세
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>

                @if ($weeklyReport['test_report'])
                    <!-- 주간테스트 요약 -->
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-stone-700 mb-2">주간테스트</h4>
                        @foreach ($weeklyReport['test_report'] as $test)
                            <div class="mb-3 p-3 bg-fuchsia-100 rounded-lg">
                                <div class="text-xs font-medium text-fuchsia-800 mb-2">{{ $test['name'] }}</div>
                                <div class="grid grid-cols-3 gap-4 text-sm">
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
                                        <div class="text-center">
                                            <p class="text-stone-600 mb-1">반별 등수</p>
                                            <p class="font-bold text-stone-900">{{ $total['classroom_rank'] }}등 ({{ $total['classroom_students_count'] ?? 0 }})</p>
                                        </div>
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
        <div class="max-w-6xl mx-auto px-2 sm:px-4 lg:px-6 pb-6 mt-16 w-full">
            <button wire:click="logout" class="w-full py-3 text-center hover:bg-stone-100 border border-violet-500 rounded-lg text-violet-500 font-semibold">로그아웃</button>
        </div>
    </main>
</div>

@script
<script>
    // 로그아웃 시 로컬스토리지 정리
    $wire.on('clear-local-storage', () => {
        try {
            localStorage.removeItem('parent_phone');
        } catch (e) {
            // 무시
        }
    });
</script>
@endscript
