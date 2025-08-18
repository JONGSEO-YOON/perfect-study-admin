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
        <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 mb-4">
             <div class="overflow-x-auto">
                @forelse ($weeklyReports as $weekReport)
                    {{-- 주간 헤더 --}}
                    <div class="mb-6">
                        <div class="relative">
                            <!-- 배경 -->
                            <div class="absolute inset-0 bg-fuchsia-50 rounded-lg shadow-sm"></div>
                            
                            <!-- 메인 컨테이너 -->
                            <div class="relative px-6 py-2">
                                <div class="flex items-center justify-center">
                                    <!-- 왼쪽 장식 -->
                                    <div class="w-8 h-px bg-slate-300"></div>
                                    
                                    <!-- 주간 라벨 -->
                                    <div class="px-4 text-center">
                                        <div class="text-slate-700 text-lg sm:text-xl font-semibold tracking-wide">
                                            {{ $weekReport['week_label'] }}
                                        </div>
                                    </div>
                                    
                                    <!-- 오른쪽 장식 -->
                                    <div class="w-8 h-px bg-slate-300"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 주간 테스트 테이블 --}}
                    @if ($weekReport['test_report'])
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3 px-2">{{ $weekReport['week_label'] }} 주간테스트</h3>
                            <div class="overflow-x-auto">
                                <table class="w-full border-collapse border border-gray-200 rounded-lg overflow-hidden">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="px-4 py-3 text-sm font-medium text-gray-700 border border-gray-200">테스트</th>
                                            <th class="px-4 py-3 text-sm font-medium text-gray-700 border border-gray-200">범위</th>
                                            <th class="px-4 py-3 text-sm font-medium text-gray-700 border border-gray-200">개인점수</th>
                                            <th class="px-4 py-3 text-sm font-medium text-gray-700 border border-gray-200">반평균</th>
                                            <th class="px-4 py-3 text-sm font-medium text-gray-700 border border-gray-200">레벨평균</th>
                                            <th class="px-4 py-3 text-sm font-medium text-gray-700 border border-gray-200">반별 등수</th>
                                            <th class="px-4 py-3 text-sm font-medium text-gray-700 border border-gray-200">학년평균</th>
                                            <th class="px-4 py-3 text-sm font-medium text-gray-700 border border-gray-200">학년 등수</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($weekReport['test_report'] as $test)
                                            @foreach ($test['by_types'] as $type)
                                                <tr class="hover:bg-gray-50 transition-colors">
                                                    @if ($loop->first)
                                                        <td rowspan="{{ count($test['by_types']) + 1 }}"
                                                            class="px-4 py-3 border border-gray-200 bg-gray-50 font-medium text-sm text-gray-700">
                                                            {{ Carbon\Carbon::parse($test['date'])->format('m월d일') }}
                                                            {{ $test['name'] }}
                                                        </td>
                                                    @endif
                                                    <td class="px-4 py-3 border border-gray-200 text-sm text-gray-900">{!! $type['name'] !!}</td>
                                                    <td class="px-4 py-3 border border-gray-200 text-sm text-gray-900 text-center">
                                                        {{ number_format($type['scores']['personal_score']) }} /
                                                        {{ number_format($type['scores']['total_questions'] ?? 0) }}</td>
                                                    <td class="px-4 py-3 border border-gray-200 text-sm text-gray-900 text-center">
                                                        {{ number_format($type['scores']['classroom_average']) }}</td>
                                                    <td class="px-4 py-3 border border-gray-200 text-sm text-gray-900 text-center">
                                                        {{ number_format($type['scores']['level_average']) }}</td>
                                                    <td class="px-4 py-3 border border-gray-200 text-sm text-gray-900 text-center">{{ $type['scores']['classroom_rank'] }}등
                                                        ({{ $type['scores']['classroom_students_count'] ?? 0 }})
                                                    </td>
                                                    <td class="px-4 py-3 border border-gray-200 text-sm text-gray-900 text-center">
                                                        {{ number_format($type['scores']['grade_average'] ?? 0) }}</td>
                                                    <td class="px-4 py-3 border border-gray-200 text-sm text-gray-900 text-center">{{ $type['scores']['grade_rank'] ?? 0 }}등
                                                        ({{ $type['scores']['grade_students_count'] ?? 0 }})</td>
                                                </tr>
                                            @endforeach
                                            <tr class="bg-blue-50 font-semibold">
                                                <td class="px-4 py-3 border border-gray-200 text-sm text-gray-700">전체</td>
                                                <td class="px-4 py-3 border border-gray-200 text-sm text-gray-700 text-center">
                                                    {{ number_format($test['total']['personal_score']) }} /
                                                    {{ number_format($test['total']['total_questions'] ?? 0) }}
                                                </td>
                                                <td class="px-4 py-3 border border-gray-200 text-sm text-gray-700 text-center">
                                                    {{ number_format($test['total']['classroom_average']) }}</td>
                                                <td class="px-4 py-3 border border-gray-200 text-sm text-gray-700 text-center">{{ number_format($test['total']['level_average']) }}
                                                </td>
                                                <td class="px-4 py-3 border border-gray-200 text-sm text-gray-700 text-center">{{ $test['total']['classroom_rank'] }}등
                                                    ({{ $test['total']['classroom_students_count'] ?? 0 }})
                                                </td>
                                                <td class="px-4 py-3 border border-gray-200 text-sm text-gray-700 text-center">
                                                    {{ number_format($test['total']['grade_average'] ?? 0) }}</td>
                                                <td class="px-4 py-3 border border-gray-200 text-sm text-gray-700 text-center">{{ $test['total']['grade_rank'] ?? 0 }}등
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
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3 px-2">{{ $weekReport['week_label'] }} 주간숙제</h3>
                            <div class="overflow-x-auto">
                                <table class="w-full border-collapse border border-gray-200 rounded-lg overflow-hidden">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="px-4 py-3 text-sm font-medium text-gray-700 border border-gray-200">숙제</th>
                                            <th class="px-4 py-3 text-sm font-medium text-gray-700 border border-gray-200">범위</th>
                                            <th class="px-4 py-3 text-sm font-medium text-gray-700 border border-gray-200">문제 수</th>
                                            <th class="px-4 py-3 text-sm font-medium text-gray-700 border border-gray-200">이행도</th>
                                            <th class="px-4 py-3 text-sm font-medium text-gray-700 border border-gray-200">정답률</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($weekReport['homework_report'] as $homework)
                                            @foreach ($homework['by_types'] as $type)
                                                <tr class="hover:bg-gray-50 transition-colors">
                                                    @if ($loop->first)
                                                        <td rowspan="{{ count($homework['by_types']) + 1 }}"
                                                            class="px-4 py-3 border border-gray-200 bg-gray-50 font-medium text-sm text-gray-700">
                                                            {{ Carbon\Carbon::parse($homework['date'])->format('m월d일') }}
                                                            {{ $homework['name'] }}
                                                        </td>
                                                    @endif
                                                    <td class="px-4 py-3 border border-gray-200 text-sm text-gray-900">{!! $type['name'] !!}</td>
                                                    <td class="px-4 py-3 border border-gray-200 text-sm text-gray-900 text-center">
                                                        {{ $type['correct_count'] }}/{{ $type['total_count'] }}</td>
                                                    <td class="px-4 py-3 border border-gray-200 text-sm text-gray-900 text-center">{{ number_format($type['attempt_rate'], 1) }}%</td>
                                                    <td class="px-4 py-3 border border-gray-200 text-sm text-gray-900 text-center">{{ number_format($type['correct_rate'], 1) }}%</td>
                                                </tr>
                                            @endforeach
                                            <tr class="bg-blue-50 font-semibold">
                                                <td class="px-4 py-3 border border-gray-200 text-sm text-gray-700">전체</td>
                                                <td class="px-4 py-3 border border-gray-200 text-sm text-gray-700 text-center">
                                                    {{ $homework['total']['correct_count'] }}/{{ $homework['total']['total_count'] }}
                                                </td>
                                                <td class="px-4 py-3 border border-gray-200 text-sm text-gray-700 text-center">
                                                    {{ number_format($homework['total']['attempt_rate'], 1) }}%</td>
                                                <td class="px-4 py-3 border border-gray-200 text-sm text-gray-700 text-center">
                                                    {{ number_format($homework['total']['correct_rate'], 1) }}%</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- 강사 코멘트 --}}
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3 px-2">{{ $weekReport['week_label'] }} 강사 코멘트</h3>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
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
