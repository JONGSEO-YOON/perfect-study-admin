<table style="width: 100%; border-collapse: collapse; ">
    <tr>
        <td colspan="8"
            style="text-align:left; height: 50px; vertical-align: middle; padding: 15px; font-weight: bold; font-size: 20px; ">
            &nbsp;&nbsp; &nbsp; 주간 학습표
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>

</table>

@foreach ($weeklyReports as $weekReport)
    {{-- 주차 헤더 --}}
    <table style="width: 100%; border-collapse: collapse; ">
        <tr>
            <td colspan="8"
                style="text-align:center; height: 40px; vertical-align: middle; padding: 15px; font-weight: bold; font-size: 14px; background: #eeeeee; border: 1px solid #ddd; ">
                {{ $weekReport['week_label'] }} ({{ $weekReport['week_range'] }})
            </td>
        </tr>
    </table>

    {{-- 1. 출결 테이블 --}}
    <table style="width: 100%; border-collapse: collapse; border: 2px solid black">
        <thead>
            <tr>
                <th colspan="4"
                    style="text-align:center; font-weight:bold; background: #eeeeee; height: 26px; vertical-align: middle; padding: 12px; border: 1px solid #ddd; ">
                    출결현황
                </th>
            </tr>
            <tr>
                <th
                    style="text-align:center; height: 26px; vertical-align: middle; padding: 12px; border: 1px solid #ddd; width: 180px;">
                    날짜</th>
                <th
                    style="text-align:center; height: 26px; vertical-align: middle; padding: 12px; border: 1px solid #ddd;width: 200px;">
                    출결</th>
                <th
                    style="text-align:center; height: 26px; vertical-align: middle; padding: 12px; border: 1px solid #ddd; width: 150px;">
                    지각, 결석
                    사유</th>
                <th
                    style="text-align:center; height: 26px; vertical-align: middle; padding: 12px; border: 1px solid #ddd; width: 120px;">
                    비고</th>
            </tr>
        </thead>
        <tbody>
            @if ($weekReport['attendance_report'])
                @foreach ($weekReport['attendance_report'] as $attendance)
                    <tr>
                        <td
                            style="text-align:center; height: 26px; vertical-align: middle; padding: 10px; border: 1px solid #ddd;">
                            {{ Carbon\Carbon::parse($attendance['date'])->format('m월d일') }}
                        </td>
                        <td
                            style="text-align:center; height: 26px; vertical-align: middle; padding: 10px; border: 1px solid #ddd;">
                            {{ $attendance['attendance'] }}</td>
                        <td
                            style="text-align:center; height: 26px; vertical-align: middle; padding: 10px; border: 1px solid #ddd;">
                            {{ $attendance['memo1'] }}</td>
                        <td
                            style="text-align:center; height: 26px; vertical-align: middle; padding: 10px; border: 1px solid #ddd;">
                            {{ $attendance['memo2'] }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4"
                        style="text-align:center; height: 26px; vertical-align: middle; padding: 10px; border: 1px solid #ddd;">
                        출결 기록이 없습니다.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    {{-- 2. 주간 숙제 테이블 --}}
    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr>
                <th colspan="5"
                    style="text-align:center; font-weight:bold; background: #eeeeee; height: 26px; vertical-align: middle; padding: 12px; border: 1px solid #ddd; ">
                    주간 숙제
                </th>
            </tr>
            <tr>
                <th
                    style="text-align:center; height: 26px; vertical-align: middle; padding: 12px; border: 1px solid #ddd;">
                    숙제</th>
                <th
                    style="text-align:center; height: 26px; vertical-align: middle; padding: 12px; border: 1px solid #ddd;">
                    범위</th>
                <th
                    style="text-align:center; height: 26px; vertical-align: middle; padding: 12px; border: 1px solid #ddd;">
                    문제 수</th>
                <th
                    style="text-align:center; height: 26px; vertical-align: middle; padding: 12px; border: 1px solid #ddd;">
                    이행도</th>
                <th
                    style="text-align:center; height: 26px; vertical-align: middle; padding: 12px; border: 1px solid #ddd;">
                    정답률</th>
            </tr>
        </thead>
        <tbody>
            @if ($weekReport['homework_report'])
                @foreach ($weekReport['homework_report'] as $homework)
                    @foreach ($homework['by_types'] as $type)
                        <tr>
                            @if ($loop->first)
                                <td rowspan="{{ count($homework['by_types']) + 1 }}"
                                    style="text-align:center; height: 26px; vertical-align: middle; padding: 10px; border: 1px solid #ddd; ">
                                    {{ Carbon\Carbon::parse($homework['date'])->format('m월d일') }}
                                    {{ $homework['name'] }}
                                </td>
                            @endif
                            <td
                                style="text-align:center; height: 26px; vertical-align: middle; padding: 10px; border: 1px solid #ddd;">
                                {!! $cleanMathML($type['name']) !!}
                            </td>
                            <td
                                style="text-align:center; height: 26px; vertical-align: middle; padding: 10px; border: 1px solid #ddd;">
                                {{ $type['correct_count'] }}/{{ $type['total_count'] }}</td>
                            <td
                                style="text-align:center; height: 26px; vertical-align: middle; padding: 10px; border: 1px solid #ddd;">
                                {{ number_format($type['attempt_rate'], 1) }}%</td>
                            <td
                                style="text-align:center; height: 26px; vertical-align: middle; padding: 10px; border: 1px solid #ddd;">
                                {{ number_format($type['correct_rate'], 1) }}%</td>
                        </tr>
                    @endforeach
                    <tr style="">
                        <td
                            style="text-align:center; font-weight:bold; height: 26px; vertical-align: middle; padding: 10px; border: 1px solid #ddd;">
                            전체</td>
                        <td
                            style="text-align:center; height: 26px; vertical-align: middle; padding: 10px; border: 1px solid #ddd;">
                            {{ $homework['total']['correct_count'] }}/{{ $homework['total']['total_count'] }}
                        </td>
                        <td
                            style="text-align:center; height: 26px; vertical-align: middle; padding: 10px; border: 1px solid #ddd;">
                            {{ number_format($homework['total']['attempt_rate'], 1) }}%</td>
                        <td
                            style="text-align:center; height: 26px; vertical-align: middle; padding: 10px; border: 1px solid #ddd;">
                            {{ number_format($homework['total']['correct_rate'], 1) }}%</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="5"
                        style="text-align:center; height: 26px; vertical-align: middle; padding: 10px; border: 1px solid #ddd;">
                        이번 주 숙제가 없습니다.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    {{-- 3. 주간 테스트 테이블 --}}
    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr>
                <th colspan="8"
                    style="text-align:center; font-weight:bold; background: #eeeeee; height: 26px; vertical-align: middle; padding: 12px; border: 1px solid #ddd; ">
                    주간 테스트
                </th>
            </tr>
            <tr>
                <th
                    style="text-align:center; height: 26px; vertical-align: middle; padding: 12px; border: 1px solid #ddd;">
                    테스트</th>
                <th
                    style="text-align:center; height: 26px; vertical-align: middle; padding: 12px; border: 1px solid #ddd;">
                    범위</th>
                <th
                    style="text-align:center; height: 26px; vertical-align: middle; padding: 12px; border: 1px solid #ddd;">
                    개인점수</th>
                <th
                    style="text-align:center; height: 26px; vertical-align: middle; padding: 12px; border: 1px solid #ddd;">
                    반평균</th>
                <th
                    style="text-align:center; height: 26px; vertical-align: middle; padding: 12px; border: 1px solid #ddd;">
                    레벨평균</th>
                <th
                    style="text-align:center; height: 26px; vertical-align: middle; padding: 12px; border: 1px solid #ddd;">
                    반별등수</th>
                <th
                    style="text-align:center; height: 26px; vertical-align: middle; padding: 12px; border: 1px solid #ddd;">
                    학년평균</th>
                <th
                    style="text-align:center; height: 26px; vertical-align: middle; padding: 12px; border: 1px solid #ddd;">
                    학년등수</th>
            </tr>
        </thead>
        <tbody>
            @if ($weekReport['test_report'])
                @foreach ($weekReport['test_report'] as $test)
                    @foreach ($test['by_types'] as $type)
                        <tr>
                            @if ($loop->first)
                                <td rowspan="{{ count($test['by_types']) + 1 }}"
                                    style="text-align:center; height: 26px; vertical-align: middle; padding: 10px; border: 1px solid #ddd; ">
                                    {{ Carbon\Carbon::parse($test['date'])->format('m월d일') }}
                                    {{ $test['name'] }}
                                </td>
                            @endif
                            <td
                                style="text-align:center; height: 26px; vertical-align: middle; padding: 10px; border: 1px solid #ddd;">
                                {!! $cleanMathML($type['name']) !!}
                            </td>
                            <td
                                style="text-align:center; height: 26px; vertical-align: middle; padding: 10px; border: 1px solid #ddd;">
                                {{ number_format($type['scores']['personal_score']) }}</td>
                            <td
                                style="text-align:center; height: 26px; vertical-align: middle; padding: 10px; border: 1px solid #ddd;">
                                {{ number_format($type['scores']['classroom_average']) }}</td>
                            <td
                                style="text-align:center; height: 26px; vertical-align: middle; padding: 10px; border: 1px solid #ddd;">
                                {{ number_format($type['scores']['level_average']) }}</td>
                            <td
                                style="text-align:center; height: 26px; vertical-align: middle; padding: 10px; border: 1px solid #ddd;">
                                {{ $type['scores']['classroom_rank'] }}등
                                ({{ $type['scores']['classroom_students_count'] ?? 0 }})
                            </td>
                            <td
                                style="text-align:center; height: 26px; vertical-align: middle; padding: 10px; border: 1px solid #ddd;">
                                {{ number_format($type['scores']['grade_average'] ?? 0) }}</td>
                            <td
                                style="text-align:center; height: 26px; vertical-align: middle; padding: 10px; border: 1px solid #ddd;">
                                {{ $type['scores']['grade_rank'] ?? 0 }}등
                                ({{ $type['scores']['grade_students_count'] ?? 0 }})</td>
                        </tr>
                    @endforeach
                    <tr style="">
                        <td
                            style="text-align:center; font-weight:bold; height: 26px; vertical-align: middle; padding: 10px; border: 1px solid #ddd;">
                            전체</td>
                        <td
                            style="text-align:center; height: 26px; vertical-align: middle; padding: 10px; border: 1px solid #ddd;">
                            {{ number_format($test['total']['personal_score']) }}</td>
                        <td
                            style="text-align:center; height: 26px; vertical-align: middle; padding: 10px; border: 1px solid #ddd;">
                            {{ number_format($test['total']['classroom_average']) }}</td>
                        <td
                            style="text-align:center; height: 26px; vertical-align: middle; padding: 10px; border: 1px solid #ddd;">
                            {{ number_format($test['total']['level_average']) }}</td>
                        <td
                            style="text-align:center; height: 26px; vertical-align: middle; padding: 10px; border: 1px solid #ddd;">
                            {{ $test['total']['classroom_rank'] }}등
                            ({{ $test['total']['classroom_students_count'] ?? 0 }})</td>
                        <td
                            style="text-align:center; height: 26px; vertical-align: middle; padding: 10px; border: 1px solid #ddd;">
                            {{ number_format($test['total']['grade_average'] ?? 0) }}</td>
                        <td
                            style="text-align:center; height: 26px; vertical-align: middle; padding: 10px; border: 1px solid #ddd;">
                            {{ $test['total']['grade_rank'] ?? 0 }}등
                            ({{ $test['total']['grade_students_count'] ?? 0 }})</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="8"
                        style="text-align:center; height: 26px; vertical-align: middle; padding: 10px; border: 1px solid #ddd;">
                        이번 주 테스트가 없습니다.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
    {{-- 4. 강사 코멘트 --}}
    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr>
                <th colspan="5"
                    style="text-align:center; font-weight:bold; background: #eeeeee; height: 26px; vertical-align: middle; padding: 12px; border: 1px solid #ddd; ">
                    강사 코멘트
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="5" style="padding: 15px; border: 1px solid #ddd; height: 100px; vertical-align:top">
                    {{ $weekReport['comment_report'] ?: '이번 주 코멘트가 없습니다.' }}
                </td>
            </tr>
        </tbody>
    </table>

    {{-- 주차 간 간격 --}}
    <tr>
        <td style="padding: 40px;"></td>
    </tr>
    <tr>
        <td style="padding: 40px;"></td>
    </tr>
@endforeach
