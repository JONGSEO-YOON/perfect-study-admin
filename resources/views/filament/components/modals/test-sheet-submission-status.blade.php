<div class="p-4 space-y-4">
    {{-- 통계 요약 --}}
    <div class="grid grid-cols-3 gap-2">
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 text-center">
            <div class="text-xs text-gray-500 dark:text-gray-400">대상</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalCount }}</div>
        </div>
        <div class="bg-green-50 dark:bg-green-950/30 rounded-lg p-3 text-center">
            <div class="text-xs text-green-600 dark:text-green-400">제출</div>
            <div class="text-2xl font-bold text-green-700 dark:text-green-400">{{ $submittedCount }}</div>
        </div>
        <div class="bg-red-50 dark:bg-red-950/30 rounded-lg p-3 text-center">
            <div class="text-xs text-red-600 dark:text-red-400">미제출</div>
            <div class="text-2xl font-bold text-red-700 dark:text-red-400">{{ $notSubmittedCount }}</div>
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
                        <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-400">학생</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-400">반</th>
                        <th class="px-3 py-2 text-center font-medium text-gray-600 dark:text-gray-400">제출 여부</th>
                        <th class="px-3 py-2 text-center font-medium text-gray-600 dark:text-gray-400">정답수</th>
                        <th class="px-3 py-2 text-center font-medium text-gray-600 dark:text-gray-400">소요시간</th>
                        <th class="px-3 py-2 text-center font-medium text-gray-600 dark:text-gray-400">제출 시각</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($rows as $row)
                        <tr class="{{ $row['submitted'] ? '' : 'bg-red-50/30 dark:bg-red-950/10' }}">
                            <td class="px-3 py-2 text-gray-900 dark:text-gray-100 font-medium">{{ $row['student_name'] }}</td>
                            <td class="px-3 py-2 text-gray-600 dark:text-gray-400">{{ $row['classroom'] }}</td>
                            <td class="px-3 py-2 text-center">
                                @if($row['submitted'])
                                    <span class="inline-flex items-center gap-1 text-xs text-green-700 dark:text-green-400 font-semibold">
                                        ✓ 제출
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs text-red-600 dark:text-red-400 font-semibold">
                                        ✗ 미제출
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-center text-gray-700 dark:text-gray-300">
                                {{ $row['correct_count'] !== null ? $row['correct_count'] . '개' : '-' }}
                            </td>
                            <td class="px-3 py-2 text-center text-gray-600 dark:text-gray-400">
                                @if($row['time'])
                                    {{ floor($row['time'] / 60) }}분 {{ $row['time'] % 60 }}초
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-3 py-2 text-center text-xs text-gray-500 dark:text-gray-500">
                                {{ $row['submitted_at']?->format('Y-m-d H:i') ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
