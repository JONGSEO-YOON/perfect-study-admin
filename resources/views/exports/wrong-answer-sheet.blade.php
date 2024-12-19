<table style="width: 100%; border-collapse: collapse; ">
    <tr>
        <td colspan="8"
            style="text-align:left; height: 50px; vertical-align: middle; padding: 15px; font-weight: bold; font-size: 20px; ">
            &nbsp;&nbsp; &nbsp; 오답 문풀 분석표
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
</table>

<table style="width: 100%; border-collapse: collapse;">


    @for ($i = 0; $i < $wrongReports->count(); $i += 2)
        {{-- 테이블 헤더 행 --}}
        <tr>
            {{-- 왼쪽 테이블 --}}
            <th colspan="3"
                style="text-align:center; font-weight:bold; background: #eeeeee;  vertical-align:middle; height:24px; padding: 12px; border: 1px solid #ddd; min-width: 500px;">
                {{ $wrongReports[$i]['date'] }} {{ $wrongReports[$i]['name'] }} 오답
            </th>
            <td style="width: 50px;"></td>

            {{-- 오른쪽 테이블 (없을 수 있음) --}}
            @if ($i + 1 < $wrongReports->count())
                <th colspan="3"
                    style="text-align:center; font-weight:bold; background: #eeeeee; vertical-align:middle; height:24px; padding: 12px; border: 1px solid #ddd; min-width: 500px;">
                    {{ $wrongReports[$i + 1]['date'] }} {{ $wrongReports[$i + 1]['name'] }} 오답
                </th>
            @else
                <td colspan="3"></td>
            @endif
        </tr>

        {{-- 컬럼 헤더 행 --}}
        <tr>
            <th
                style="text-align:center; vertical-align:middle; height:24px; padding: 12px; border: 1px solid #ddd; width:100px">
                문항번호</th>
            <th
                style="text-align:center; vertical-align:middle; height:24px; padding: 12px; border: 1px solid #ddd; width:100px">
                오답유사유형</th>
            <th
                style="text-align:center; vertical-align:middle; height:24px; padding: 12px; border: 1px solid #ddd; width:100px">
                오답테스트</th>
            <td style="width: 50px;"></td>

            @if ($i + 1 < $wrongReports->count())
                <th
                    style="text-align:center; vertical-align:middle; height:24px; padding: 12px; border: 1px solid #ddd; width:100px">
                    문항번호</th>
                <th
                    style="text-align:center; vertical-align:middle; height:24px; padding: 12px; border: 1px solid #ddd; width:100px">
                    오답유사유형</th>
                <th
                    style="text-align:center; vertical-align:middle; height:24px; padding: 12px; border: 1px solid #ddd; width:100px">
                    오답테스트</th>
            @else
                <td colspan="3"></td>
            @endif
        </tr>

        {{-- 데이터 행들 --}}
        @php
            $leftRows = count($wrongReports[$i]['report']);
            $rightRows = $i + 1 < $wrongReports->count() ? count($wrongReports[$i + 1]['report']) : 0;
            $maxRows = max(15, max($leftRows, $rightRows));
        @endphp

        @for ($row = 0; $row < $maxRows; $row++)
            <tr>
                {{-- 왼쪽 테이블 데이터 --}}
                @if ($row < $leftRows)
                    <td
                        style="text-align:center; vertical-align:middle; height:24px; padding: 10px; border: 1px solid #ddd; width:100px">
                        {{ $wrongReports[$i]['report'][$row]['original_seq'] }}번</td>
                    <td
                        style="text-align:center; vertical-align:middle; height:24px; padding: 10px; border: 1px solid #ddd; width:100px">
                        {{ $wrongReports[$i]['report'][$row]['first_retry_correct'] ? 'O' : 'X' }}</td>
                    <td
                        style="text-align:center; vertical-align:middle; height:24px; padding: 10px; border: 1px solid #ddd; width:100px">
                        @if ($wrongReports[$i]['report'][$row]['second_retry_correct'] === true)
                            O
                        @elseif($wrongReports[$i]['report'][$row]['second_retry_correct'] === false)
                            X
                        @else
                            -
                        @endif
                    </td>
                @else
                    <td
                        style="text-align:center; vertical-align:middle; height:24px; padding: 10px; border: 1px solid #ddd; width:100px">
                        &nbsp;</td>
                    <td
                        style="text-align:center; vertical-align:middle; height:24px; padding: 10px; border: 1px solid #ddd; width:100px">
                        &nbsp;</td>
                    <td
                        style="text-align:center; vertical-align:middle; height:24px; padding: 10px; border: 1px solid #ddd; width:100px">
                        &nbsp;</td>
                @endif
                <td style="width: 50px;"></td>

                {{-- 오른쪽 테이블 데이터 (없을 수 있음) --}}
                @if ($i + 1 < $wrongReports->count())
                    @if ($row < $rightRows)
                        <td
                            style="text-align:center; vertical-align:middle; height:24px; padding: 10px; border: 1px solid #ddd; width:100px">
                            {{ $wrongReports[$i + 1]['report'][$row]['original_seq'] }}번</td>
                        <td
                            style="text-align:center; vertical-align:middle; height:24px; padding: 10px; border: 1px solid #ddd; width:100px">
                            {{ $wrongReports[$i + 1]['report'][$row]['first_retry_correct'] ? 'O' : 'X' }}</td>
                        <td
                            style="text-align:center; vertical-align:middle; height:24px; padding: 10px; border: 1px solid #ddd; width:100px">
                            @if ($wrongReports[$i + 1]['report'][$row]['second_retry_correct'] === true)
                                O
                            @elseif($wrongReports[$i + 1]['report'][$row]['second_retry_correct'] === false)
                                X
                            @else
                                -
                            @endif
                        </td>
                    @else
                        <td
                            style="text-align:center; vertical-align:middle; height:24px; padding: 10px; border: 1px solid #ddd; width:100px">
                            &nbsp;
                        </td>
                        <td
                            style="text-align:center; vertical-align:middle; height:24px; padding: 10px; border: 1px solid #ddd; width:100px">
                            &nbsp;
                        </td>
                        <td
                            style="text-align:center; vertical-align:middle; height:24px; padding: 10px; border: 1px solid #ddd; width:100px">
                            &nbsp;
                        </td>
                    @endif
                @else
                    <td colspan="3"></td>
                @endif
            </tr>
        @endfor

        {{-- 테이블 간 여백 --}}
        <tr>
            <td colspan="8" style="padding: 20px;"></td>
        </tr>
    @endfor
</table>
