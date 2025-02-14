<div class="w-full">
    <div class="overflow-x-auto rounded-b-[0.8rem]">
        @forelse ($reports as $report)
            <table class="min-w-full bg-white mb-4">
                <thead>
                    <tr class="bg-gray-100 text-center">
                        <th colspan="2" class="px-1.5 py-3 text-sm font-semibold text-gray-700 border-b">
                            {{ $report['date'] }}
                        </th>
                        <th class="px-1.5 py-3 text-sm font-semibold text-gray-700 border-b">
                            {{ $report['name'] }}
                        </th>
                        @for ($level = 1; $level <= 5; $level++)
                            <th colspan="3" class="px-1.5 py-3 text-sm font-semibold text-gray-700 border-b">
                                레벨{{ $level }}
                            </th>
                        @endfor
                    </tr>
                    <tr class="bg-gray-50 text-center text-xs text-gray-700">
                        <th class="px-1 py-3 border-b">대단원</th>
                        <th class="px-1 py-3 border-b">중단원</th>
                        <th class="px-1 py-3 border-b">문제유형</th>
                        @for ($level = 1; $level <= 5; $level++)
                            <th class="px-1 py-3 border-b min-w-[50px]">출제문항</th>
                            <th class="px-1 py-3 border-b  min-w-[50px]">정답개수</th>
                            @if ($reportKey === 'hierarchy')
                                <th class="px-1 py-3 border-b  min-w-[50px]">정답률</th>
                            @else
                                <th class="px-1 py-3 border-b  min-w-[50px]">모름</th>
                            @endif
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @php
                        $currentMajor = '';
                        $currentMiddle = '';
                        $majorRowspan = 0;
                        $middleRowspan = 0;
                    @endphp

                    @foreach ($report[$reportKey] as $index => $item)
                        <tr class="hover:bg-gray-50 text-sm text-gray-900 text-center">
                            @php
                                if (!isset($item['major'])) {
                                    dd($item);
                                }
                            @endphp
                            @if ($currentMajor !== $item['major'])
                                @php
                                    $currentMajor = $item['major'];
                                    $majorRowspan = count(
                                        array_filter($report[$reportKey], fn($h) => $h['major'] === $currentMajor),
                                    );
                                @endphp
                                <td rowspan="{{ $majorRowspan }}" class="px-1 py-3 border-b">
                                    {!! $item['major'] !!}
                                </td>
                            @endif

                            @if ($currentMiddle !== $item['middle'])
                                @php
                                    $currentMiddle = $item['middle'];
                                    $middleRowspan = count(
                                        array_filter($report[$reportKey], fn($h) => $h['middle'] === $currentMiddle),
                                    );
                                @endphp
                                <td rowspan="{{ $middleRowspan }}" class="px-1 py-3 border-b">
                                    {!! $item['middle'] !!}
                                </td>
                            @endif

                            <td class="px-1 py-3 border-b">{!! $item['type'] !!}</td>

                            @for ($level = 1; $level <= 5; $level++)
                                <td class="px-1 py-3 border-b border-r">{{ $item['levels'][$level]['total'] ?? 0 }}</td>
                                <td class="px-1 py-3 border-b border-r">{{ $item['levels'][$level]['correct'] ?? 0 }}
                                </td>
                                @if ($reportKey === 'hierarchy')
                                    <td class="px-1 py-3 border-b border-r">
                                        {{ $item['levels'][$level]['percentage'] ?? 0 }}%
                                    </td>
                                @else
                                    <td class="px-1 py-3 border-b border-r">
                                        {{ $item['levels'][$level]['dont_know_answers_count'] ?? 0 }}
                                    </td>
                                @endif
                            @endfor
                        </tr>
                    @endforeach

                    <!-- 개인별 전체 정답률 -->
                    <tr class="hover:bg-gray-50 text-sm text-gray-900 text-center">
                        <td rowspan="2" colspan="2" class="px-1 py-3 border-b">단원별 전체 정답률</td>
                        <td class="px-1 py-3 border-b">개인</td>
                        @for ($level = 1; $level <= 5; $level++)
                            <td class="px-1 py-3 border-b border-r">
                                {{ $report['personal_level'][$level]['total'] ?? 0 }}</td>
                            <td class="px-1 py-3 border-b border-r">
                                {{ $report['personal_level'][$level]['correct'] ?? 0 }}</td>
                            @if ($reportKey === 'hierarchy')
                                <td class="px-1 py-3 border-b border-r">
                                    {{ $report['personal_level'][$level]['percentage'] ?? 0 }}%
                                </td>
                            @else
                                <td class="px-1 py-3 border-b border-r">
                                    -
                                </td>
                            @endif
                        @endfor
                    </tr>

                    <!-- 반별 전체 정답률 -->
                    <tr class="hover:bg-gray-50 text-sm text-gray-900 text-center">
                        <td class="px-1 py-3 border-b">반별</td>
                        @for ($level = 1; $level <= 5; $level++)
                            <td class="px-1 py-3 border-b border-r">
                                {{ $report['classroom_level'][$level]['total'] ?? 0 }}</td>
                            <td class="px-1 py-3 border-b border-r">
                                {{ $report['classroom_level'][$level]['correct'] ?? 0 }}
                            </td>
                            @if ($reportKey === 'hierarchy')
                                <td class="px-1 py-3 border-b border-r">
                                    {{ $report['classroom_level'][$level]['percentage'] ?? 0 }}%
                                </td>
                            @else
                                <td class="px-1 py-3 border-b border-r">
                                    -
                                </td>
                            @endif
                        @endfor
                    </tr>
                </tbody>
            </table>
        @empty
            <div class="w-full text-center py-4 text-gray-500 col-span-2">
                해당 기간에 검색된 주간 보고서가 없습니다.
            </div>
        @endforelse
    </div>
</div>
