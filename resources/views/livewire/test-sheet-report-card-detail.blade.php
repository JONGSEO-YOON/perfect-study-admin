<div class="w-full">
    <div class="overflow-x-auto  flex flex-col items-start gap-y-4">
        <table class="w-full bg-white border-x border-t">
            <thead>
                <tr class="bg-gray-100 text-center">
                    <th colspan="8" class="px-6 py-3 text-sm font-semibold text-gray-700 border-b">
                        2024년 1학기 중간고사
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr class="bg-gray-100 text-center">
                    <th class="px-6 py-3  text-sm font-semibold text-gray-700 border-b">
                        범위
                    </th>
                    <th class="px-6 py-3  text-sm font-semibold text-gray-700 border-b">
                        개인점수
                    </th>
                    <th class="px-6 py-3  text-sm font-semibold text-gray-700 border-b">
                        반평균
                    </th>
                    <th class="px-6 py-3  text-sm font-semibold text-gray-700 border-b">
                        레벨평균
                    </th>
                    <th class="px-6 py-3  text-sm font-semibold text-gray-700 border-b">
                        반별 등수
                    </th>
                </tr>
                @for ($i = 1; $i <= 4; $i++)
                    <tr class="hover:bg-gray-50 text-sm text-gray-900 text-center">
                        <td class="px-6 py-3 border-b">다항식~인수분해</td>
                        <td class="px-6 py-3 border-b">
                            7/14
                            (50%)
                        </td>
                        <td class="px-6 py-3 border-b">
                            7/14
                            (50%)
                        </td>
                        <td class="px-6 py-3 border-b">
                            7/14
                            (50%)
                        </td>
                        <td class="px-6 py-3 border-b">
                            7/14
                            (50%)
                        </td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>

</div>
