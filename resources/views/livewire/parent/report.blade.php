<div class="min-h-screen bg-gray-50">

  <!-- Main Content Area -->
    <main class="max-w-6xl mx-auto px-2 sm:px-6 lg:px-8 py-2 sm:py-8">
        <div class="bg-gradient-to-br from-violet-400 to-fuchsia-600 p-2 sm:p-4 rounded-lg shadow-sm mb-4">
            <h2 class="text-base sm:text-xl font-bold text-white mb-2 sm:mb-3">성적표 조회</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 sm:gap-4">
                <div>
                    <label for="date_from" class="block text-sm font-medium text-white">시작 날짜</label>
                    <select name="date_from" id="date_from"
                        wire:model.live="dateFrom"
                        class="mt-1 block w-full rounded-md border-transparent bg-white/20 text-white placeholder-white/70 shadow-sm focus:border-violet-300 focus:ring-violet-300 sm:text-sm text-sm" style="color-scheme: dark;">
                        @foreach($weekOptions as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="date_until" class="block text-sm font-medium text-white">종료 날짜</label>
                    <select name="date_until" id="date_until"
                        wire:model.live="dateUntil"
                        class="mt-1 block w-full rounded-md border-transparent bg-white/20 text-white placeholder-white/70 shadow-sm focus:border-violet-300 focus:ring-violet-300 sm:text-sm text-sm" style="color-scheme: dark;">
                        @foreach($weekOptions as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- 주간 보고서 테이블 --}}
        <div class="bg-white rounded-lg shadow-sm">
             <div class="overflow-x-auto">
                @forelse ($weeklyReports as $weekReport)
                    {{-- 주차 전체 컨테이너 --}}
                    <div class="bg-white border border-purple-200 rounded-lg shadow-sm overflow-hidden">
                        {{-- 주간 헤더 --}}
                        <div class="relative">
                            <!-- 배경 -->
                            <div class="absolute inset-0 bg-gradient-to-r from-fuchsia-100 to-violet-100"></div>
                            
                            <!-- 메인 컨테이너 -->
                            <div class="relative px-6 py-4">
                                <div class="flex items-center justify-center">
                                    <!-- 왼쪽 장식 -->
                                    <div class="w-12 h-px bg-gradient-to-r from-transparent to-fuchsia-400"></div>
                                    
                                    <!-- 주간 라벨 -->
                                    <div class="px-6 text-center">
                                        <div class="text-fuchsia-800 text-lg font-bold tracking-wide">
                                            {{ $weekReport['week_label'] }}
                                        </div>
                                        <div class="text-fuchsia-600 text-sm mt-1">주간 학습 보고서</div>
                                    </div>
                                    
                                    <!-- 오른쪽 장식 -->
                                    <div class="w-12 h-px bg-gradient-to-l from-transparent to-violet-400"></div>
                                </div>
                            </div>
                        </div>

                        {{-- 카드 내용 --}}
                        <div class="">
                            {{-- 주간 테스트 테이블 --}}
                            @if ($weekReport['test_report'])
                                <div class="mb-8">
                                    <div class="flex items-center mb-4 p-6">
                                        <div class="w-2 h-6 bg-blue-500 rounded-full mr-3"></div>
                                        <h3 class="text-lg font-bold text-gray-800">{{ $weekReport['week_label'] }} 주간테스트</h3>
                                    </div>
                                    <div class="overflow-x-auto border-t border-gray-200">
                                    <table class="w-full table-auto border-collapse border border-gray-200 rounded-lg overflow-hidden">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="px-2 py-3 text-xs font-medium text-gray-700 border border-gray-200">테스트</th>
                                            <th class="px-2 py-3 text-xs font-medium text-gray-700 border border-gray-200 whitespace-nowrap">범위</th>
                                            <th class="px-2 py-3 text-xs font-medium text-gray-700 border border-gray-200 whitespace-nowrap">개인점수</th>
                                            <th class="px-2 py-3 text-xs font-medium text-gray-700 border border-gray-200 whitespace-nowrap">반평균</th>
                                            <th class="px-2 py-3 text-xs font-medium text-gray-700 border border-gray-200 whitespace-nowrap">레벨평균</th>
                                            <th class="px-2 py-3 text-xs font-medium text-gray-700 border border-gray-200 whitespace-nowrap">반별 등수</th>
                                            <th class="px-2 py-3 text-xs font-medium text-gray-700 border border-gray-200 whitespace-nowrap">학년평균</th>
                                            <th class="px-2 py-3 text-xs font-medium text-gray-700 border border-gray-200 whitespace-nowrap">학년 등수</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($weekReport['test_report'] as $test)
                                            @foreach ($test['by_types'] as $type)
                                                <tr class="hover:bg-gray-50 transition-colors">
                                                    @if ($loop->first)
                                                        <td rowspan="{{ count($test['by_types']) + 1 }}"
                                                            class="px-2 py-3 border border-gray-200 bg-gray-50 font-medium text-xs text-gray-700">
                                                            {{ Carbon\Carbon::parse($test['date'])->format('m월d일') }}
                                                            {{ $test['name'] }}
                                                        </td>
                                                    @endif
                                                    <td class="px-2 py-3 border border-gray-200 text-xs text-gray-900 whitespace-nowrap">{!! $type['name'] !!}</td>
                                                    <td class="px-2 py-3 border border-gray-200 text-xs text-gray-900 text-center whitespace-nowrap">
                                                        {{ number_format($type['scores']['personal_score']) }} /
                                                        {{ number_format($type['scores']['total_questions'] ?? 0) }}</td>
                                                    <td class="px-2 py-3 border border-gray-200 text-xs text-gray-900 text-center whitespace-nowrap">
                                                        {{ number_format($type['scores']['classroom_average']) }}</td>
                                                    <td class="px-2 py-3 border border-gray-200 text-xs text-gray-900 text-center whitespace-nowrap">
                                                        {{ number_format($type['scores']['level_average']) }}</td>
                                                    <td class="px-2 py-3 border border-gray-200 text-xs text-gray-900 text-center whitespace-nowrap">{{ $type['scores']['classroom_rank'] }}등
                                                        ({{ $type['scores']['classroom_students_count'] ?? 0 }})
                                                    </td>
                                                    <td class="px-2 py-3 border border-gray-200 text-xs text-gray-900 text-center whitespace-nowrap">
                                                        {{ number_format($type['scores']['grade_average'] ?? 0) }}</td>
                                                    <td class="px-2 py-3 border border-gray-200 text-xs text-gray-900 text-center whitespace-nowrap">{{ $type['scores']['grade_rank'] ?? 0 }}등
                                                        ({{ $type['scores']['grade_students_count'] ?? 0 }})</td>
                                                </tr>
                                            @endforeach
                                            <tr class="bg-blue-50 font-semibold">
                                                <td class="px-2 py-3 border border-gray-200 text-xs text-gray-700">전체</td>
                                                <td class="px-2 py-3 border border-gray-200 text-xs text-gray-700 text-center whitespace-nowrap">
                                                    {{ number_format($test['total']['personal_score']) }} /
                                                    {{ number_format($test['total']['total_questions'] ?? 0) }}
                                                </td>
                                                <td class="px-2 py-3 border border-gray-200 text-xs text-gray-700 text-center whitespace-nowrap">
                                                    {{ number_format($test['total']['classroom_average']) }}</td>
                                                <td class="px-2 py-3 border border-gray-200 text-xs text-gray-700 text-center whitespace-nowrap">{{ number_format($test['total']['level_average']) }}
                                                </td>
                                                <td class="px-2 py-3 border border-gray-200 text-xs text-gray-700 text-center whitespace-nowrap">{{ $test['total']['classroom_rank'] }}등
                                                    ({{ $test['total']['classroom_students_count'] ?? 0 }})
                                                </td>
                                                <td class="px-2 py-3 border border-gray-200 text-xs text-gray-700 text-center whitespace-nowrap">
                                                    {{ number_format($test['total']['grade_average'] ?? 0) }}</td>
                                                <td class="px-2 py-3 border border-gray-200 text-xs text-gray-700 text-center whitespace-nowrap">{{ $test['total']['grade_rank'] ?? 0 }}등
                                                    ({{ $test['total']['grade_students_count'] ?? 0 }})</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                    </div>
                                </div>
                            @endif

                            {{-- 주간 숙제 테이블 --}}
                            @if ($weekReport['homework_report'])
                                <div class="mb-8">
                                    <div class="flex items-center mb-4 p-6">
                                        <div class="w-2 h-6 bg-green-500 rounded-full mr-3"></div>
                                        <h3 class="text-lg font-bold text-gray-800">{{ $weekReport['week_label'] }} 주간숙제</h3>
                                    </div>
                                    <div class="overflow-x-auto border-t border-gray-200">
                                        <table class="w-full table-auto border-collapse border border-gray-200 rounded-lg overflow-hidden">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="px-2 py-3 text-xs font-medium text-gray-700 border border-gray-200">숙제</th>
                                            <th class="px-2 py-3 text-xs font-medium text-gray-700 border border-gray-200 whitespace-nowrap">범위</th>
                                            <th class="px-2 py-3 text-xs font-medium text-gray-700 border border-gray-200 whitespace-nowrap">문제 수</th>
                                            <th class="px-2 py-3 text-xs font-medium text-gray-700 border border-gray-200 whitespace-nowrap">이행도</th>
                                            <th class="px-2 py-3 text-xs font-medium text-gray-700 border border-gray-200 whitespace-nowrap">정답률</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($weekReport['homework_report'] as $homework)
                                            @foreach ($homework['by_types'] as $type)
                                                <tr class="hover:bg-gray-50 transition-colors">
                                                    @if ($loop->first)
                                                        <td rowspan="{{ count($homework['by_types']) + 1 }}"
                                                            class="px-2 py-3 border border-gray-200 bg-gray-50 font-medium text-xs text-gray-700">
                                                            {{ Carbon\Carbon::parse($homework['date'])->format('m월d일') }}
                                                            {{ $homework['name'] }}
                                                        </td>
                                                    @endif
                                                    <td class="px-2 py-3 border border-gray-200 text-xs text-gray-900 whitespace-nowrap">{!! $type['name'] !!}</td>
                                                    <td class="px-2 py-3 border border-gray-200 text-xs text-gray-900 text-center whitespace-nowrap">
                                                        {{ $type['correct_count'] }}/{{ $type['total_count'] }}</td>
                                                    <td class="px-2 py-3 border border-gray-200 text-xs text-gray-900 text-center whitespace-nowrap">{{ number_format($type['attempt_rate'], 1) }}%</td>
                                                    <td class="px-2 py-3 border border-gray-200 text-xs text-gray-900 text-center whitespace-nowrap">{{ number_format($type['correct_rate'], 1) }}%</td>
                                                </tr>
                                            @endforeach
                                            <tr class="bg-blue-50 font-semibold">
                                                <td class="px-2 py-3 border border-gray-200 text-xs text-gray-700">전체</td>
                                                <td class="px-2 py-3 border border-gray-200 text-xs text-gray-700 text-center whitespace-nowrap">
                                                    {{ $homework['total']['correct_count'] }}/{{ $homework['total']['total_count'] }}
                                                </td>
                                                <td class="px-2 py-3 border border-gray-200 text-xs text-gray-700 text-center whitespace-nowrap">
                                                    {{ number_format($homework['total']['attempt_rate'], 1) }}%</td>
                                                <td class="px-2 py-3 border border-gray-200 text-xs text-gray-700 text-center whitespace-nowrap">
                                                    {{ number_format($homework['total']['correct_rate'], 1) }}%</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                    </div>
                                </div>
                            @endif

                            {{-- 강사 코멘트 --}}
                            <div class="mb-4 p-6">
                                <div class="flex items-center mb-4">
                                    <div class="w-2 h-6 bg-purple-500 rounded-full mr-3"></div>
                                    <h3 class="text-lg font-bold text-gray-800">{{ $weekReport['week_label'] }} 강사 코멘트</h3>
                                </div>
                                <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-2 border border-purple-200">
                                    @if ($weekReport['comment_report'])
                                        <p class="text-gray-800 text-sm leading-relaxed">{{ $weekReport['comment_report'] }}</p>
                                    @else
                                        <p class="text-gray-500 text-sm italic">강사 코멘트가 없습니다.</p>
                                    @endif
                                </div>
                            </div>

                            @if (!$weekReport['test_report'] && !$weekReport['homework_report'])
                                <div class="text-center py-8">
                                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-gray-500 text-sm">해당 기간에 검색된 주간 보고서가 없습니다.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- 주차 구분선 --}}
                    @if (!$loop->last)
                        <div class="flex items-center justify-center my-8">
                            <div class="w-32 h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent"></div>
                            <div class="w-32 h-px bg-gradient-to-l from-transparent via-gray-300 to-transparent"></div>
                        </div>
                    @endif

                    <div class="page-break"></div>
                @empty
                    <div class="text-center py-12">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-6">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-700 mb-2">보고서가 없습니다</h3>
                        <p class="text-gray-500 text-sm">선택하신 기간에 해당하는 주간 보고서가 없습니다.</p>
                    </div>
                @endforelse

            </div>
        </div>
    </main>

</div>
