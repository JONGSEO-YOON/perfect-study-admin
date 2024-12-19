<table style="width: 100%; border-collapse: collapse; ">
    <tr>
        <td colspan="8"
            style="text-align:left; height: 50px; vertical-align: middle; padding: 15px; font-weight: bold; font-size: 20px; ">
            &nbsp;&nbsp; &nbsp; 오답 유형분석표
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
</table>

@foreach ($reports as $report)
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

    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th
                    style="text-align:center;  height:26px; background: #eeeeee; font-weight:bold; vertical-align:center; padding: 12px; border: 1px solid #ddd; width: 150px;">
                    {{ $report['date'] }}
                </th>
                <th
                    style="text-align:center; height:26px; background: #eeeeee; font-weight:bold; vertical-align:center; padding: 12px; border: 1px solid #ddd; width: 230px;">
                    {{ $report['name'] }}
                </th>
                @foreach ($availableScores as $score)
                    <th colspan="5"
                        style="text-align:center; height:26px; background: #eeeeee; font-weight:bold; vertical-align:center; padding: 12px; border: 1px solid #ddd;">
                        {{ $score }}점
                    </th>
                @endforeach
            </tr>
            <tr>
                <th
                    style="text-align:center; height:26px; vertical-align:center; padding: 12px; border: 1px solid #ddd;">
                    과목</th>
                <th
                    style="text-align:center; height:26px; vertical-align:center; padding: 12px; border: 1px solid #ddd;">
                    단원</th>
                @foreach ($availableScores as $score)
                    @foreach ([1, 2, 3, 4, 5] as $level)
                        <th
                            style="text-align:center; height:26px; vertical-align:center; padding: 12px; border: 1px solid #ddd;">
                            레벨{{ $level }}</th>
                    @endforeach
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($report['score_data'] as $majorData)
                @foreach ($majorData['items'] as $index => $item)
                    <tr>
                        @if ($index === 0)
                            <td rowspan="{{ count($majorData['items']) }}"
                                style="text-align:center; height:26px; vertical-align:center; padding: 10px; border: 1px solid #ddd; ">
                                {{ $majorData['major'] }}
                            </td>
                        @endif
                        <td
                            style="text-align:center; height:26px; vertical-align:center; padding: 10px; border: 1px solid #ddd;">
                            {{ $item['type'] }}</td>
                        @foreach ($availableScores as $score)
                            @foreach ([1, 2, 3, 4, 5] as $level)
                                <td
                                    style="text-align:center; height:26px; vertical-align:center; padding: 10px; border: 1px solid #ddd;">
                                    {{ $item['scores'][$score][$level] ?? 0 }}
                                </td>
                            @endforeach
                        @endforeach
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <!-- 테이블 간 간격 -->
    <tr>
        <td colspan="{{ 2 + count($availableScores) * 5 }}" style="padding: 20px;"></td>
    </tr>
@endforeach
