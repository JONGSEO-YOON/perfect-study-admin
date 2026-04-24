<x-filament-panels::page>
    @if($classrooms->isEmpty())
        <div class="text-center py-12 text-gray-500 dark:text-gray-400">
            등록된 반이 없습니다.
        </div>
    @else
        @php
            $maxStudents = $classrooms->max('students_count');
        @endphp

        <style>
            .classroom-table {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
            }
            .classroom-table th,
            .classroom-table td {
                border: 1px solid #d1d5db;
                padding: 6px 10px;
                font-size: 13px;
                vertical-align: top;
            }
            .dark .classroom-table th,
            .dark .classroom-table td {
                border-color: #4b5563;
            }
            .classroom-table .header-row th {
                background: #1f2937;
                color: #fff;
                text-align: center;
                font-size: 14px;
                font-weight: 700;
                padding: 10px;
            }
            .classroom-table .label-cell {
                background: #f3f4f6;
                font-weight: 600;
                color: #4b5563;
                width: 52px;
                min-width: 52px;
                max-width: 52px;
                text-align: center;
                white-space: nowrap;
            }
            .dark .classroom-table .label-cell {
                background: #374151;
                color: #9ca3af;
            }
            .classroom-table .info-row td {
                color: #111827;
            }
            .dark .classroom-table .info-row td {
                color: #f3f4f6;
            }
            .classroom-table .student-header td {
                background: #e5e7eb;
                font-weight: 600;
                color: #4b5563;
                text-align: center;
                padding: 5px;
            }
            .dark .classroom-table .student-header td {
                background: #374151;
                color: #9ca3af;
            }
            .classroom-table .student-row td {
                padding: 4px 10px;
                color: #111827;
            }
            .dark .classroom-table .student-row td {
                color: #e5e7eb;
            }
            .classroom-table .student-row:hover td {
                background: #eff6ff;
            }
            .dark .classroom-table .student-row:hover td {
                background: #1e3a5f;
            }
            .classroom-table .num-cell {
                text-align: center;
                color: #9ca3af;
                width: 30px;
                min-width: 30px;
                max-width: 30px;
            }
            .classroom-table .empty-cell {
                text-align: center;
                color: #9ca3af;
                font-size: 12px;
                padding: 8px;
            }
            .dark .classroom-table .empty-cell {
                color: #6b7280;
            }
        </style>

        <div class="overflow-x-auto rounded-lg shadow">
            <table class="classroom-table">
                {{-- 반 이름 헤더 --}}
                <thead>
                    <tr class="header-row">
                        @foreach($classrooms as $classroom)
                            <th colspan="2">{{ $classroom['name'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    {{-- 학년 --}}
                    <tr class="info-row">
                        @foreach($classrooms as $classroom)
                            <td class="label-cell">학년</td>
                            <td>{{ $classroom['grades'] ?: '-' }} ({{ $classroom['level'] }}레벨)</td>
                        @endforeach
                    </tr>

                    {{-- 개설일 --}}
                    <tr class="info-row">
                        @foreach($classrooms as $classroom)
                            <td class="label-cell">개설일</td>
                            <td>{{ $classroom['started_at'] ? \Carbon\Carbon::parse($classroom['started_at'])->format('Y-m-d') : '-' }}</td>
                        @endforeach
                    </tr>

                    {{-- 시간표 --}}
                    <tr class="info-row">
                        @foreach($classrooms as $classroom)
                            <td class="label-cell">시간표</td>
                            <td>
                                @if(count($classroom['timetable']) > 0)
                                    {!! implode('<br>', array_map('e', $classroom['timetable'])) !!}
                                @else
                                    -
                                @endif
                            </td>
                        @endforeach
                    </tr>

                    {{-- 담임 --}}
                    <tr class="info-row">
                        @foreach($classrooms as $classroom)
                            <td class="label-cell">담임</td>
                            <td>{{ $classroom['teacher'] }}</td>
                        @endforeach
                    </tr>

                    {{-- 부담임 --}}
                    <tr class="info-row">
                        @foreach($classrooms as $classroom)
                            <td class="label-cell">부담임</td>
                            <td>{{ $classroom['sub_teacher'] }}</td>
                        @endforeach
                    </tr>

                    {{-- 학생 목록 헤더 --}}
                    <tr class="student-header">
                        @foreach($classrooms as $classroom)
                            <td></td>
                            <td>이름 ({{ $classroom['students_count'] }}명)</td>
                        @endforeach
                    </tr>

                    {{-- 학생 목록 --}}
                    @for($i = 0; $i < max($maxStudents, 1); $i++)
                        <tr class="student-row">
                            @foreach($classrooms as $classroom)
                                @if($classroom['students_count'] === 0 && $i === 0)
                                    <td colspan="2" class="empty-cell">배정된 학생 없음</td>
                                @elseif($i < $classroom['students_count'])
                                    <td class="num-cell">{{ $i + 1 }}</td>
                                    <td>{{ $classroom['students'][$i]->user->name ?? '-' }}</td>
                                @else
                                    <td class="num-cell"></td>
                                    <td></td>
                                @endif
                            @endforeach
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    @endif
</x-filament-panels::page>
