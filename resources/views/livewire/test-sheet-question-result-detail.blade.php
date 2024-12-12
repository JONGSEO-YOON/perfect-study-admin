<div class="flex flex-col w-full max-w-[740px] mx-auto pb-10">
    <div class="font-semibold py-2 md:py-4 mt-3 md:mt-2 text-lg border-b px-4 md:px-0 flex flex-col">
        <h1 class="text-base {{ $isCorrect ? 'text-[#6156EF]' : 'text-[#FF4F57]' }} flex flex-row gap-x-2">
            @if ($isCorrect)
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                정답이에요.
            @else
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                오답이에요.
            @endif
        </h1>
        <h1 class="mt-3 flex items-center">
            문제 {{ $questionNo }})

        </h1>
        @if ($testsheet->use_score_table)
            <h1 class="text-gray-500 font-medium text-sm">
                [{{ $testsheet->parsed_score_table['table'][$questionNo] }}점]
            </h1>
        @endif
    </div>

    @if ($question['question_display_type'] === 'image')
        <img src="{{ Storage::url($question['image_path']) }}" class="w-[90%] md:w-[55%] full h-auto mt-8 px-4 md:px-0"
            alt="문제 이미지" />
    @else
        <div class="mt-8 px-4 md:px-0">
            {!! $question['content'] !!}
        </div>
    @endif

    <div
        class="flex flex-row border rounded-lg divide-x p-4 mx-4 md:mx-0 mt-8 font-medium divide-gray-300 
        {{ $isCorrect ? '!border-[#6156EF] !divide-[#6156EF]' : '!border-[#FF4F57] !divide-[#FF4F57]' }}">
        <div class="flex flex-col items-center justify-center flex-1 gap-y-2">
            <h1>정답</h1>
            <h1 class="text-2xl font-semibold">{{ $question['answer'] }}</h1>
        </div>
        <div class="flex flex-col items-center justify-center flex-1 gap-y-2">
            <h1>나의 답</h1>
            @if (empty($userAnswer))
                <h1 class="text-xl font-semibold">없음</h1>
            @else
                <h1 class="text-2xl font-semibold">{{ $userAnswer }}</h1>
            @endif
        </div>
    </div>

    @if (isset($question['explanation']))
        <div class="font-semibold py-2 md:py-4 mt-6 text-lg border-b px-4 md:px-0 flex flex-col">
            문제 해설
        </div>
        @if (isset($question['explanation_image_path']))
            <img src="{{ Storage::url($question['explanation_image_path']) }}"
                class="w-[90%] md:w-[55%] full h-auto mt-8 px-4 md:px-0" alt="해설 이미지" />
        @else
            <div class="mt-8 px-4 md:px-0">
                {!! $question['explanation'] !!}
            </div>
        @endif
    @endif

    @if (isset($question['explanation_video_url']))
        <div class="font-semibold py-2 md:py-4 mt-6 text-lg border-b px-4 md:px-0 flex flex-col">
            해설 영상
        </div>
        <div class="mt-8 px-4 md:px-0">
            <video controls class="w-full">
                <source src="/storage/{{ $question['explanation_video_url'] }}" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
    @endif
</div>
