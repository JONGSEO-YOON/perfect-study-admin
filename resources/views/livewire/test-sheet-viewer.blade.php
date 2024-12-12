<div class="h-full w-full flex flex-col" x-data="{ showAnswerPanel: false, showOverviewPanel: false, showCompletionModal: false }" x-cloak>
    <div class="h-full w-full flex flex-col">
        <div class="border-b py-4 px-5 flex flex-col">
            <div class="flex flex-row items-center w-full max-w-[740px] mx-auto">
                <a href="#" @click="showOverviewPanel = true" class="p-2 bg-white rounded">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M16.5 4.5H20.25V21.75H3.75V4.5H7.5V6H16.5V4.5ZM6.75 12H17.25V10.5H6.75V12ZM6.75 18H17.25V16.5H6.75V18ZM9 4.5V2.25H15V4.5H9Z"
                            fill="#3E3E3E" />
                    </svg>
                </a>
                <div class="flex flex-col md:flex-row ml-6 md:items-center">
                    <h1 class="font-bold">
                        {{ $testsheet->start_date->format('m월 d일') }} {{ $testsheet->name }}
                    </h1>
                    <h2 class="text-[#7D7D92] md:ml-4 text-sm">
                        {{ count($testsheet->questions) }} 문제 | {{ $testsheet->scopes[0] }}
                    </h2>
                </div>
                <div class="flex flex-col md:flex-row ml-auto">
                    <div wire:poll.1000ms="updateTimer"
                        class="flex flex-row gap-x-2 items-center text-[#7D7D92] ml-auto text-sm md:text-base">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M7.00001 13.6667C3.31801 13.6667 0.333344 10.682 0.333344 7.00004C0.333344 3.31804 3.31801 0.333374 7.00001 0.333374C10.682 0.333374 13.6667 3.31804 13.6667 7.00004C13.6667 10.682 10.682 13.6667 7.00001 13.6667ZM7.66668 7.00004V3.66671H6.33334L6.33334 8.33337H10.3333V7.00004H7.66668Z"
                                fill="#7D7D92" />
                        </svg>
                        {{ sprintf('%02d:%02d:%02d', floor($elapsedTime / 3600), floor(($elapsedTime % 3600) / 60), $elapsedTime % 60) }}
                    </div>
                    <button @click="showCompletionModal=true"
                        class="bg-[#F4F4F4] rounded-[5px] py-2 px-6 font-semibold ml-4 hidden md:flex">
                        풀이 종료
                    </button>
                </div>
            </div>
            <button @click="showCompletionModal=true"
                class="bg-[#F4F4F4] rounded-[5px] py-2 text-sm font-semibold md:hidden mt-2.5">
                풀이 종료
            </button>
        </div>
        <div class="flex flex-col w-full max-w-[740px] mx-auto md:mt-3 ">
            <div
                class="bg-[#F4F4F4] px-4 py-2 text-sm md:text-base  md:px-5 md:py-4 flex flex-row  md:rounded-[10px] items-center">
                <h1 class="font-bold">진행율</h1>
                <h2 class="ml-4 text-[#7D7D92] flex flex-row items-center"><span
                        class="font-bold text-[#8570C2] mr-1 text-lg">{{ $progress['current'] }}</span>/{{ $progress['total'] }}
                </h2>
                <div class="flex-1 bg-white rounded-[5px] h-5 ml-4">
                    <div class="bg-[#8570C2] rounded-[5px] h-full" style="width: {{ $progress['percentage'] }}%">
                    </div>
                </div>
            </div>
            <div wire:key="question-{{ $currentQuestionIndex }}" class="question-transition flex flex-col"
                wire:transition.duration.300ms.slide-fade>
                <div class="flex flex-col font-semibold py-2 md:py-4 mt-1 md:mt-2 text-lg border-b px-4 md:px-0">
                    문제 {{ $progress['current'] }})
                    @if ($testsheet->use_score_table)
                        <h1 class="text-gray-500 font-medium text-base mt-0.5">
                            [{{ $testsheet->parsed_score_table['table'][$progress['current']] }}점]
                        </h1>
                    @endif
                </div>
                @if ($currentQuestion['question_display_type'] === 'image')
                    <img src="{{ Storage::url($currentQuestion['image_path']) }}"
                        class="w-[90%] md:w-[55%] full h-auto mt-8 px-4 md:px-0" alt="문제 이미지" />
                @else
                    <div class="mt-8 px-4 md:px-0">
                        {!! $currentQuestion['content'] !!}
                    </div>
                @endif
                {{-- {{ dd($currentQuestion['choices_display_type']) }} --}}
                @if ($currentQuestion['answer_type'] === 'multiple_choice' && $currentQuestion['choices_display_type'] === 'seperate')
                    <div class="gap-x-1.5 grid grid-cols-3 mt-4 w-full md:w-2/3 px-4 md:px-0">
                        @foreach ($currentQuestion['choices'] as $i => $choice)
                            <div class="flex flex-row gap-x-1 items-center h-7">
                                <div>
                                    @if ($i === 0)
                                        ①
                                    @elseif ($i === 1)
                                        ②
                                    @elseif ($i === 2)
                                        ③
                                    @elseif ($i === 3)
                                        ④
                                    @elseif ($i === 4)
                                        ⑤
                                    @elseif ($i === 5)
                                        ⑥
                                    @endif
                                </div>
                                @if ($choice['display_type'] === 'content')
                                    <div class="flex-1">{!! $choice['content'] !!}</div>
                                @elseif ($choice['display_type'] === 'image')
                                    <div class="flex-1">
                                        <img src="{{ Storage::url($choice['image_path']) }}"
                                            alt="Choice {{ $i + 1 }}" class="w-full">
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="fixed bottom-0 left-0 right-0 w-full">
        <div class="flex flex-col w-full max-w-[740px] mx-auto py-6 shadow-xl rounded-t-[35px] px-5 md:px-16 gap-y-4 z-10 bg-white transition-all duration-300"
            :style="'box-shadow: 0px 4px 12px 6px rgba(189, 189, 189, 0.25); ' + (showAnswerPanel ? 'height: 500px' :
                'height:100px')"
            style="">
            <div class="flex flex-row gap-x-5">
                <button @click="showAnswerPanel = !showAnswerPanel"
                    class="grow-[2] border-[#E9E9E9] rounded-[10px] font-semibold flex-1 py-3 bg-[#F4F4F4]">
                    <template x-if="!showAnswerPanel">
                        <div>
                            답 입력
                        </div>
                    </template>
                    <template x-if="showAnswerPanel">
                        <div>
                            닫기
                        </div>
                    </template>
                </button>
                <button x-show="!showAnswerPanel"
                    class="border border-[#E9E9E9] rounded-[10px] font-semibold flex-1 py-3 bg-white"
                    wire:click="nextQuestion" @click="showAnswerPanel = false">
                    다음 문제
                </button>
            </div>
            <div class="flex flex-col" x-show="showAnswerPanel"
                x-transition:enter="transition transform ease-out duration-300"
                x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
                x-transition:leave="transition transform ease-in duration-300" x-transition:leave-start="translate-y-0"
                x-transition:leave-end="translate-y-full">
                <div class="mb-4 flex flex-row gap-x-5 items-center ">
                    <h1 class="font-bold">답 입력</h1>
                    <button
                        class="grow-[2] border-[#E9E9E9] flex justify-start pl-4 rounded-[10px] flex-1 py-3 bg-[#F4F4F4] {{ empty($currentAnswer) ? 'text-[#C4C4C4]' : 'text-black font-bold' }}">
                        {{ empty($currentAnswer) ? '답을 입력해 주세요.' : $currentAnswer }}
                    </button>
                </div>

                <div class="grid grid-cols-3 gap-2">

                    <div class="col-span-2"></div>
                    @if ($currentQuestion['answer_type'] !== 'multiple_choice')
                        <button wire:click="toggleSign"
                            class="bg-[#F4F4F4] hover:bg-gray-300 p-4 rounded-[10px] text-xl transition-all">+ /
                            -</button>
                    @else
                        <div></div>
                    @endif
                    <button wire:click="appendNumber(1)"
                        class="bg-[#F4F4F4] hover:bg-gray-300 p-4 rounded-[10px] text-xl transition-all">1</button>
                    <button wire:click="appendNumber(2)"
                        class="bg-[#F4F4F4] hover:bg-gray-300 p-4 rounded-[10px] text-xl transition-all">2</button>
                    <button wire:click="appendNumber(3)"
                        class="bg-[#F4F4F4] hover:bg-gray-300 p-4 rounded-[10px] text-xl transition-all">3</button>
                    <button wire:click="appendNumber(4)"
                        class="bg-[#F4F4F4] hover:bg-gray-300 p-4 rounded-[10px] text-xl transition-all">4</button>
                    <button wire:click="appendNumber(5)"
                        class="bg-[#F4F4F4] hover:bg-gray-300 p-4 rounded-[10px] text-xl transition-all">5</button>
                    <button wire:click="appendNumber(6)"
                        class="bg-[#F4F4F4] hover:bg-gray-300 p-4 rounded-[10px] text-xl transition-all">6</button>
                    <button wire:click="appendNumber(7)"
                        class="bg-[#F4F4F4] hover:bg-gray-300 p-4 rounded-[10px] text-xl transition-all">7</button>
                    <button wire:click="appendNumber(8)"
                        class="bg-[#F4F4F4] hover:bg-gray-300 p-4 rounded-[10px] text-xl transition-all">8</button>
                    <button wire:click="appendNumber(9)"
                        class="bg-[#F4F4F4] hover:bg-gray-300 p-4 rounded-[10px] text-xl transition-all">9</button>
                    <button wire:click="clear"
                        class="bg-[#F4F4F4] hover:bg-gray-300 p-4 rounded-[10px] text-lg font-semibold transition-all">지우기</button>
                    <button wire:click="appendNumber(0)"
                        class="bg-[#F4F4F4] hover:bg-gray-300 p-4 rounded-[10px] text-xl transition-all">0</button>
                    <button wire:click="submitAnswer" @click="showAnswerPanel = false"
                        class="p-4 text-white rounded-[10px] text-lg bg-[#8570C2] font-semibold transition-all">완료</button>
                </div>
            </div>

        </div>
    </div>

    <div x-show="showOverviewPanel" @click.self="showOverviewPanel = false"
        class="fixed left-0 right-0 top-0 bottom-0 bg-black/50 z-10 flex flex-row"
        x-transition:enter="transition-opacity duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity duration-300"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="w-[320px] bg-white shadow p-5 flex flex-col"
            x-transition:enter="transition transform duration-300" x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0" x-transition:leave="transition transform duration-300"
            x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
            <div class="text-lg font-bold justify-between flex flex-row border-b pb-4">
                <h1>풀이 현황</h1>
                <h1 class="font-medium">
                    {{ count(array_filter($answers, fn($answer) => $answer !== null)) }}/{{ count($questions) }}</h1>
            </div>
            <div class="grid grid-cols-5 gap-2 mt-4">
                @foreach ($questions as $index => $question)
                    @if ($index === $currentQuestionIndex)
                        <button wire:click="goToQuestion({{ $index }})" @click="showOverviewPanel = false"
                            class="rounded-full cursor-pointer hover:brightness-90 transition-all w-[48px] h-[48px] font-medium border flex items-center justify-center text-white bg-[#8570C2]">
                            {{ $index + 1 }}
                        </button>
                    @elseif ($answers[$index] !== null)
                        <button wire:click="goToQuestion({{ $index }})" @click="showOverviewPanel = false"
                            class="rounded-full w-[48px] h-[48px] cursor-pointer hover:brightness-90 transition-all font-medium border flex items-center justify-center bg-[#8570C2]/50 text-white">
                            {{ $index + 1 }}
                        </button>
                    @else
                        <button wire:click="goToQuestion({{ $index }})" @click="showOverviewPanel = false"
                            class="rounded-full w-[48px] h-[48px] cursor-pointer hover:bg-gray-100 transition-all font-medium border flex items-center justify-center">
                            {{ $index + 1 }}
                        </button>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <div x-show="showCompletionModal"
        class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="transform scale-95 opacity-0"
            x-transition:enter-end="transform scale-100 opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="transform scale-100 opacity-100"
            x-transition:leave-end="transform scale-95 opacity-0">
            <h2 class="text-xl font-bold mb-4">풀이 종료</h2>
            <p class="text-gray-600 mb-6">문제지를 제출하시겠습니까? <br />제출 후에는 답변을 수정할 수 없습니다.</p>
            <div class="flex justify-end space-x-3">
                <button @click="showCompletionModal = false"
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors">
                    취소
                </button>
                <button wire:click="pauseTest"
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors">
                    일시정지
                </button>

                <button wire:click="completeTest"
                    class="px-4 py-2 bg-[#8570C2] text-white rounded-lg hover:bg-[#7460B2] transition-colors">
                    제출하기
                </button>
            </div>
        </div>
    </div>
</div>
