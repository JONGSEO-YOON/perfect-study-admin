@php
    $questions = $getState() ?? [];
    $questionIds = [];
    if ($this->mountedActionsData[0]['question_ids'] ?? false) {
        $questionIds = $this->mountedActionsData[0]['question_ids'];
    }
@endphp
<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div class="flex flex-col bg-gray-100 p-4 rounded-lg gap-y-3 h-[calc(60vh)] overflow-auto ">
        <h1 class="text-base font-bold text-gray-800">선택된 문제 목록</h1>
        @foreach ($questions as $number => $question)
            <div class="bg-white shadow flex flex-col rounded-lg">
                <div class="text-2xl bg-primary-400 text-white font-bold px-4 py-2.5 rounded-t-lg flex items-center">
                    {{ $number + 1 }}
                    <h2 class="flex items-center ml-4 text-base">
                        {{ $question['question_type']['name'] }}
                    </h2>
                    <h2 class="flex items-center ml-4 text-sm">
                        레벨: {{ $question['level'] }}
                    </h2>
                </div>
                @if ($question['question_display_type'] === 'image')
                    <img class="w-full rounded-b-lg p-4" src="/storage/{{ $question['image_path'] }}" />
                @else
                    <div class="p-4 text-2xl">
                        {!! $question['content'] !!}
                    </div>
                @endif
                <div class="flex flex-row text-sm">
                    <button wire:click="addTempQuestion({{ $question['id'] }})" type="button"
                        @disabled(in_array($question['id'], $questionIds))
                        class="flex flex-1 items-center justify-center bg-primary-400 text-white py-2.5 font-bold rounded-bl-lg gap-x-2.5 hover:bg-primary-500 transition-all disabled:!opacity-50 ">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.25a.75.75 0 0 0-1.5 0v2.5h-2.5a.75.75 0 0 0 0 1.5h2.5v2.5a.75.75 0 0 0 1.5 0v-2.5h2.5a.75.75 0 0 0 0-1.5h-2.5v-2.5Z"
                                clip-rule="evenodd" />
                        </svg>
                        추가하기
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</x-dynamic-component>
