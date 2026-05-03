@php
    $studentId = $this->data['analysis_student_id'] ?? null;
    $report = null;
    $student = null;

    if ($studentId) {
        $student = \App\Models\Student::with('user')->find($studentId);
        $report = collect($record->report ?? [])->firstWhere('student_id', (int) $studentId);
    }
@endphp

<div class="p-2">
    @if (!$studentId)
        <div class="text-center py-12 text-gray-500 dark:text-gray-400">
            상단에서 학생을 선택하면 해당 학생의 오답 유형분석표가 표시됩니다.
        </div>
    @elseif (!$report)
        <div class="text-center py-12 text-gray-500 dark:text-gray-400">
            <strong>{{ $student->user->name ?? '-' }}</strong> 학생의 분석 데이터가 아직 없습니다.<br>
            <span class="text-xs">학생이 답안을 제출했는지 확인하세요.</span>
        </div>
    @else
        @php
            $clean = fn($s) => trim(strip_tags((string) $s));
            $hierarchy = $report['hierarchical_analysis'] ?? [];
            $rows = [];
            foreach ($hierarchy as $major => $majorData) {
                foreach (($majorData['sub_categories'] ?? []) as $middle => $middleData) {
                    foreach (($middleData['types'] ?? []) as $type => $typeData) {
                        $levels = [];
                        foreach (($typeData['levels'] ?? []) as $level => $data) {
                            $levels[$level] = $data;
                        }
                        $rows[] = [
                            'major' => $clean($majorData['name'] ?? $major),
                            'middle' => $clean($middleData['name'] ?? $middle),
                            'type' => $clean($typeData['name'] ?? $type),
                            'levels' => $levels,
                        ];
                    }
                }
            }
        @endphp

        @if (empty($rows))
            <div class="text-center py-8 text-gray-500">유형 분석 데이터가 없습니다.</div>
        @else
            <div class="mb-3 text-sm">
                <strong class="text-gray-900 dark:text-white">{{ $student->user->name }}</strong>
                <span class="text-gray-500">학생 - {{ $record->name }}</span>
            </div>
            <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                <table class="w-full text-xs">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="px-2 py-2 text-left font-medium text-gray-600 dark:text-gray-400">대분류</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-600 dark:text-gray-400">중분류</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-600 dark:text-gray-400">유형</th>
                            <th class="px-2 py-2 text-center font-medium text-gray-600 dark:text-gray-400">L1</th>
                            <th class="px-2 py-2 text-center font-medium text-gray-600 dark:text-gray-400">L2</th>
                            <th class="px-2 py-2 text-center font-medium text-gray-600 dark:text-gray-400">L3</th>
                            <th class="px-2 py-2 text-center font-medium text-gray-600 dark:text-gray-400">L4</th>
                            <th class="px-2 py-2 text-center font-medium text-gray-600 dark:text-gray-400">L5</th>
                            <th class="px-2 py-2 text-center font-medium text-gray-600 dark:text-gray-400">합계</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($rows as $row)
                            @php
                                $totalCorrect = 0;
                                $totalAll = 0;
                                foreach ($row['levels'] as $lv) {
                                    $totalCorrect += $lv['correct'] ?? 0;
                                    $totalAll += $lv['total'] ?? 0;
                                }
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-2 py-1.5 text-gray-700 dark:text-gray-300">{{ $row['major'] }}</td>
                                <td class="px-2 py-1.5 text-gray-700 dark:text-gray-300">{{ $row['middle'] }}</td>
                                <td class="px-2 py-1.5 text-gray-900 dark:text-gray-100 font-medium">{{ $row['type'] }}</td>
                                @foreach ([1, 2, 3, 4, 5] as $lv)
                                    @php $cell = $row['levels'][$lv] ?? null; @endphp
                                    <td class="px-2 py-1.5 text-center">
                                        @if ($cell && ($cell['total'] ?? 0) > 0)
                                            <span class="@if (($cell['percentage'] ?? 0) >= 80) text-green-600 @elseif (($cell['percentage'] ?? 0) >= 50) text-yellow-600 @else text-red-600 @endif">
                                                {{ $cell['correct'] }}/{{ $cell['total'] }}
                                            </span>
                                        @else
                                            <span class="text-gray-300">-</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="px-2 py-1.5 text-center font-semibold text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-800">
                                    @if ($totalAll > 0)
                                        {{ $totalCorrect }}/{{ $totalAll }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-2 text-[11px] text-gray-400">L1~L5는 난이도 레벨, 색상: 초록(80%↑) / 노랑(50~79%) / 빨강(50%↓)</p>
        @endif
    @endif
</div>
