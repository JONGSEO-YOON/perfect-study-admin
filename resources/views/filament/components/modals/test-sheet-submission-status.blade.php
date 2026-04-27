<div class="p-4 space-y-4">
    {{-- 통계 요약: 본문제 --}}
    <div>
        <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">본문제</div>
        <div class="grid grid-cols-3 gap-2">
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 text-center">
                <div class="text-xs text-gray-500 dark:text-gray-400">대상</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalCount }}</div>
            </div>
            <div class="bg-green-50 dark:bg-green-950/30 rounded-lg p-3 text-center">
                <div class="text-xs text-green-600 dark:text-green-400">제출</div>
                <div class="text-2xl font-bold text-green-700 dark:text-green-400">{{ $mainSubmittedCount }}</div>
            </div>
            <div class="bg-red-50 dark:bg-red-950/30 rounded-lg p-3 text-center">
                <div class="text-xs text-red-600 dark:text-red-400">미제출</div>
                <div class="text-2xl font-bold text-red-700 dark:text-red-400">{{ $mainNotSubmittedCount }}</div>
            </div>
        </div>
    </div>

    {{-- 통계 요약: 오답유사유형 --}}
    <div>
        <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">오답유사유형</div>
        <div class="grid grid-cols-3 gap-2">
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 text-center">
                <div class="text-xs text-gray-500 dark:text-gray-400">대상</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $wrongAssignedCount }}</div>
            </div>
            <div class="bg-amber-50 dark:bg-amber-950/30 rounded-lg p-3 text-center">
                <div class="text-xs text-amber-600 dark:text-amber-400">제출</div>
                <div class="text-2xl font-bold text-amber-700 dark:text-amber-400">{{ $wrongSubmittedCount }}</div>
            </div>
            <div class="bg-orange-50 dark:bg-orange-950/30 rounded-lg p-3 text-center">
                <div class="text-xs text-orange-600 dark:text-orange-400">미제출</div>
                <div class="text-2xl font-bold text-orange-700 dark:text-orange-400">{{ $wrongNotSubmittedCount }}</div>
            </div>
        </div>
    </div>

    {{-- 학생 목록 --}}
    @if($rows->isEmpty())
        <div class="text-center py-8 text-gray-500">출제 대상 학생이 없습니다.</div>
    @else
        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
            <table class="w-full text-sm">
                <thead class="bg-gray-100 dark:bg-gray-800">
                    <tr>
                        <th rowspan="2" class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-400 border-r border-gray-200 dark:border-gray-700">학생</th>
                        <th rowspan="2" class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-400 border-r border-gray-200 dark:border-gray-700">반</th>
                        <th colspan="4" class="px-3 py-1 text-center font-semibold text-gray-700 dark:text-gray-300 border-r border-b border-gray-200 dark:border-gray-700 bg-green-50/40 dark:bg-green-950/20">본문제</th>
                        <th colspan="4" class="px-3 py-1 text-center font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 bg-amber-50/40 dark:bg-amber-950/20">오답유사유형</th>
                    </tr>
                    <tr>
                        <th class="px-2 py-2 text-center font-medium text-xs text-gray-500 dark:text-gray-400">제출</th>
                        <th class="px-2 py-2 text-center font-medium text-xs text-gray-500 dark:text-gray-400">정답수</th>
                        <th class="px-2 py-2 text-center font-medium text-xs text-gray-500 dark:text-gray-400">소요</th>
                        <th class="px-2 py-2 text-center font-medium text-xs text-gray-500 dark:text-gray-400 border-r border-gray-200 dark:border-gray-700">제출시각</th>
                        <th class="px-2 py-2 text-center font-medium text-xs text-gray-500 dark:text-gray-400">제출</th>
                        <th class="px-2 py-2 text-center font-medium text-xs text-gray-500 dark:text-gray-400">정답수</th>
                        <th class="px-2 py-2 text-center font-medium text-xs text-gray-500 dark:text-gray-400">소요</th>
                        <th class="px-2 py-2 text-center font-medium text-xs text-gray-500 dark:text-gray-400">제출시각</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($rows as $row)
                        <tr class="{{ $row['main_submitted'] ? '' : 'bg-red-50/30 dark:bg-red-950/10' }}">
                            <td class="px-3 py-2 text-gray-900 dark:text-gray-100 font-medium border-r border-gray-200 dark:border-gray-700">{{ $row['student_name'] }}</td>
                            <td class="px-3 py-2 text-gray-600 dark:text-gray-400 border-r border-gray-200 dark:border-gray-700">{{ $row['classroom'] }}</td>

                            {{-- 본문제 --}}
                            <td class="px-2 py-2 text-center">
                                @if($row['main_submitted'])
                                    <span class="inline-flex items-center text-xs text-green-700 dark:text-green-400 font-semibold">✓</span>
                                @else
                                    <span class="inline-flex items-center text-xs text-red-600 dark:text-red-400 font-semibold">✗</span>
                                @endif
                            </td>
                            <td class="px-2 py-2 text-center text-gray-700 dark:text-gray-300 text-xs">
                                {{ $row['main_correct_count'] !== null ? $row['main_correct_count'] . '개' : '-' }}
                            </td>
                            <td class="px-2 py-2 text-center text-gray-600 dark:text-gray-400 text-xs">
                                @if($row['main_time'])
                                    {{ floor($row['main_time'] / 60) }}분 {{ $row['main_time'] % 60 }}초
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-2 py-2 text-center text-xs text-gray-500 dark:text-gray-500 border-r border-gray-200 dark:border-gray-700">
                                {{ $row['main_submitted_at']?->format('m-d H:i') ?? '-' }}
                            </td>

                            {{-- 오답유사유형 --}}
                            <td class="px-2 py-2 text-center">
                                @if(!$row['wrong_assigned'])
                                    <span class="text-xs text-gray-400">-</span>
                                @elseif($row['wrong_submitted'])
                                    <span class="inline-flex items-center text-xs text-amber-700 dark:text-amber-400 font-semibold">✓</span>
                                @else
                                    <span class="inline-flex items-center text-xs text-orange-600 dark:text-orange-400 font-semibold">✗</span>
                                @endif
                            </td>
                            <td class="px-2 py-2 text-center text-gray-700 dark:text-gray-300 text-xs">
                                {{ $row['wrong_correct_count'] !== null ? $row['wrong_correct_count'] . '개' : '-' }}
                            </td>
                            <td class="px-2 py-2 text-center text-gray-600 dark:text-gray-400 text-xs">
                                @if($row['wrong_time'])
                                    {{ floor($row['wrong_time'] / 60) }}분 {{ $row['wrong_time'] % 60 }}초
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-2 py-2 text-center text-xs text-gray-500 dark:text-gray-500">
                                {{ $row['wrong_submitted_at']?->format('m-d H:i') ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
