<x-layouts.simple>
    <div class="h-full w-full flex flex-col">
        <div class="border-b py-4 px-5 flex flex-col">
            <div class="flex flex-row items-center w-full max-w-[740px] mx-auto">
                <div class="flex flex-col md:flex-row  md:items-center">
                    <h1 class="font-bold">
                        {{ $testsheet->start_date->format('m월 d일') }} {{ $testsheet->name }}
                    </h1>
                    <h2 class="text-[#7D7D92] md:ml-4 text-sm">
                        {{ count($testsheet->questions) }}문제 | {{ $testsheet->scopes[0] }}
                    </h2>
                </div>
                <button wire:click="backToList"
                    class="bg-[#F4F4F4] rounded-[5px] py-2 px-6 font-semibold hidden md:flex ml-auto">
                    목록으로
                </button>
            </div>
            <button wire:click="backToList"
                class="bg-[#F4F4F4] rounded-[5px] py-2 text-sm font-semibold md:hidden mt-2.5">
                목록으로
            </button>
        </div>
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
                <h1 class="mt-3">
                    문제 {{ $questionNo }})
                </h1>
            </div>
            @if ($question['question_display_type'] === 'image')
                <img src="{{ Storage::url($question['image_path']) }}"
                    class="w-[90%] md:w-[55%] full h-auto mt-8 px-4 md:px-0" alt="문제 이미지" />
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
    </div>
</x-layouts.simple>
