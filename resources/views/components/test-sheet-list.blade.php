@props(['testsheets'])

<div id="test-sheet-list" class="flex flex-col  gap-y-4">
    @foreach ($testsheets as $testsheet)
        <div class="hidden md:flex rounded-[10px] border-[#E9E9E9] border py-7 px-5 flex-row items-center">
            <div
                class="rounded-full h-[56px] w-[56px] bg-[#8570C2] text-white text-center p-1.5 leading-tight font-medium flex items-center justify-center">
                {{ $testsheet->target_grade_names }}
            </div>
            <h1 class="font-semibold ml-4 w-14 break-all">
                {{ $testsheet->tags[0] ?? '' }}
            </h1>
            <div class="flex flex-col ml-6 gap-y-1.5 flex-1">
                <h1 class="font-bold">
                    {{ $testsheet->start_date->format('m월 d일') }} {{ $testsheet->name }}
                </h1>
                <h1 class="font-medium text-sm text-[#7D7D92]">
                    {{ count($testsheet->questions) }} 문제 | {!! $testsheet->scopes[0] !!}
                </h1>
            </div>
            <div class="flex flex-col gap-y-1">
                @if ($testsheet->due_text && $testsheet->latestUserAnswer?->status !== 'completed')
                    <div class="font-medium text-sm text-[#7D7D92] flex flex-row justify-between gap-x-4">
                        마감 기한
                        <span><span class="text-[#F86363] font-bold mr-1">{{ $testsheet->due_text }}</span></span>
                    </div>
                @endif
                <div class="font-medium text-sm text-[#7D7D92] flex flex-row justify-between gap-x-4">
                    풀이된 문제
                    <span><span
                            class="text-[#7256C2] font-bold">{{ $testsheet->latestUserAnswer?->answer_count ?? 0 }}</span>/{{ count($testsheet->questions) }}</span>
                </div>
            </div>
            @if ($testsheet->latestUserAnswer?->status === 'completed')
                <a href="/test-sheet-result/{{ $testsheet->id }}"
                    class="flex flex-row items-center justify-center py-2.5 px-7 font-semibold bg-[#F4F4F4]  rounded-[5px] gap-x-2 ml-4">

                    <svg width="14" height="14" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor" class="size-5">
                        <path fill-rule="evenodd"
                            d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z"
                            clip-rule="evenodd" />
                    </svg>
                    체점결과
                </a>
            @else
                <a href="{{ $testsheet->latestUserAnswer
                    ? '/test-sheet/' .
                        $testsheet->id .
                        '?index=' .
                        (count(array_filter($testsheet->latestUserAnswer->answers, fn($answer) => $answer !== null)) - 1)
                    : '/test-sheet/' . $testsheet->id }}"
                    class="flex flex-row items-center justify-center py-2.5 px-7 font-semibold bg-[#7256C2] text-white rounded-[5px] gap-x-3 ml-4">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M12.106 5.23521C12.4262 5.40549 12.694 5.65967 12.8808 5.97054C13.0676 6.2814 13.1663 6.63722 13.1663 6.99988C13.1663 7.36254 13.0676 7.71836 12.8808 8.02922C12.694 8.34008 12.4262 8.59427 12.106 8.76455L3.56467 13.4092C2.18933 14.1579 0.5 13.1845 0.5 11.6452V2.35521C0.5 0.815213 2.18933 -0.157454 3.56467 0.589879L12.106 5.23521Z"
                            fill="currentColor" />
                    </svg>
                    @if ($testsheet->latestUserAnswer)
                        이어풀기
                    @else
                        응시하기
                    @endif
                </a>
            @endif
        </div>
        <div class="rounded-[10px] border-[#E9E9E9] border py-4 px-5 flex flex-col md:hidden">
            <div class="flex flex-row items-center">
                <div
                    class="rounded-full h-[56px] w-[56px] bg-[#8570C2] text-white text-center p-1.5 leading-tight font-medium flex items-center justify-center">
                    {{ $testsheet->target_grade_names }}
                </div>
                <h1 class="font-semibold ml-4 w-14 break-all">
                    @foreach ($testsheet->tags ?? [] as $item)
                        {{ $item }}
                    @endforeach
                </h1>
            </div>
            <div class="flex flex-col mt-4 gap-y-1.5 flex-1 px-2">
                <h1 class="font-bold">
                    {{ $testsheet->start_date->format('m월 d일') }} {{ $testsheet->name }}
                </h1>
                <h1 class="font-medium text-sm text-[#7D7D92]">
                    {{ count($testsheet->questions) }} 문제 | {!! $testsheet->scopes[0] !!}
                </h1>
            </div>
            <div class="flex flex-col gap-y-1 mt-4 px-2">
                <div class="hidden font-medium text-sm text-[#7D7D92] flex flex-row justify-between gap-x-4">
                    마감 기한
                    <span><span class="text-[#F86363] font-bold mr-1">2</span>일남음</span>
                </div>
                <div class="font-medium text-sm text-[#7D7D92] flex flex-row justify-between gap-x-4">
                    풀이된 문제
                    @if ($testsheet->latestUserAnswer)
                        <span><span
                                class="text-[#7256C2] font-bold">{{ $testsheet->latestUserAnswer->answer_count }}</span>/{{ count($testsheet->questions) }}</span>
                    @else
                        <span><span class="text-[#7256C2] font-bold">0</span>/{{ count($testsheet->questions) }}</span>
                    @endif
                </div>
            </div>
            @if ($testsheet->latestUserAnswer?->status === 'completed')
                <a href="/test-sheet-result/{{ $testsheet->id }}"
                    class="flex flex-row items-center justify-center py-2.5 px-7 bg-[#F4F4F4] font-semibold  rounded-[5px] gap-x-3 mt-4">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z" fill="currentColor" />
                    </svg>
                    체점결과
                </a>
            @else
                <a href="/test-sheet/{{ $testsheet->id }}"
                    class="flex flex-row items-center justify-center py-2.5 px-7 font-semibold text-white bg-[#7256C2] rounded-[5px] gap-x-3 mt-4">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M12.106 5.23521C12.4262 5.40549 12.694 5.65967 12.8808 5.97054C13.0676 6.2814 13.1663 6.63722 13.1663 6.99988C13.1663 7.36254 13.0676 7.71836 12.8808 8.02922C12.694 8.34008 12.4262 8.59427 12.106 8.76455L3.56467 13.4092C2.18933 14.1579 0.5 13.1845 0.5 11.6452V2.35521C0.5 0.815213 2.18933 -0.157454 3.56467 0.589879L12.106 5.23521Z"
                            fill="currentColor" />
                    </svg>
                    @if ($testsheet->latestUserAnswer)
                        이어풀기
                    @else
                        응시하기
                    @endif
                </a>
            @endif
        </div>
    @endforeach
</div>
