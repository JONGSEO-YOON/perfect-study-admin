<div class="h-full w-full flex flex-col">
    <div class="border-b py-4 px-5 flex flex-col">
        <div class="flex flex-row items-center w-full max-w-[740px] mx-auto">
            <div class="flex flex-col md:flex-row  md:items-center">
                <h1 class="font-bold">
                    {{ $testsheet->start_date->format('m월 d일') }} {{ $testsheet->name }}
                </h1>
                <h2 class="text-[#7D7D92] md:ml-4 text-sm">
                    {{ count($testsheet->questions) }}문제 | {!! $testsheet->scopes[0] !!}
                </h2>
            </div>
            <button wire:click="backToList"
                class="bg-[#F4F4F4] rounded-[5px] py-2 px-6 font-semibold hidden md:flex ml-auto">
                목록으로
            </button>
        </div>
        <button wire:click="backToList" class="bg-[#F4F4F4] rounded-[5px] py-2 text-sm font-semibold md:hidden mt-2.5">
            목록으로
        </button>
    </div>
    <livewire:test-sheet-question-result-detail :testsheet="$testsheet" :question-no="$questionNo"
        :start-number="$startNumber" />
</div>
