<div class="w-full">
    <div class="overflow-x-auto rounded-b-[0.8rem]">
        <table class="min-w-full bg-white">
            <thead>
                <tr class="bg-gray-100 text-center">
                    <th class="px-1.5 py-3  text-sm font-semibold text-gray-700 border-b">7월1주차
                    </th>
                    <th class="px-1.5 py-3  text-sm font-semibold text-gray-700 border-b">A반(숙제)
                    </th>
                    <th colspan="3" class="px-1.5 py-3  text-sm font-semibold text-gray-700 border-b">2점
                    </th>
                    <th colspan="3" class="px-1.5 py-3  text-sm font-semibold text-gray-700 border-b">3점
                    </th>
                    <th colspan="3" class="px-1.5 py-3  text-sm font-semibold text-gray-700 border-b">4점
                    </th>
                </tr>
                <tr class="bg-gray-50 text-center text-xs text-gray-700">
                    <th class="px-1 py-3 border-b">과목</th>
                    <th class="px-1 py-3 border-b">단원</th>
                    <th class="px-1 py-3 border-b">레벨1</th>
                    <th class="px-1 py-3 border-b">레벨2</th>
                    <th class="px-1 py-3 border-b">레벨3</th>
                    <th class="px-1 py-3 border-b">레벨1</th>
                    <th class="px-1 py-3 border-b">레벨2</th>
                    <th class="px-1 py-3 border-b">레벨3</th>
                    <th class="px-1 py-3 border-b">레벨1</th>
                    <th class="px-1 py-3 border-b">레벨2</th>
                    <th class="px-1 py-3 border-b">레벨3</th>
                </tr>
            </thead>
            <tbody>
                <tr class="hover:bg-gray-50 text-sm text-gray-900 text-center">
                    <td rowspan="7" class="px-1 py-3 border-b">수2</td>
                    <td class="px-1 py-3 border-b">다항식과연산</td>
                    <td class="px-1 py-3 border-b">120</td>
                    <td class="px-1 py-3 border-b">110</td>
                    <td class="px-1 py-3 border-b">110</td>
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
                    </tr>
                @endforeach

            </tbody>
        </table>
        <table class="min-w-full bg-white">
            <thead>
                <tr class="bg-gray-100 text-center">
                    <th class="px-1.5 py-3  text-sm font-semibold text-gray-700 border-b">7월1주차
                    </th>
                    <th class="px-1.5 py-3  text-sm font-semibold text-gray-700 border-b">A반(일일테스트)
                    </th>
                    <th colspan="3" class="px-1.5 py-3  text-sm font-semibold text-gray-700 border-b">2점
                    </th>
                    <th colspan="3" class="px-1.5 py-3  text-sm font-semibold text-gray-700 border-b">3점
                    </th>
                    <th colspan="3" class="px-1.5 py-3  text-sm font-semibold text-gray-700 border-b">4점
                    </th>
                </tr>
                <tr class="bg-gray-50 text-center text-xs text-gray-700">
                    <th class="px-1 py-3 border-b">과목</th>
                    <th class="px-1 py-3 border-b">단원</th>
                    <th class="px-1 py-3 border-b">레벨1</th>
                    <th class="px-1 py-3 border-b">레벨2</th>
                    <th class="px-1 py-3 border-b">레벨3</th>
                    <th class="px-1 py-3 border-b">레벨1</th>
                    <th class="px-1 py-3 border-b">레벨2</th>
                    <th class="px-1 py-3 border-b">레벨3</th>
                    <th class="px-1 py-3 border-b">레벨1</th>
                    <th class="px-1 py-3 border-b">레벨2</th>
                    <th class="px-1 py-3 border-b">레벨3</th>
                </tr>
            </thead>
            <tbody>
                <tr class="hover:bg-gray-50 text-sm text-gray-900 text-center">
                    <td rowspan="7" class="px-1 py-3 border-b">수2</td>
                    <td class="px-1 py-3 border-b">다항식과연산</td>
                    <td class="px-1 py-3 border-b">120</td>
                    <td class="px-1 py-3 border-b">110</td>
                    <td class="px-1 py-3 border-b">110</td>
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
                    </tr>
                @endforeach

            </tbody>
        </table>
    </div>

</div>
