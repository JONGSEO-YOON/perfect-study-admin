<div class="min-h-screen flex flex-col">
    <!-- Main Content Area -->
    <main class="flex-1 max-w-6xl mx-auto px-2 sm:px-4 lg:px-6 py-2 sm:py-4 w-full">
        <!-- Filter Section -->
        <div class="bg-fuchsia-50 rounded-lg shadow-sm p-3 sm:p-4 mb-4">
            <h2 class="text-lg sm:text-xl font-bold text-stone-900 mb-3">성적표 조회</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                <div>
                    <label for="date_from" class="block text-sm font-medium text-fuchsia-600">시작 날짜</label>
                    <select name="date_from" id="date_from"
                        wire:model.live="dateFrom"
                        class="mt-1 block w-full rounded-md border-transparent bg-fuchsia-500 text-white shadow-sm focus:border-fuchsia-300 focus:ring-fuchsia-300 sm:text-sm text-sm">
                        @foreach($weekOptions as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="date_until" class="block text-sm font-medium text-fuchsia-600">종료 날짜</label>
                    <select name="date_until" id="date_until"
                        wire:model.live="dateUntil"
                        class="mt-1 block w-full rounded-md border-transparent bg-fuchsia-500 text-white shadow-sm focus:border-fuchsia-300 focus:ring-fuchsia-300 sm:text-sm text-sm">
                        @foreach($weekOptions as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Weekly Reports Summary -->
        <div class="space-y-4">
            @forelse ($weeklyReports as $weekReport)
                <div class="bg-fuchsia-50 rounded-lg shadow-sm p-3 sm:p-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-stone-900">학습현황</h3>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-stone-600">{{ $weekReport['week_label'] }}</span>
                            @if ($weekReport['test_report'] || $weekReport['homework_report'])
                                <a href="{{ route('parent.report-detail', ['weekKey' => urlencode($weekReport['week_key'])]) }}" class="text-sm text-fuchsia-600 flex items-center"
                                     wire:navigate>
                                    상세보기
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>

                    @if ($weekReport['test_report'])
                        <!-- 주간테스트 요약 -->
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-stone-700 mb-2">주간테스트</h4>
                            @foreach ($weekReport['test_report'] as $test)
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

                    @if ($weekReport['homework_report'])
                        <!-- 주간숙제 요약 -->
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-stone-700 mb-2">주간숙제</h4>
                            @foreach ($weekReport['homework_report'] as $homework)
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

                    @if ($weekReport['comment_report'])
                        <!-- 강사 코멘트 -->
                        <div class="border-t pt-4 border-fuchsia-600">
                            <h4 class="text-sm font-medium text-stone-700 mb-2">강사 코멘트</h4>
                            <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-2 border border-purple-200">
                                <p class="text-stone-800 text-sm leading-relaxed">{{ $weekReport['comment_report'] }}</p>
                            </div>
                        </div>
                    @endif

                    @if (!$weekReport['test_report'] && !$weekReport['homework_report'])
                        <div class="text-center py-8">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-fuchsia-100 rounded-full mb-4">
                                <svg class="w-8 h-8 text-fuchsia-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <p class="text-stone-500 text-sm">해당 기간에 검색된 주간 보고서가 없습니다.</p>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-fuchsia-50 rounded-lg shadow-sm p-3 sm:p-4">
                    <div class="flex flex-col items-center justify-center py-8 sm:py-12">
                        <div class="mt-4 sm:mt-6 text-center">
                            <h4 class="text-lg sm:text-xl font-bold text-fuchsia-600">
                                이번 주차 성적표 없음
                            </h4>
                            <p class="text-sm text-stone-500 mt-2">선택하신 기간에 해당하는 주간 보고서가 없습니다.</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </main>
</div>
