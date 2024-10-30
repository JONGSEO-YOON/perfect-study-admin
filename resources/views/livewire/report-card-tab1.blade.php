<div class="w-full">
    <div class="overflow-x-auto rounded-b-[0.8rem]">
        <table class="min-w-full bg-white">
            <thead>
                <tr class="bg-gray-100 text-center">
                    <th colspan="2" class="px-1.5 py-3  text-sm font-semibold text-gray-700 border-b">7월1주차
                    </th>
                    <th class="px-1.5 py-3  text-sm font-semibold text-gray-700 border-b">A반(숙제)
                    </th>
                    <th colspan="3" class="px-1.5 py-3  text-sm font-semibold text-gray-700 border-b">레벨1
                    </th>
                    <th colspan="3" class="px-1.5 py-3  text-sm font-semibold text-gray-700 border-b">레벨2
                    </th>
                    <th colspan="3" class="px-1.5 py-3  text-sm font-semibold text-gray-700 border-b">레벨3
                    </th>
                    <th colspan="3" class="px-1.5 py-3  text-sm font-semibold text-gray-700 border-b">레벨4
                    </th>
                    <th colspan="3" class="px-1.5 py-3  text-sm font-semibold text-gray-700 border-b">레벨5
                    </th>
                </tr>
                <tr class="bg-gray-50 text-center text-xs text-gray-700">
                    <th class="px-1 py-3 border-b">대단원</th>
                    <th class="px-1 py-3 border-b">중단원</th>
                    <th class="px-1 py-3 border-b">문제유형</th>
                    <th class="px-1 py-3 border-b">출제문항</th>
                    <th class="px-1 py-3 border-b">정답개수</th>
                    <th class="px-1 py-3 border-b">정답률</th>
                    <th class="px-1 py-3 border-b">출제문항</th>
                    <th class="px-1 py-3 border-b">정답개수</th>
                    <th class="px-1 py-3 border-b">정답률</th>
                    <th class="px-1 py-3 border-b">출제문항</th>
                    <th class="px-1 py-3 border-b">정답개수</th>
                    <th class="px-1 py-3 border-b">정답률</th>
                    <th class="px-1 py-3 border-b">출제문항</th>
                    <th class="px-1 py-3 border-b">정답개수</th>
                    <th class="px-1 py-3 border-b">정답률</th>
                    <th class="px-1 py-3 border-b">출제문항</th>
                    <th class="px-1 py-3 border-b">정답개수</th>
                    <th class="px-1 py-3 border-b">정답률</th>
                </tr>
            </thead>
            <tbody>
                <tr class="hover:bg-gray-50 text-sm text-gray-900 text-center">
                    <td rowspan="7" class="px-1 py-3 border-b">공통수학</td>
                    <td rowspan="7" class="px-1 py-3 border-b">다항식과연산</td>
                    <td class="px-1 py-3 border-b">거듭제곱근 계산</td>
                    <td class="px-1 py-3 border-b">120</td>
                    <td class="px-1 py-3 border-b">110</td>
                    <td class="px-1 py-3 border-b">110</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                </tr>
                @foreach (['거듭제곱근 성질', '지수의 성질', '지수의 연산', '로그의 연산', '로그의 성질', '수학적귀납법'] as $item)
                    <tr class="hover:bg-gray-50 text-sm text-gray-900 text-center">
                        <td class="px-1 py-3 border-b">{{ $item }}</td>
                        <td class="px-1 py-3 border-b">120</td>
                        <td class="px-1 py-3 border-b">110</td>
                        <td class="px-1 py-3 border-b">110</td>
                        <td class="px-1 py-3 border-b">190</td>
                        <td class="px-1 py-3 border-b">190</td>
                        <td class="px-1 py-3 border-b">190</td>
                        <td class="px-1 py-3 border-b">190</td>
                        <td class="px-1 py-3 border-b">190</td>
                        <td class="px-1 py-3 border-b">190</td>
                        <td class="px-1 py-3 border-b">190</td>
                        <td class="px-1 py-3 border-b">190</td>
                        <td class="px-1 py-3 border-b">190</td>
                        <td class="px-1 py-3 border-b">190</td>
                        <td class="px-1 py-3 border-b">190</td>
                        <td class="px-1 py-3 border-b">190</td>
                    </tr>
                @endforeach
                <tr class="hover:bg-gray-50 text-sm text-gray-900 text-center">
                    <td rowspan="2" colspan="2" class="px-1 py-3 border-b">단원별 전체 정답률</td>
                    <td class="px-1 py-3 border-b">개인</td>
                    <td class="px-1 py-3 border-b">120</td>
                    <td class="px-1 py-3 border-b">110</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">120</td>
                </tr>
                <tr class="hover:bg-gray-50 text-sm text-gray-900 text-center">
                    <td class="px-1 py-3 border-b">반별</td>
                    <td class="px-1 py-3 border-b">120</td>
                    <td class="px-1 py-3 border-b">110</td>
                    <td class="px-1 py-3 border-b">110</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                </tr>
            </tbody>
        </table>
        <table class="min-w-full bg-white">
            <thead>
                <tr class="bg-gray-100 text-center">
                    <th colspan="2" class="px-1.5 py-3  text-sm font-semibold text-gray-700 border-b">7월1주차
                    </th>
                    <th class="px-1.5 py-3  text-sm font-semibold text-gray-700 border-b">A반(일일테스트)
                    </th>
                    <th colspan="3" class="px-1.5 py-3  text-sm font-semibold text-gray-700 border-b">레벨1
                    </th>
                    <th colspan="3" class="px-1.5 py-3  text-sm font-semibold text-gray-700 border-b">레벨2
                    </th>
                    <th colspan="3" class="px-1.5 py-3  text-sm font-semibold text-gray-700 border-b">레벨3
                    </th>
                    <th colspan="3" class="px-1.5 py-3  text-sm font-semibold text-gray-700 border-b">레벨4
                    </th>
                    <th colspan="3" class="px-1.5 py-3  text-sm font-semibold text-gray-700 border-b">레벨5
                    </th>
                </tr>
                <tr class="bg-gray-50 text-center text-xs text-gray-700">
                    <th class="px-1 py-3 border-b">대단원</th>
                    <th class="px-1 py-3 border-b">중단원</th>
                    <th class="px-1 py-3 border-b">문제유형</th>
                    <th class="px-1 py-3 border-b">출제문항</th>
                    <th class="px-1 py-3 border-b">정답개수</th>
                    <th class="px-1 py-3 border-b">정답률</th>
                    <th class="px-1 py-3 border-b">출제문항</th>
                    <th class="px-1 py-3 border-b">정답개수</th>
                    <th class="px-1 py-3 border-b">정답률</th>
                    <th class="px-1 py-3 border-b">출제문항</th>
                    <th class="px-1 py-3 border-b">정답개수</th>
                    <th class="px-1 py-3 border-b">정답률</th>
                    <th class="px-1 py-3 border-b">출제문항</th>
                    <th class="px-1 py-3 border-b">정답개수</th>
                    <th class="px-1 py-3 border-b">정답률</th>
                    <th class="px-1 py-3 border-b">출제문항</th>
                    <th class="px-1 py-3 border-b">정답개수</th>
                    <th class="px-1 py-3 border-b">정답률</th>
                </tr>
            </thead>
            <tbody>
                <tr class="hover:bg-gray-50 text-sm text-gray-900 text-center">
                    <td rowspan="7" class="px-1 py-3 border-b">공통수학</td>
                    <td rowspan="7" class="px-1 py-3 border-b">다항식과연산</td>
                    <td class="px-1 py-3 border-b">거듭제곱근 계산</td>
                    <td class="px-1 py-3 border-b">120</td>
                    <td class="px-1 py-3 border-b">110</td>
                    <td class="px-1 py-3 border-b">110</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                </tr>
                @foreach (['거듭제곱근 성질', '지수의 성질', '지수의 연산', '로그의 연산', '로그의 성질', '수학적귀납법'] as $item)
                    <tr class="hover:bg-gray-50 text-sm text-gray-900 text-center">
                        <td class="px-1 py-3 border-b">{{ $item }}</td>
                        <td class="px-1 py-3 border-b">120</td>
                        <td class="px-1 py-3 border-b">110</td>
                        <td class="px-1 py-3 border-b">110</td>
                        <td class="px-1 py-3 border-b">190</td>
                        <td class="px-1 py-3 border-b">190</td>
                        <td class="px-1 py-3 border-b">190</td>
                        <td class="px-1 py-3 border-b">190</td>
                        <td class="px-1 py-3 border-b">190</td>
                        <td class="px-1 py-3 border-b">190</td>
                        <td class="px-1 py-3 border-b">190</td>
                        <td class="px-1 py-3 border-b">190</td>
                        <td class="px-1 py-3 border-b">190</td>
                        <td class="px-1 py-3 border-b">190</td>
                        <td class="px-1 py-3 border-b">190</td>
                        <td class="px-1 py-3 border-b">190</td>
                    </tr>
                @endforeach
                <tr class="hover:bg-gray-50 text-sm text-gray-900 text-center">
                    <td rowspan="2" colspan="2" class="px-1 py-3 border-b">단원별 전체 정답률</td>
                    <td class="px-1 py-3 border-b">개인</td>
                    <td class="px-1 py-3 border-b">120</td>
                    <td class="px-1 py-3 border-b">110</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">120</td>
                </tr>
                <tr class="hover:bg-gray-50 text-sm text-gray-900 text-center">
                    <td class="px-1 py-3 border-b">반별</td>
                    <td class="px-1 py-3 border-b">120</td>
                    <td class="px-1 py-3 border-b">110</td>
                    <td class="px-1 py-3 border-b">110</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                    <td class="px-1 py-3 border-b">190</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>
