<div class="w-full">
    <div class="overflow-x-auto rounded-b-[0.8rem] flex flex-col items-start gap-y-8">
        @forelse ($weeklyReports as $weekReport)
            @if (!$hide['header'] ?? true)
                <div class="pt-6 px-4 font-bold flex flex-row items-center w-full gap-x-2">
                    <div class="h-px  flex-1 bg-gray-300">
                    </div>
                    <div class="text-gray-600">
                        {{ $weekReport['week_label'] }}
                    </div>
                    <div class="h-px  flex-1 bg-gray-300">
                    </div>
                </div>
            @endif
            {{-- 주간 테스트 테이블 --}}
            @if ($weekReport['test_report'])
                <table class="w-full bg-white border-t">
                    <thead>
                        <tr class="bg-gray-100 text-center">
                            <th colspan="8" class="px-6 py-3 text-sm font-semibold text-gray-700 border-b">
                                {{ $weekReport['week_label'] }} 주간테스트
                            </th>
                        </tr>
                        <tr class="bg-gray-100 text-center">
                            <th class="px-6 py-3 text-sm font-semibold text-gray-700 border-b">테스트</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-700 border-b">범위</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-700 border-b min-w-[86px]">개인점수</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-700 border-b min-w-[86px]">반평균</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-700 border-b min-w-[86px]">레벨평균</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-700 border-b min-w-[86px]">반별 등수</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-700 border-b min-w-[86px]">학년평균</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-700 border-b min-w-[86px]">학년 등수</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($weekReport['test_report'] as $test)
                            @foreach ($test['by_types'] as $type)
                                <tr class="hover:bg-gray-50 text-sm text-gray-900 text-center">
                                    @if ($loop->first)
                                        <td rowspan="{{ count($test['by_types']) + 1 }}"
                                            class="px-2 py-3 border-b border-r bg-gray-100  font-medium">
                                            {{ Carbon\Carbon::parse($test['date'])->format('m월d일') }}
                                            {{ $test['name'] }}
                                        </td>
                                    @endif
                                    <td class="px-6  py-3 border-b">{!! $type['name'] !!}</td>
                                    <td class="px-6 py-3 border-b">
                                        {{ number_format($type['scores']['personal_score']) }} /
                                        {{ number_format($type['scores']['total_questions'] ?? 0) }}</td>
                                    <td class="px-6 py-3 border-b">
                                        {{ number_format($type['scores']['classroom_average']) }}</td>
                                    <td class="px-6 py-3 border-b">
                                        {{ number_format($type['scores']['level_average']) }}</td>
                                    <td class="px-6 py-3 border-b">{{ $type['scores']['classroom_rank'] }}등
                                        ({{ $type['scores']['classroom_students_count'] ?? 0 }})
                                    </td>
                                    <td class="px-6 py-3 border-b">
                                        {{ number_format($type['scores']['grade_average'] ?? 0) }}</td>
                                    <td class="px-6 py-3 border-b">{{ $type['scores']['grade_rank'] ?? 0 }}등
                                        ({{ $type['scores']['grade_students_count'] ?? 0 }})</td>
                                </tr>
                            @endforeach
                            <tr class="hover:bg-gray-50 text-sm text-gray-900 text-center font-semibold bg-gray-50">
                                <td class="px-6 py-3 border-b">전체</td>
                                <td class="px-6 py-3 border-b">
                                    {{ number_format($test['total']['personal_score']) }} /
                                    {{ number_format($test['total']['total_questions'] ?? 0) }}
                                </td>
                                <td class="px-6 py-3 border-b">
                                    {{ number_format($test['total']['classroom_average']) }}</td>
                                <td class="px-6 py-3 border-b">{{ number_format($test['total']['level_average']) }}
                                </td>
                                <td class="px-6 py-3 border-b">{{ $test['total']['classroom_rank'] }}등
                                    ({{ $test['total']['classroom_students_count'] ?? 0 }})
                                </td>

                                <td class="px-6 py-3 border-b">
                                    {{ number_format($test['total']['grade_average'] ?? 0) }}</td>
                                <td class="px-6 py-3 border-b">{{ $test['total']['grade_rank'] ?? 0 }}등
                                    ({{ $test['total']['grade_students_count'] ?? 0 }})</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if (!$hide['attendance'] ?? true)
                {{-- 출결 테이블 --}}
                <div class="flex flex-col">
                    {{-- 출결 테이블 --}}
                    <table class="min-w-fit bg-white border-r border-t">
                        <thead>
                            <tr class="bg-gray-100 text-center">
                                <th colspan="5" class="px-6 py-3 text-sm font-semibold text-gray-700 border-b">
                                    {{ $weekReport['week_label'] }} 출결
                                </th>
                            </tr>
                            <tr class="bg-gray-100 text-center">
                                <th class="px-6 py-3 text-sm font-semibold text-gray-700 border-b">날짜</th>
                                <th class="px-6 py-3 text-sm font-semibold text-gray-700 border-b">시간</th>
                                <th class="px-6 py-3 text-sm font-semibold text-gray-700 border-b">타입</th>
                                <th class="px-6 py-3 text-sm font-semibold text-gray-700 border-b">비고</th>
                                <th class="px-6 py-3 text-sm font-semibold text-gray-700 border-b"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($weekReport['attendance_logs']) && $weekReport['attendance_logs'] && $weekReport['attendance_logs']->isNotEmpty())
                                @foreach ($weekReport['attendance_logs'] as $log)
                                    <tr class="hover:bg-gray-50 text-sm text-gray-900 text-center">
                                        <td class="px-6 py-3 border-b">
                                            {{ $log->created_at->format('m월d일') }}
                                        </td>
                                        <td class="px-6 py-3 border-b">
                                            <div class="w-[198px]">{{ $log->created_at->format('H:i') }}</div>
                                        </td>
                                        <td class="px-6 py-3 border-b">
                                            정규{{ $log->type === 'in' ? '등원' : '하원' }}{{ $log->is_late ? ' (지각)' : '' }}
                                        </td>
                                        <td class="px-6 py-3 border-b">
                                            <div class="w-[150px]">{{ $log->memo ?? '' }}</div>
                                        </td>
                                        @if ($this->readonly)
                                            <td class="border-b">

                                            </td>
                                        @else
                                            <td class="px-6 py-3 border-b">
                                                <div class="flex gap-1">
                                                    <x-filament::icon-button
                                                        wire:click="mountAction('editAttendance', { log_id: '{{ $log->id }}' })"
                                                        icon="heroicon-m-pencil-square" color="warning" />
                                                    <x-filament::icon-button
                                                        wire:click="mountAction('deleteAttendance', { date: '{{ $log->created_at->format('Y-m-d') }}' })"
                                                        icon="heroicon-m-trash" color="danger" />
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-sm text-center text-gray-500 border-b">
                                        해당 주차에 출결 기록이 없습니다.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>


                    @if (!$readonly)
                        <div class="flex flex-col items-end">
                            <x-filament::button
                                wire:click="mountAction('addAttendance', { week: {{ $weekReport['week'] }}, year: {{ $weekReport['year'] }} })"
                                icon="heroicon-m-plus-circle" size="sm" class="w-fit  mt-2">
                                출결 기록
                            </x-filament::button>
                        </div>
                    @endif

                </div>
            @endif
            {{-- 주간 숙제 테이블 --}}
            @if ($weekReport['homework_report'])
                <table class="min-w-fit bg-white border-r border-t">
                    <thead>
                        <tr class="bg-gray-100 text-center">
                            <th colspan="6" class="px-6 py-3 text-sm font-semibold text-gray-700 border-b">
                                {{ $weekReport['week_label'] }} 주간숙제
                            </th>
                        </tr>
                        <tr class="bg-gray-100 text-center">
                            <th class="px-6 py-3 text-sm font-semibold text-gray-700 border-b">숙제</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-700 border-b">범위</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-700 border-b">문제 수</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-700 border-b">이행도</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-700 border-b">정답률</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($weekReport['homework_report'] as $homework)
                            @foreach ($homework['by_types'] as $type)
                                <tr class="hover:bg-gray-50 text-sm text-gray-900 text-center">
                                    @if ($loop->first)
                                        <td rowspan="{{ count($homework['by_types']) + 1 }}"
                                            class="px-8 py-3 border-b border-r bg-gray-100  font-medium">
                                            {{ Carbon\Carbon::parse($homework['date'])->format('m월d일') }}
                                            {{ $homework['name'] }}
                                        </td>
                                    @endif
                                    <td class="px-6 py-3 border-b">{!! $type['name'] !!}</td>
                                    <td class="px-6 py-3 border-b">
                                        {{ $type['correct_count'] }}/{{ $type['total_count'] }}</td>
                                    <td class="px-6 py-3 border-b">{{ number_format($type['attempt_rate'], 1) }}%</td>
                                    <td class="px-6 py-3 border-b">{{ number_format($type['correct_rate'], 1) }}%</td>
                                </tr>
                            @endforeach
                            <tr class="hover:bg-gray-50 text-sm text-gray-900 text-center font-semibold bg-gray-50">
                                <td class="px-6 py-3 border-b">전체</td>
                                <td class="px-6 py-3 border-b">
                                    {{ $homework['total']['correct_count'] }}/{{ $homework['total']['total_count'] }}
                                </td>
                                <td class="px-6 py-3 border-b">
                                    {{ number_format($homework['total']['attempt_rate'], 1) }}%</td>
                                <td class="px-6 py-3 border-b">
                                    {{ number_format($homework['total']['correct_rate'], 1) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif



            {{-- 강사 코멘트 --}}
            @if (!$hide['comment'] ?? true)
                <div class="p-4 w-1/2">
                    <div data-field-wrapper="" class="fi-fo-field-wrp">
                        <div class="grid gap-y-2">
                            <div class="flex items-center gap-x-3 justify-between">
                                <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                                    <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                        {{ $weekReport['week_label'] }} 강사 코멘트
                                    </span>
                                </label>
                            </div>
                            <div class="grid auto-cols-fr gap-y-2">
                                <div
                                    class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-2 ring-gray-950/10 dark:ring-white/20 [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-600 dark:[&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-500 fi-fo-textarea overflow-hidden">
                                    <div class="min-w-0 flex-1">
                                        <div wire:ignore.self="" style="height: '5rem'">
                                            @if ($this->readonly)
                                                @if ($weekReport['comment_report'])
                                                    {{ $weekReport['comment_report'] }}
                                                @else
                                                    <div class="text-gray-500 p-5">강사 코멘트가 없습니다.</div>
                                                @endif
                                            @else
                                                <textarea rows="5"
                                                    wire:model.defer="comments.{{ $weekReport['year'] }}-{{ $weekReport['week'] }}"
                                                    class="block h-full w-full border-none bg-transparent px-3 py-1.5 text-base text-gray-950 placeholder:text-gray-400 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] dark:disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.500)] sm:text-sm sm:leading-6">{{ $weekReport['comment_report'] }}</textarea>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @if (!$this->readonly)
                                    <div class="flex justify-end gap-2 mt-2">
                                        @if ($weekReport['comment_status'] === 'sent')
                                            {{-- 학부모 전달 완료 상태 --}}
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm text-green-600 font-medium">✓ 학부모에게 전달됨</span>
                                                <x-filament::button
                                                    wire:click="deleteComment({{ $weekReport['year'] }}, {{ $weekReport['week'] }})"
                                                    size="sm"
                                                    color="danger">
                                                    삭제
                                                </x-filament::button>
                                            </div>
                                        @else
                                            {{-- 임시 저장 또는 작성 중 상태 --}}
                                            <x-filament::button
                                                wire:click="saveComment({{ $weekReport['year'] }}, {{ $weekReport['week'] }})"
                                                size="sm"
                                                color="gray">
                                                임시 저장
                                            </x-filament::button>
                                            <x-filament::button
                                                wire:click="sendCommentToParent({{ $weekReport['year'] }}, {{ $weekReport['week'] }})"
                                                size="sm">
                                                학부모 전달
                                            </x-filament::button>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if (!$weekReport['test_report'] && !$weekReport['homework_report'] && $hide['header'])
                <div class="w-full text-center py-4 text-gray-500">
                    해당 기간에 검색된 주간 보고서가 없습니다.
                </div>
            @endif

            <div class="page-break"></div>
        @empty
            <div class="w-full text-center py-4 text-gray-500">
                해당 기간에 검색된 주간 보고서가 없습니다.
            </div>
        @endforelse

    </div>
    <x-filament-actions::modals />
</div>
