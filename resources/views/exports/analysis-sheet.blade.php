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
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th colspan="2"
                    style="text-align:center; height:26px; background: #eeeeee; font-weight:bold; vertical-align:center; padding: 12px; border: 1px solid #ddd;">
                    {{ $report['date'] }}
                </th>
                <th
                    style="text-align:center; height:26px; background: #eeeeee; font-weight:bold; vertical-align:center; padding: 12px; border: 1px solid #ddd; width:230px;">
                    {{ $report['name'] }}
                </th>
                @for ($level = 1; $level <= 5; $level++)
                    <th colspan="3"
                        style="text-align:center; height:26px; background: #eeeeee; font-weight:bold; vertical-align:center; padding: 12px; border: 1px solid #ddd;">
                        레벨{{ $level }}
                    </th>
                @endfor
            </tr>
            <tr>
                <th
                    style="text-align:center; height: 26px; vertical-align:center; padding: 12px; border: 1px solid #ddd;  width:120px;">
                    대단원</th>
                <th
                    style="text-align:center; height: 26px; vertical-align:center; padding: 12px; border: 1px solid #ddd;  width:120px;">
                    중단원</th>
                <th
                    style="text-align:center; height: 26px; vertical-align:center; padding: 12px; border: 1px solid #ddd;  width:230px;">
                    문제유형</th>
                @for ($level = 1; $level <= 5; $level++)
                    <th
                        style="text-align:center; height: 26px; vertical-align:center; padding: 12px; border: 1px solid #ddd;">
                        출제문항</th>
                    <th
                        style="text-align:center; height: 26px; vertical-align:center; padding: 12px; border: 1px solid #ddd;">
                        정답개수</th>
                    <th
                        style="text-align:center; height: 26px; vertical-align:center; padding: 12px; border: 1px solid #ddd;">
                        정답률</th>
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

            @foreach ($report['hierarchy'] as $index => $item)
                <tr>
                    @if ($currentMajor !== $item['major'])
                        @php
                            $currentMajor = $item['major'];
                            $majorRowspan = count(
                                array_filter($report['hierarchy'], fn($h) => $h['major'] === $currentMajor),
                            );
                        @endphp
                        <td rowspan="{{ $majorRowspan }}"
                            style="text-align:center; height: 26px; vertical-align:center; padding: 10px; border: 1px solid #ddd;">
                            {!! $cleanMathML($item['major']) !!}
                        </td>
                    @endif

                    @if ($currentMiddle !== $item['middle'])
                        @php
                            $currentMiddle = $item['middle'];
                            $middleRowspan = count(
                                array_filter($report['hierarchy'], fn($h) => $h['middle'] === $currentMiddle),
                            );
                        @endphp
                        <td rowspan="{{ $middleRowspan }}"
                            style="text-align:center; height: 26px; vertical-align:center; padding: 10px; border: 1px solid #ddd;">
                            {!! $cleanMathML($item['middle']) !!}
                        </td>
                    @endif

                    <td
                        style="text-align:center; height: 26px; vertical-align:center; padding: 10px; border: 1px solid #ddd;">
                        {!! $cleanMathML($item['type']) !!}</td>

                    @for ($level = 1; $level <= 5; $level++)
                        <td
                            style="text-align:center; height: 26px; vertical-align:center; padding: 10px; border: 1px solid #ddd;">
                            {{ $item['levels'][$level]['total'] ?? 0 }}</td>
                        <td
                            style="text-align:center; height: 26px; vertical-align:center; padding: 10px; border: 1px solid #ddd;">
                            {{ $item['levels'][$level]['correct'] ?? 0 }}</td>
                        <td
                            style="text-align:center; height: 26px; vertical-align:center; padding: 10px; border: 1px solid #ddd;">
                            {{ $item['levels'][$level]['percentage'] ?? 0 }}%</td>
                    @endfor
                </tr>
            @endforeach

            <!-- 개인별 전체 정답률 -->
            <tr>
                <td rowspan="2" colspan="2"
                    style="text-align:center; height: 26px; vertical-align:center; padding: 10px; border: 1px solid #ddd; background-color: #f8f9fa;">
                    단원별 전체 정답률
                </td>
                <td
                    style="text-align:center; height: 26px; vertical-align:center; padding: 10px; border: 1px solid #ddd; background-color: #f8f9fa;">
                    개인
                </td>
                @for ($level = 1; $level <= 5; $level++)
                    <td
                        style="text-align:center; height: 26px; vertical-align:center; padding: 10px; border: 1px solid #ddd; background-color: #f8f9fa;">
                        {{ $report['personal_level'][$level]['total'] ?? 0 }}
                    </td>
                    <td
                        style="text-align:center; height: 26px; vertical-align:center; padding: 10px; border: 1px solid #ddd; background-color: #f8f9fa;">
                        {{ $report['personal_level'][$level]['correct'] ?? 0 }}
                    </td>
                    <td
                        style="text-align:center; height: 26px; vertical-align:center; padding: 10px; border: 1px solid #ddd; background-color: #f8f9fa;">
                        {{ $report['personal_level'][$level]['percentage'] ?? 0 }}%
                    </td>
                @endfor
            </tr>

            <!-- 반별 전체 정답률 -->
            <tr>
                <td
                    style="text-align:center; height: 26px; vertical-align:center; padding: 10px; border: 1px solid #ddd; background-color: #f8f9fa;">
                    반별
                </td>
                @for ($level = 1; $level <= 5; $level++)
                    <td
                        style="text-align:center; height: 26px; vertical-align:center; padding: 10px; border: 1px solid #ddd; background-color: #f8f9fa;">
                        {{ $report['classroom_level'][$level]['total'] ?? 0 }}
                    </td>
                    <td
                        style="text-align:center; height: 26px; vertical-align:center; padding: 10px; border: 1px solid #ddd; background-color: #f8f9fa;">
                        {{ round($report['classroom_level'][$level]['total'] * ($report['classroom_level'][$level]['percentage'] / 100), 1) ?? 0 }}
                    </td>
                    <td
                        style="text-align:center; height: 26px; vertical-align:center; padding: 10px; border: 1px solid #ddd; background-color: #f8f9fa;">
                        {{ $report['classroom_level'][$level]['percentage'] ?? 0 }}%
                    </td>
                @endfor
            </tr>
        </tbody>
    </table>

    <!-- 테이블 간 간격 -->
    <tr>
        <td colspan="17" style="padding: 20px;"></td>
    </tr>
@endforeach
