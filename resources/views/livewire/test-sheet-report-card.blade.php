<div class="w-full">
    <div class="overflow-x-auto flex flex-col items-start gap-y-4">
        <table class="w-full bg-white border-x border-t border-collapse">
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
                        등수
                    </th>
                    <th class="px-6 py-3  text-sm font-semibold text-gray-700 border-b">
                        학생명
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
                    <th class="px-6 py-3  text-sm font-semibold text-gray-700 border-b">
                        제출 시간
                    </th>
                    <th class="px-6 py-3  text-sm font-semibold text-gray-700 border-b">
                    </th>
                </tr>
                @for ($i = 1; $i <= 37; $i++)
                    <tr class="hover:bg-gray-50 text-sm text-gray-900 text-center">
                        <td class="px-6 py-3 border-b">{{ $i }}등</td>
                        <td class="px-6 py-3 border-b">
                            홍길동
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
                        <td class="px-6 py-3 border-b">
                            3등
                        </td>
                        <td class="px-6 py-3 border-b">
                            07/14 12:00
                        </td>
                        <td class="px-6 py-3 border-b">
                            <button
                                class="fi-link group/link relative inline-flex items-center justify-center outline-none fi-size-sm fi-link-size-sm gap-1 fi-color-custom fi-color-primary fi-ac-action fi-ac-link-action text-primary-500"
                                type="button" wire:loading.attr="disabled" wire:click="mountAction('detail')">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                    class="size-5">
                                    <path d="M8 10a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0Z" />
                                    <path fill-rule="evenodd"
                                        d="M4.5 2A1.5 1.5 0 0 0 3 3.5v13A1.5 1.5 0 0 0 4.5 18h11a1.5 1.5 0 0 0 1.5-1.5V7.621a1.5 1.5 0 0 0-.44-1.06l-4.12-4.122A1.5 1.5 0 0 0 11.378 2H4.5Zm5 5a3 3 0 1 0 1.524 5.585l1.196 1.195a.75.75 0 1 0 1.06-1.06l-1.195-1.196A3 3 0 0 0 9.5 7Z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span
                                    class="font-semibold text-sm text-custom-600 dark:text-custom-400 group-hover/link:underline group-focus-visible/link:underline"
                                    style="--c-400:var(--primary-400);--c-600:var(--primary-600);">
                                    상세보기
                                </span>
                            </button>
                        </td>
                    </tr>
                @endfor
            </tbody>
        </table>
        <x-filament-actions::modals />
    </div>
</div>
