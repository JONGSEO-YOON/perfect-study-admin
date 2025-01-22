<div class="w-full">
    <div class="overflow-x-auto rounded-b-[0.8rem]">
        @forelse ($reports as $report)
            @php
                // 현재 테스트의 모든 점수 추출
                $availableScores = collect($report['score_data'])
                    ->flatMap(function ($majorData) {
                        return collect($majorData['items'])->flatMap(function ($item) {
                            return array_keys($item['scores']);
                        });
                    })
                    ->unique()
                    ->sort()
                    ->values();
            @endphp
            <table class="min-w-full bg-white mb-4">
                <thead>
                    <tr class="bg-gray-100 text-center">
                        <th class="px-1.5 py-3 text-sm font-semibold text-gray-700 border-b">
                            {{ $report['date'] }}
                        </th>
                        <th class="px-1.5 py-3 text-sm font-semibold text-gray-700 border-b">
                            {{ $report['name'] }}
                        </th>
                        @foreach ($availableScores as $score)
                            <th colspan="5" class="px-1.5 border-l py-3 text-sm font-semibold text-gray-700 border-b">
                                {{ $score }}점
                            </th>
                        @endforeach
                    </tr>
                    <tr class="bg-gray-50 text-center text-xs text-gray-700">
                        <th class="px-1 py-3 border-b">과목</th>
                        <th class="px-1 py-3 border-b">단원</th>
                        @foreach ($availableScores as $score)
                            @foreach ([1, 2, 3, 4, 5] as $level)
                                <th class="px-1 py-3 border-b border-l">레벨{{ $level }}</th>
                            @endforeach
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($report['score_data'] as $majorData)
                        @foreach ($majorData['items'] as $index => $item)
                            <tr class="hover:bg-gray-50 text-sm text-gray-900 text-center">
                                @if ($index === 0)
                                    <td rowspan="{{ count($majorData['items']) }}" class="px-1 py-3 border-b">
                                        {!! $majorData['major'] !!}
                                    </td>
                                @endif
                                <td class="px-1 py-3 border-b">{!! $item['type'] !!}</td>
                                @foreach ($availableScores as $score)
                                    @foreach ([1, 2, 3, 4, 5] as $level)
                                        <td class="px-1 py-3 border-b border-l">
                                            {{ $item['scores'][$score][$level] ?? 0 }}
                                        </td>
                                    @endforeach
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        @empty
            <div class="w-full text-center py-4 text-gray-500 col-span-2">
                해당 기간에 검색된 주간 보고서가 없습니다.
            </div>
        @endforelse
    </div>
</div>
