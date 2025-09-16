<x-filament-panels::page>
    <div class="space-y-6">
        <!-- 헤더 -->
        <div class="text-center">
            <div class="mt-4 flex items-center justify-center space-x-4">
                <button
                    wire:click="previousMonth"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    이전달
                </button>

                <h3 class="text-xl font-semibold text-gray-900 dark:text-white min-w-[120px]">
                    {{ $selectedYear }}년 {{ $selectedMonth }}월
                </h3>

                @php
                    $currentYear = now()->year;
                    $currentMonth = now()->month;
                    $isCurrentMonth = $selectedYear === $currentYear && $selectedMonth === $currentMonth;
                @endphp

                <button
                    wire:click="nextMonth"
                    @if($isCurrentMonth) disabled @endif
                    class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md {{ $isCurrentMonth ? 'text-gray-400 bg-gray-100 dark:bg-gray-700 cursor-not-allowed' : 'text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    다음달
                    <svg class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- 필터 -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            {{ $this->form }}
        </div>

        <!-- 출석 테이블 -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden w-full">
            <div class="overflow-x-auto w-full">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700" style="table-layout: fixed; width: 100%;">
                    <!-- 테이블 헤더 -->
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="sticky left-0 z-10 bg-gray-50 dark:bg-gray-700 px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r border-gray-200 dark:border-gray-600" style="width: 120px;">
                                학생명
                            </th>
                            <!-- 날짜 헤더 -->
                            @for($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $date = \Carbon\Carbon::create($selectedYear, $selectedMonth, $day);
                                $dayNames = ['일', '월', '화', '수', '목', '금', '토'];
                                $dayOfWeek = $dayNames[$date->dayOfWeek];
                                $dayColor = in_array($date->dayOfWeek, [0, 6]) ? 'text-red-500' : 'text-gray-500 dark:text-gray-300';
                            @endphp
                            <th class="px-2 py-3 text-center text-xs font-medium {{ $dayColor }} uppercase tracking-wider" style="width: calc((100% - 120px) / {{ $daysInMonth }});">
                                <div>{{ $day }}</div>
                                <div class="text-xs">{{ $dayOfWeek }}</div>
                            </th>
                            @endfor
                        </tr>
                    </thead>
                    <!-- 테이블 바디 -->
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($students as $student)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="sticky left-0 z-10 bg-white dark:bg-gray-800 px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-600">
                                {{ $student->user->name }}
                            </td>
                            @for($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $dayLogs = $attendanceData[$student->id][$day] ?? collect();
                                $dayLog = $dayLogs->first();

                                if (!$dayLog) {
                                    $icon = '-';
                                    $bgColor = 'text-gray-400';
                                } elseif ($dayLog->is_absent) {
                                    $icon = '/';
                                    $bgColor = 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800';
                                } elseif ($dayLog->is_late) {
                                    $icon = 'x';
                                    $bgColor = 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800';
                                } elseif ($dayLog->check_in_time || $dayLog->check_out_time) {
                                    if ($dayLog->is_supplementary) {
                                        $icon = '+';
                                        $bgColor = 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800';
                                    } else {
                                        $icon = '✓';
                                        $bgColor = 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800';
                                    }
                                } else {
                                    $icon = '-';
                                    $bgColor = 'text-gray-400';
                                }
                            @endphp
                            <td class="px-2 py-4 text-center text-sm">
                                <button
                                    wire:click="openAttendanceModal({{ $student->id }}, {{ $day }}, '{{ $student->user->name }}')"
                                    class="w-full h-full flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-600 rounded transition-colors duration-150"
                                    title="클릭하여 출결 기록 편집"
                                >
                                    @if($icon === '-')
                                        <span class="{{ $bgColor }}">{{ $icon }}</span>
                                    @else
                                        <span class="{{ $bgColor }}">{{ $icon }}</span>
                                    @endif
                                </button>
                            </td>
                            @endfor
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 범례 -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">범례</h3>
            <div class="flex flex-wrap gap-4">
                <div class="flex items-center">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-2">✓</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">출석 (정규)</span>
                </div>
                <div class="flex items-center">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 mr-2">+</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">출석 (보충)</span>
                </div>
                <div class="flex items-center">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 mr-2">x</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">지각</span>
                </div>
                <div class="flex items-center">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 mr-2">/</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">결석</span>
                </div>
                <div class="flex items-center">
                    <span class="text-gray-400 mr-2">-</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">기록없음</span>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
