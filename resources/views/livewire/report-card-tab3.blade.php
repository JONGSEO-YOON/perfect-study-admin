<div class="w-full">
    <div class="overflow-x-auto rounded-b-[0.8rem] flex flex-col items-start gap-y-4">
        <table class="w-full bg-white border-r">
            <thead>
                <tr class="bg-gray-100 text-center">
                    <th colspan="8" class="px-6 py-3 text-sm font-semibold text-gray-700 border-b">
                        주간테스트
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr class="bg-gray-100 text-center">
                    <td rowspan="5" class="px-6 py-3  text-sm font-semibold text-gray-700 border-b border-r">
                        7월1주차
                    </td>
                    <th class="px-6 py-3  text-sm font-semibold text-gray-700 border-b">
                        테스트
                    </th>
                    <th class="px-6 py-3  text-sm font-semibold text-gray-700 border-b">
                        범위
                    </th>
                    <th class="px-6 py-3  text-sm font-semibold text-gray-700 border-b">
                        날짜
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
                        {{-- <td class="px-6 py-3 border-b">7월3일</td> --}}
                        @if ($i % 2 == 1)
                            <td rowspan="2" class="px-6 py-3 border-b border-r">일일테스트</td>
                        @endif
                        <td class="px-6 py-3 border-b">다항식~인수분해</td>
                        <td class="px-6 py-3 border-b">
                            7월 8일
                        </td>
                        <td class="px-6 py-3 border-b">
                            60%
                        </td>
                        <td class="px-6 py-3 border-b">
                            60%
                        </td>
                        <td class="px-6 py-3 border-b">
                            60%
                        </td>
                        <td class="px-6 py-3 border-b">
                            60%
                        </td>
                    </tr>
                @endfor
            </tbody>
        </table>
        <table class="min-w-fit bg-white border-r  border-t">
            <thead>
                <tr class="bg-gray-100 text-center">
                    <th colspan="2" class="px-6 py-3  text-sm font-semibold text-gray-700 border-b">
                        7월 1주차 출결
                    </th>
                    <th class="px-6 py-3  text-sm font-semibold text-gray-700 border-b">
                        지각, 결석 사유
                    </th>
                    <th class="px-6 py-3  text-sm font-semibold text-gray-700 border-b">
                        비고
                    </th>
                </tr>

            </thead>
            <tbody>
                @for ($i = 0; $i <= 4; $i++)
                    <tr class="hover:bg-gray-50 text-sm text-gray-900 text-center">
                        <td class="px-6 py-3 border-b">7월3일</td>
                        <td class="px-6 py-3 border-b">정규등원(출석)</td>
                        <td class="px-6 py-3 border-b">
                            <div class="w-[150px]">
                            </div>
                        </td>
                        <td class="px-6 py-3 border-b">
                            <div class="w-[150px]">
                            </div>
                        </td>
                    </tr>
                @endfor
            </tbody>
        </table>
        <table class="min-w-fit bg-white border-r border-t">
            <thead>
                <tr class="bg-gray-100 text-center">
                    <th colspan="5" class="px-6 py-3 text-sm font-semibold text-gray-700 border-b">
                        7월 1주차 숙제 이행도
                    </th>
                </tr>
                <tr class="bg-gray-100 text-center">
                    <th class="px-6 py-3  text-sm font-semibold text-gray-700 border-b">
                        날짜
                    </th>
                    <th class="px-6 py-3  text-sm font-semibold text-gray-700 border-b">
                        범위
                    </th>
                    <th class="px-6 py-3  text-sm font-semibold text-gray-700 border-b">
                        전체개수
                    </th>
                    <th class="px-6 py-3  text-sm font-semibold text-gray-700 border-b">
                        이행도
                    </th>
                    <th class="px-6 py-3  text-sm font-semibold text-gray-700 border-b">
                        정답률
                    </th>
                </tr>
            </thead>
            <tbody>
                @for ($i = 0; $i <= 2; $i++)
                    <tr class="hover:bg-gray-50 text-sm text-gray-900 text-center">
                        <td class="px-6 py-3 border-b">7월3일</td>
                        <td class="px-6 py-3 border-b">다항식~인수분해</td>
                        <td class="px-6 py-3 border-b">
                            30/50
                        </td>
                        <td class="px-6 py-3 border-b">
                            60%
                        </td>
                        <td class="px-6 py-3 border-b">
                            60%
                        </td>
                    </tr>
                @endfor
            </tbody>
        </table>
        <div class="p-4 w-1/2">
            <div data-field-wrapper="" class="fi-fo-field-wrp">
                <div class="grid gap-y-2">
                    <div class="flex items-center gap-x-3 justify-between ">
                        <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                강사 코멘트
                            </span>
                        </label>
                    </div>
                    <div class="grid auto-cols-fr gap-y-2">
                        <div
                            class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 [&amp;:not(:has(.fi-ac-action:focus))]:focus-within:ring-2 ring-gray-950/10 dark:ring-white/20 [&amp;:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-600 dark:[&amp;:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-500 fi-fo-textarea overflow-hidden">
                            <div class="min-w-0 flex-1">
                                <div wire:ignore.self="" style="height: '5rem'">
                                    <textarea ax-load="" ax-load-src="http://localhost/js/filament/forms/components/textarea.js?v=3.2.110.0"
                                        class="block h-full w-full border-none bg-transparent px-3 py-1.5 text-base text-gray-950 placeholder:text-gray-400 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] dark:disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.500)] sm:text-sm sm:leading-6"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>

</div>
