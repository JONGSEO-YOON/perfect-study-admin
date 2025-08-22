@if ($weeklyReport)
        <div >
            <!-- 주간 테스트 테이블 -->
            @if ($weeklyReport['test_report'])
                <div class="mb-8">
                    <div class="flex items-center mb-4 p-6">
                        <div class="w-2 h-6 bg-blue-500 rounded-full mr-3"></div>
                        <h3 class="text-lg font-bold text-stone-800">{{ $weeklyReport['week_label'] }} 주간테스트</h3>
                    </div>
                    <div class="overflow-x-auto border-t border-stone-200">
                        <table class="w-full table-auto border-collapse border border-stone-200 rounded-lg overflow-hidden">
                            <thead>
                                <tr class="bg-stone-50">
                                    <th class="px-2 py-3 text-xs font-medium text-stone-700 border border-stone-200">테스트</th>
                                    <th class="px-2 py-3 text-xs font-medium text-stone-700 border border-stone-200 whitespace-nowrap">범위</th>
                                    <th class="px-2 py-3 text-xs font-medium text-stone-700 border border-stone-200 whitespace-nowrap">개인점수</th>
                                    <th class="px-2 py-3 text-xs font-medium text-stone-700 border border-stone-200 whitespace-nowrap">반평균</th>
                                    <th class="px-2 py-3 text-xs font-medium text-stone-700 border border-stone-200 whitespace-nowrap">레벨평균</th>
                                    <th class="px-2 py-3 text-xs font-medium text-stone-700 border border-stone-200 whitespace-nowrap">반별 등수</th>
                                    <th class="px-2 py-3 text-xs font-medium text-stone-700 border border-stone-200 whitespace-nowrap">학년평균</th>
                                    <th class="px-2 py-3 text-xs font-medium text-stone-700 border border-stone-200 whitespace-nowrap">학년 등수</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($weeklyReport['test_report'] as $test)
                                    @foreach ($test['by_types'] as $type)
                                        <tr class="hover:bg-stone-50 transition-colors">
                                            @if ($loop->first)
                                                <td rowspan="{{ count($test['by_types']) + 1 }}"
                                                    class="px-2 py-3 border border-stone-200 bg-stone-50 font-medium text-xs text-stone-700">
                                                    {{ Carbon\Carbon::parse($test['date'])->format('m월d일') }}
                                                    {{ $test['name'] }}
                                                </td>
                                            @endif
                                            <td class="px-2 py-3 border border-stone-200 text-xs text-stone-900 whitespace-nowrap">{!! $type['name'] !!}</td>
                                            <td class="px-2 py-3 border border-stone-200 text-xs text-stone-900 text-center whitespace-nowrap">
                                                {{ number_format($type['scores']['personal_score']) }} /
                                                {{ number_format($type['scores']['total_questions'] ?? 0) }}</td>
                                            <td class="px-2 py-3 border border-stone-200 text-xs text-stone-900 text-center whitespace-nowrap">
                                                {{ number_format($type['scores']['classroom_average']) }}</td>
                                            <td class="px-2 py-3 border border-stone-200 text-xs text-stone-900 text-center whitespace-nowrap">
                                                {{ number_format($type['scores']['level_average']) }}</td>
                                            <td class="px-2 py-3 border border-stone-200 text-xs text-stone-900 text-center whitespace-nowrap">{{ $type['scores']['classroom_rank'] }}등
                                                ({{ $type['scores']['classroom_students_count'] ?? 0 }})
                                            </td>
                                            <td class="px-2 py-3 border border-stone-200 text-xs text-stone-900 text-center whitespace-nowrap">
                                                {{ number_format($type['scores']['grade_average'] ?? 0) }}</td>
                                            <td class="px-2 py-3 border border-stone-200 text-xs text-stone-900 text-center whitespace-nowrap">{{ $type['scores']['grade_rank'] ?? 0 }}등
                                                ({{ $type['scores']['grade_students_count'] ?? 0 }})</td>
                                        </tr>
                                    @endforeach
                                    <tr class="bg-blue-50 font-semibold">
                                        <td class="px-2 py-3 border border-stone-200 text-xs text-stone-700">전체</td>
                                        <td class="px-2 py-3 border border-stone-200 text-xs text-stone-700 text-center whitespace-nowrap">
                                            {{ number_format($test['total']['personal_score']) }} /
                                            {{ number_format($test['total']['total_questions'] ?? 0) }}
                                        </td>
                                        <td class="px-2 py-3 border border-stone-200 text-xs text-stone-700 text-center whitespace-nowrap">
                                            {{ number_format($test['total']['classroom_average']) }}</td>
                                        <td class="px-2 py-3 border border-stone-200 text-xs text-stone-700 text-center whitespace-nowrap">{{ number_format($test['total']['level_average']) }}
                                        </td>
                                        <td class="px-2 py-3 border border-stone-200 text-xs text-stone-700 text-center whitespace-nowrap">{{ $test['total']['classroom_rank'] }}등
                                            ({{ $test['total']['classroom_students_count'] ?? 0 }})
                                        </td>
                                        <td class="px-2 py-3 border border-stone-200 text-xs text-stone-700 text-center whitespace-nowrap">
                                            {{ number_format($test['total']['grade_average'] ?? 0) }}</td>
                                        <td class="px-2 py-3 border border-stone-200 text-xs text-stone-700 text-center whitespace-nowrap">{{ $test['total']['grade_rank'] ?? 0 }}등
                                            ({{ $test['total']['grade_students_count'] ?? 0 }})</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- 주간 숙제 테이블 -->
            @if ($weeklyReport['homework_report'])
                <div class="mb-8">
                    <div class="flex items-center mb-4 p-6">
                        <div class="w-2 h-6 bg-green-500 rounded-full mr-3"></div>
                        <h3 class="text-lg font-bold text-stone-800">{{ $weeklyReport['week_label'] }} 주간숙제</h3>
                    </div>
                    <div class="overflow-x-auto border-t border-stone-200">
                        <table class="w-full table-auto border-collapse border border-stone-200 rounded-lg overflow-hidden">
                            <thead>
                                <tr class="bg-stone-50">
                                    <th class="px-2 py-3 text-xs font-medium text-stone-700 border border-stone-200">숙제</th>
                                    <th class="px-2 py-3 text-xs font-medium text-stone-700 border border-stone-200 whitespace-nowrap">범위</th>
                                    <th class="px-2 py-3 text-xs font-medium text-stone-700 border border-stone-200 whitespace-nowrap">문제 수</th>
                                    <th class="px-2 py-3 text-xs font-medium text-stone-700 border border-stone-200 whitespace-nowrap">이행도</th>
                                    <th class="px-2 py-3 text-xs font-medium text-stone-700 border border-stone-200 whitespace-nowrap">정답률</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($weeklyReport['homework_report'] as $homework)
                                    @foreach ($homework['by_types'] as $type)
                                        <tr class="hover:bg-stone-50 transition-colors">
                                            @if ($loop->first)
                                                <td rowspan="{{ count($homework['by_types']) + 1 }}"
                                                    class="px-2 py-3 border border-stone-200 bg-stone-50 font-medium text-xs text-stone-700">
                                                    {{ Carbon\Carbon::parse($homework['date'])->format('m월d일') }}
                                                    {{ $homework['name'] }}
                                                </td>
                                            @endif
                                            <td class="px-2 py-3 border border-stone-200 text-xs text-stone-900 whitespace-nowrap">{!! $type['name'] !!}</td>
                                            <td class="px-2 py-3 border border-stone-200 text-xs text-stone-900 text-center whitespace-nowrap">
                                                {{ $type['correct_count'] }}/{{ $type['total_count'] }}</td>
                                            <td class="px-2 py-3 border border-stone-200 text-xs text-stone-900 text-center whitespace-nowrap">{{ number_format($type['attempt_rate'], 1) }}%</td>
                                            <td class="px-2 py-3 border border-stone-200 text-xs text-stone-900 text-center whitespace-nowrap">{{ number_format($type['correct_rate'], 1) }}%</td>
                                        </tr>
                                    @endforeach
                                    <tr class="bg-blue-50 font-semibold">
                                        <td class="px-2 py-3 border border-stone-200 text-xs text-stone-700">전체</td>
                                        <td class="px-2 py-3 border border-stone-200 text-xs text-stone-700 text-center whitespace-nowrap">
                                            {{ $homework['total']['correct_count'] }}/{{ $homework['total']['total_count'] }}
                                        </td>
                                        <td class="px-2 py-3 border border-stone-200 text-xs text-stone-700 text-center whitespace-nowrap">
                                            {{ number_format($homework['total']['attempt_rate'], 1) }}%</td>
                                        <td class="px-2 py-3 border border-stone-200 text-xs text-stone-700 text-center whitespace-nowrap">
                                            {{ number_format($homework['total']['correct_rate'], 1) }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- 강사 코멘트 -->
            <div class="mb-4 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-2 h-6 bg-purple-500 rounded-full mr-3"></div>
                    <h3 class="text-lg font-bold text-stone-800">{{ $weeklyReport['week_label'] }} 강사 코멘트</h3>
                </div>
                <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-2 border border-purple-200">
                    @if ($weeklyReport['comment_report'])
                        <p class="text-stone-800 text-sm leading-relaxed">{{ $weeklyReport['comment_report'] }}</p>
                    @else
                        <p class="text-stone-500 text-sm italic">강사 코멘트가 없습니다.</p>
                    @endif
                </div>
            </div>

            @if (!$weeklyReport['test_report'] && !$weeklyReport['homework_report'])
                <div class="text-center py-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-stone-100 rounded-full mb-4">
                        <svg class="w-8 h-8 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <p class="text-stone-500 text-sm">해당 기간에 검색된 주간 보고서가 없습니다.</p>
                </div>
            @endif
        </div>
@else
    <div class="text-center py-12">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-stone-100 rounded-full mb-6">
            <svg class="w-10 h-10 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
        </div>
        <h3 class="text-lg font-medium text-stone-700 mb-2">보고서가 없습니다</h3>
        <p class="text-stone-500 text-sm">해당 주차에 대한 주간 보고서가 없습니다.</p>
    </div>
@endif