<div class="bg-white overflow-hidden">
    <div>
        <div class="flex items-center mb-4 p-6">
            <div class="w-2 h-6 bg-orange-500 rounded-full mr-3"></div>
            <h3 class="text-lg font-bold text-stone-800">오답 유형분석표</h3>
        </div>
        
       @forelse ($reports as $report)
            <div class="mb-8">
                <div class="flex items-center mb-4 p-3">
                    <h3 class="text-lg font-bold text-stone-800">{{ $report['date'] }} - {{ $report['name'] }}</h3>
                </div>
                <div class="overflow-x-auto border-t border-stone-200">
                    <table class="w-full table-auto border-collapse border border-stone-200 rounded-lg overflow-hidden">
                        <thead>
                            <tr class="bg-stone-50">
                                <th class="px-2 py-3 text-xs font-medium text-stone-700 border border-stone-200">대단원</th>
                                <th class="px-2 py-3 text-xs font-medium text-stone-700 border border-stone-200">중단원</th>
                                <th class="px-2 py-3 text-xs font-medium text-stone-700 border border-stone-200 whitespace-nowrap">문제유형</th>
                                @for ($level = 1; $level <= 5; $level++)
                                    <th class="px-2 py-3 text-xs font-medium text-stone-700 border border-stone-200 whitespace-nowrap">L{{ $level }}출제</th>
                                    <th class="px-2 py-3 text-xs font-medium text-stone-700 border border-stone-200 whitespace-nowrap">L{{ $level }}정답</th>
                                    @if ($reportKey === 'hierarchy')
                                        <th class="px-2 py-3 text-xs font-medium text-stone-700 border border-stone-200 whitespace-nowrap">L{{ $level }}율</th>
                                    @else
                                        <th class="px-2 py-3 text-xs font-medium text-stone-700 border border-stone-200 whitespace-nowrap">L{{ $level }}모름</th>
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
                                <tr class="hover:bg-stone-50 transition-colors">
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
                                        <td rowspan="{{ $majorRowspan }}" class="px-2 py-3 border border-stone-200 bg-stone-50 font-medium text-xs text-stone-700">
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
                                        <td rowspan="{{ $middleRowspan }}" class="px-2 py-3 border border-stone-200 bg-stone-50 font-medium text-xs text-stone-700">
                                            {!! $item['middle'] !!}
                                        </td>
                                    @endif

                                    <td class="px-2 py-3 border border-stone-200 text-xs text-stone-900 whitespace-nowrap">{!! $item['type'] !!}</td>

                                    @for ($level = 1; $level <= 5; $level++)
                                        <td class="px-2 py-3 border border-stone-200 text-xs text-stone-900 text-center whitespace-nowrap">{{ $item['levels'][$level]['total'] ?? 0 }}</td>
                                        <td class="px-2 py-3 border border-stone-200 text-xs text-stone-900 text-center whitespace-nowrap">{{ $item['levels'][$level]['correct'] ?? 0 }}</td>
                                        @if ($reportKey === 'hierarchy')
                                            <td class="px-2 py-3 border border-stone-200 text-xs text-stone-900 text-center whitespace-nowrap">
                                                {{ $item['levels'][$level]['percentage'] ?? 0 }}%
                                            </td>
                                        @else
                                            <td class="px-2 py-3 border border-stone-200 text-xs text-stone-900 text-center whitespace-nowrap">
                                                {{ $item['levels'][$level]['dont_know_answers_count'] ?? 0 }}
                                            </td>
                                        @endif
                                    @endfor
                                </tr>
                            @endforeach

                            <!-- 개인별 전체 정답률 -->
                            <tr class="bg-blue-50 font-semibold">
                                <td rowspan="2" colspan="2" class="px-2 py-3 border border-stone-200 text-xs text-stone-700">단원별 전체 정답률</td>
                                <td class="px-2 py-3 border border-stone-200 text-xs text-stone-700">개인</td>
                                @for ($level = 1; $level <= 5; $level++)
                                    <td class="px-2 py-3 border border-stone-200 text-xs text-stone-700 text-center whitespace-nowrap">
                                        {{ $report['personal_level'][$level]['total'] ?? 0 }}</td>
                                    <td class="px-2 py-3 border border-stone-200 text-xs text-stone-700 text-center whitespace-nowrap">
                                        {{ $report['personal_level'][$level]['correct'] ?? 0 }}</td>
                                    @if ($reportKey === 'hierarchy')
                                        <td class="px-2 py-3 border border-stone-200 text-xs text-stone-700 text-center whitespace-nowrap">
                                            {{ $report['personal_level'][$level]['percentage'] ?? 0 }}%
                                        </td>
                                    @else
                                        <td class="px-2 py-3 border border-stone-200 text-xs text-stone-700 text-center whitespace-nowrap">
                                            -
                                        </td>
                                    @endif
                                @endfor
                            </tr>

                            <!-- 반별 전체 정답률 -->
                            <tr class="bg-blue-50 font-semibold">
                                <td class="px-2 py-3 border border-stone-200 text-xs text-stone-700">반별</td>
                                @for ($level = 1; $level <= 5; $level++)
                                    <td class="px-2 py-3 border border-stone-200 text-xs text-stone-700 text-center whitespace-nowrap">
                                        {{ $report['classroom_level'][$level]['total'] ?? 0 }}</td>
                                    <td class="px-2 py-3 border border-stone-200 text-xs text-stone-700 text-center whitespace-nowrap">
                                        {{ round($report['classroom_level'][$level]['total'] * ($report['classroom_level'][$level]['percentage'] / 100), 1) ?? 0 }}
                                    </td>
                                    @if ($reportKey === 'hierarchy')
                                        <td class="px-2 py-3 border border-stone-200 text-xs text-stone-700 text-center whitespace-nowrap">
                                            {{ $report['classroom_level'][$level]['percentage'] ?? 0 }}%
                                        </td>
                                    @else
                                        <td class="px-2 py-3 border border-stone-200 text-xs text-stone-700 text-center whitespace-nowrap">
                                            -
                                        </td>
                                    @endif
                                @endfor
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="w-full text-center py-4 text-gray-500 col-span-2">
                해당 기간에 검색된 주간 보고서가 없습니다.
            </div>
        @endforelse
    </div>
</div>