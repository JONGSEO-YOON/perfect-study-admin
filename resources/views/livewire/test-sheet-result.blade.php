<div class="h-full w-full flex flex-col" x-data="{ showWrongOnly: false }">
    <div class="border-b py-4 px-5 flex flex-col">
        <div class="flex flex-row items-center w-full max-w-[740px] mx-auto">
            <div class="flex flex-col md:flex-row md:ml-6 md:items-center">
                <h1 class="font-bold">
                    {{ $testsheet->start_date->format('m월 d일') }} {{ $testsheet->name }}
                </h1>
                <h2 class="text-[#7D7D92] md:ml-4 text-sm">
                    {{ count($testsheet->questions) }}문제 | {{ $testsheet->scopes[0] }}
                </h2>
            </div>
            <button wire:click="returnToMain"
                class="bg-[#F4F4F4] rounded-[5px] py-2 px-6 font-semibold hidden md:flex ml-auto">
                메인화면
            </button>
        </div>
        <button wire:click="returnToMain" class="bg-[#F4F4F4] rounded-[5px] py-2 text-sm font-semibold md:hidden mt-2.5">
            메인화면
        </button>
    </div>
    <div class="flex flex-col w-full max-w-[740px] mx-auto pb-10">
        <div class="text-center pt-4 mt-4 text-xl font-bold text-gray-700">
            문제지 체점 결과
        </div>
        <div class="flex flex-col items-center p-8 bg-white rounded-lg">
            <div class="relative w-64">
                <svg viewBox="-110 -110 220 130" xmlns="http://www.w3.org/2000/svg">
                    <!-- 배경 아치 -->
                    <path d="M -100 0 A 100 100 0 0 1 100 0" fill="none" stroke="#e5e5e5" stroke-width="20"
                        stroke-linecap="round" />

                    <!-- 마스크 정의 -->
                    <defs>
                        <clipPath id="progress-mask">
                            <rect x="-110" y="-110" width="{{ $percentage }}%" height="130" />
                        </clipPath>
                    </defs>

                    <!-- 진행바 아치 -->
                    <path d="M -100 0 A 100 100 0 0 1 100 0" fill="none" stroke="#8570C2" stroke-width="20"
                        stroke-linecap="round" clip-path="url(#progress-mask)" />
                </svg>

                <div class="absolute inset-0 flex flex-col items-center justify-center mt-10 gap-y-2">
                    <span class="text-4xl font-bold text-gray-700">{{ $correctCount }} /
                        {{ $totalQuestions }}</span>
                    <span class="text-lg text-gray-600">{{ $percentage }}%</span>
                </div>
            </div>
        </div>
        <div class="flex flex-col w-full max-w-[740px] mx-auto flex-1">
            <div class="font-semibold py-2 md:py-4 mt-6 text-lg border-b px-4 md:px-0 flex flex-row justify-between">
                문제 해설
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700">오답 문제만 보기</label>
                    <button @click="showWrongOnly = !showWrongOnly"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200"
                        :class="showWrongOnly ? 'bg-[#6156EF]' : 'bg-gray-200'">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-all duration-200"
                            :class="showWrongOnly ? 'translate-x-6' : 'translate-x-1'"></span>
                    </button>

                </div>
            </div>
            <div class="flex flex-row gap-x-2 ">
                <div class="grid grid-cols-5 gap-4 mt-4 px-5 md:px-0 md:grid-cols-1  h-fit  overflow-auto ">
                    @foreach ($answerStatuses as $index => $status)
                        <a href="/test-sheet-result/{{ $testsheet->id }}/{{ $index + 1 }}"
                            x-show="!showWrongOnly || !{{ $status['isCorrect'] }}"
                            class="md:!hidden rounded-full w-[54px] h-[54px] cursor-pointer hover:brightness-90 transition-all font-medium border flex items-center justify-center text-white {{ $status['isCorrect'] ? 'border-[#6156EF] bg-[#6156EF]/70' : 'border-[#FF4F57] bg-[#FF4F57]/70' }}">
                            {{ $index + 1 }}
                        </a>
                        <button wire:click="selectQuestion({{ $index + 1 }})"
                            x-show="!showWrongOnly || !{{ $status['isCorrect'] }}"
                            class="hidden md:flex min-h-[54px] max-h-[54px] rounded-full w-[54px] h-[54px] cursor-pointer hover:brightness-90 transition-all font-medium border items-center justify-center text-white {{ $status['isCorrect'] ? 'border-[#6156EF] bg-[#6156EF]/70' : 'border-[#FF4F57] bg-[#FF4F57]/70' }}">
                            {{ $index + 1 }}
                        </button>
                    @endforeach
                </div>
                <div class="ml-1 w-px bg-gray-200 h-full"></div>
                <div
                    class="hidden md:flex flex-1 bg-gray-200 m-4 rounded-lg items-center justify-center font-medium text-gray-400 overflow-auto md:min-h-[500px]">
                    @if ($selectedQuestionNo)
                        <div class="bg-white w-full text-black">
                            <livewire:test-sheet-question-result-detail :testsheet="$testsheet" :question-no="$selectedQuestionNo"
                                :wire:key="$selectedQuestionNo" />
                        </div>
                    @else
                        <div class="flex items-center justify-center font-medium text-gray-400">
                            문제 번호를 선택해주세요.
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
