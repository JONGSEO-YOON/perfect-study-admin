@script
    <script>
        window.questions = @json($this->questions);
        window.initialPrintLayout = @json($this->initialPrintLayout);

        window.onPreviewLoaded = async () => {
            const preview = document.getElementById('preview').contentWindow;
            if (window.initialPrintLayout) {
                preview.postMessage({
                    type: 'restorePrintLayout',
                    data: window.initialPrintLayout
                }, '*');
            }
            preview.postMessage({
                type: 'setQuestions',
                questions: window.questions
            }, '*');

        }
        Livewire.on('onQuestionUpdated', (data) => {
            window.questions = Object.values(data[0]);
            const preview = document.getElementById('preview')?.contentWindow;
            if (!preview) return;
            preview.postMessage({
                type: 'setQuestions',
                questions: window.questions
            }, '*');

        });
        Livewire.on('onSplitChanged', (data) => {
            document.getElementById('preview').contentWindow.postMessage({
                type: 'onSplitChanged',
                data: data[0]
            }, '*');
        });
        Livewire.on('onPageMetaChanged', (data) => {
            document.getElementById('preview').contentWindow.postMessage({
                type: 'onPageMetaChanged',
                data: data[0]
            }, '*');
        });
        //window add event listener
        window.addEventListener('message', (event) => {
            if (event.data.type === 'onPageSelected') {
                Livewire.dispatch('onPageSelected', event.data);
            } else if (event.data.type === 'printLayoutData') {
                Livewire.dispatch('submitWithLayout', {
                    layoutData: event.data.data
                });
            }
        });
    </script>
@endscript

<x-filament-panels::page>

    <div class="flex flex-col mx-auto">
        <x-filament-actions::modals />
        @if ($state === 'question-selection')
            <div class="flex flex-row mb-3 justify-between">
                {{ $this->addQuestion }}
                {{ $this->confirmQuestion }}
            </div>
            <div class="flex flex-row gap-x-2">
                <div class="flex flex-col flex-1">
                    <div class="w-full bg-white border rounded-lg p-4 flex flex-col items-center justify-center mb-1">
                        <h1 class="font-medium text-gray-600">총 문제</h1>
                        <h1 class="font-bold text-gray-800 text-2xl">{{ count($questions) }}문제</h1>
                        <h1 class="font-medium text-gray-600 text-sm my-1">
                            객관식 {{ $summary['by_answer_type']['multiple_choice'] ?? 0 }} · 주관식(정수형)
                            {{ $summary['by_answer_type']['integer'] ?? 0 }}
                        </h1>

                        <div class="py-2 px-14 w-full flex flex-col mt-4 text-gray-600">
                            <div class="flex flex-row border-b">
                                @for ($i = 1; $i <= 5; $i++)
                                    @php
                                        // 최대값을 120px로 설정하고 현재 값의 비율을 계산
                                        if (!count($summary['by_level'])) {
                                            $summary['by_level'] = [0];
                                        }
                                        $maxValue = max($summary['by_level']);
                                        $height =
                                            $maxValue > 0 ? (($summary['by_level'][$i] ?? 0) * 120) / $maxValue : 0;
                                    @endphp
                                    <div
                                        class="flex-1 flex flex-col items-center justify-end text-medium gap-y-1 text-sm">
                                        {{ $summary['by_level'][$i] ?? 0 }}문제
                                        <div class=" w-[24px] rounded-t bg-primary-500"
                                            style="height: {{ $height }}px">
                                        </div>
                                    </div>
                                @endfor
                            </div>
                            <div class="flex flex-row py-1 text-sm">
                                @for ($i = 1; $i <= 5; $i++)
                                    <div class="flex-1 flex items-center justify-center">
                                        레벨 {{ $i }}
                                    </div>
                                @endfor

                            </div>
                        </div>
                    </div>
                    <div class="flex-1 flex bg-white border rounded-lg flex-col">
                        <div class="bg-gray-50 border-b rounded-t-lg">
                            <div class="grid grid-cols-12 gap-2 px-2 py-3">
                                <div class="col-span-1 text-center text-sm font-medium text-gray-900">번호</div>
                                <div class="col-span-2 text-center text-sm font-medium text-gray-900">레벨</div>
                                <div class="col-span-2 text-center text-sm font-medium text-gray-900">문제 타입</div>
                                <div class="col-span-5 text-center text-sm font-medium text-gray-900">유형명</div>
                                <div class="col-span-2 text-center text-sm font-medium text-gray-900">순서 변경</div>
                            </div>
                        </div>
                        <!-- Scrollable Content -->
                        <div class="grow overflow-y-auto h-0">
                            <div wire:sortable="onOrderChanged" class="divide-y divide-gray-200">
                                @foreach ($questions as $index => $question)
                                    <div wire:key="question-{{ $question->id }}"
                                        wire:sortable.item="{{ $question->id }}"
                                        class="grid grid-cols-12 gap-2 px-2 py-3 bg-white hover:bg-gray-50 transition-colors">
                                        <div class="col-span-1 text-center text-sm text-gray-900">{{ $index + 1 }}
                                        </div>
                                        <div class="col-span-2 text-center text-sm text-gray-900">레벨
                                            {{ $question->level }}
                                        </div>

                                        <div class="col-span-2 text-center text-sm text-gray-900">
                                            {{ $question->answer_type === 'integer' ? '주관식 (정수형)' : '객관식' }}
                                        </div>
                                        <div class="col-span-5 text-center text-sm text-gray-900">
                                            {!! $question->questionType?->name !!}</div>
                                        <div wire:sortable.handle class="col-span-2  flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                fill="currentColor" class="size-4">
                                                <path fill-rule="evenodd"
                                                    d="M2 4.75A.75.75 0 0 1 2.75 4h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 4.75ZM2 10a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 10Zm0 5.25a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1-.75-.75Z"
                                                    clip-rule="evenodd" />
                                            </svg>

                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-1">
                    <div
                        class="flex flex-col bg-gray-100 p-4 rounded-lg gap-y-3 h-[calc(100vh)] overflow-auto min-w-[600px]">
                        <h1 class="text-base font-bold text-gray-800">선택된 문제 목록</h1>
                        @foreach ($questions as $number => $question)
                            <div class="bg-white shadow flex flex-col rounded-lg">
                                <div
                                    class="text-2xl bg-primary-400 text-white font-bold px-4 py-2.5 rounded-t-lg flex items-center">
                                    {{ $number + 1 }}
                                    <h2 class="flex items-center ml-4 text-base">
                                        {!! $question->questionType?->name !!}
                                    </h2>
                                    <h2 class="flex items-center ml-4 text-sm">
                                        레벨: {{ $question->level }}
                                    </h2>
                                </div>
                                @if ($question->question_display_type === 'image')
                                    <img class="w-full " src="/storage/{{ $question->image_path }}" />
                                @else
                                    <div class="min-h-[150px] p-4 text-xl">{!! $question->content !!}</div>
                                @endif
                                <div class="flex flex-row text-sm">
                                    <button type="button"
                                        wire:click="mountAction('addSimilarQuestion', { question: {{ $question }} })"
                                        class="flex flex-1 items-center justify-center bg-primary-400 text-white py-2.5 font-bold rounded-bl-lg gap-x-2.5 hover:bg-primary-500 transition-all ">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                            class="size-5">
                                            <path d="M8 10a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0Z" />
                                            <path fill-rule="evenodd"
                                                d="M4.5 2A1.5 1.5 0 0 0 3 3.5v13A1.5 1.5 0 0 0 4.5 18h11a1.5 1.5 0 0 0 1.5-1.5V7.621a1.5 1.5 0 0 0-.44-1.06l-4.12-4.122A1.5 1.5 0 0 0 11.378 2H4.5Zm5 5a3 3 0 1 0 1.524 5.585l1.196 1.195a.75.75 0 1 0 1.06-1.06l-1.195-1.196A3 3 0 0 0 9.5 7Z"
                                                clip-rule="evenodd" />
                                        </svg>

                                        유사 문제 조회
                                    </button>
                                    <button wire:click="removeQuestion({{ $question->id }})"
                                        class="flex flex-1 items-center justify-center bg-danger-400 text-white py-2.5 font-bold rounded-br-lg gap-x-2.5 hover:bg-danger-500 transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                            class="size-4">
                                            <path fill-rule="evenodd"
                                                d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Zm4.34.06a.75.75 0 1 0-1.5-.06l-.3 7.5a.75.75 0 1 0 1.5.06l.3-7.5Z"
                                                clip-rule="evenodd" />
                                        </svg>

                                        삭제
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="flex flex-row mb-3 justify-between">
                <button style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                    class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-action fi-ac-btn-action"
                    wire:click="backToQuestionSelection">

                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                        <path fill-rule="evenodd"
                            d="M7.793 2.232a.75.75 0 0 1-.025 1.06L3.622 7.25h10.003a5.375 5.375 0 0 1 0 10.75H10.75a.75.75 0 0 1 0-1.5h2.875a3.875 3.875 0 0 0 0-7.75H3.622l4.146 3.957a.75.75 0 0 1-1.036 1.085l-5.5-5.25a.75.75 0 0 1 0-1.085l5.5-5.25a.75.75 0 0 1 1.06.025Z"
                            clip-rule="evenodd" />
                    </svg>

                    <span class="fi-btn-label">
                        이전 단계
                    </span>
                </button>
                <button style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                    class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-action fi-ac-btn-action"
                    type="submit" form="test-sheet-form">
                    <svg class="fi-btn-icon transition duration-75 h-5 w-5 text-white"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"
                        data-slot="icon">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span class="fi-btn-label">
                        다음 단계
                    </span>
                </button>

            </div>
            <div class="w-[1350px] flex flex-row gap-x-4">
                <div class="flex-1 !grow-[15]">
                    <div class="h-[400px] mb-4 flex bg-white border rounded-lg flex-col">
                        <div class="bg-gray-50 border-b rounded-t-lg">
                            <div class="grid grid-cols-12 gap-2 px-2 py-3">
                                <div class="col-span-1 text-center text-xs font-medium text-gray-900">번호</div>
                                <div class="col-span-2 text-center text-xs font-medium text-gray-900">레벨</div>
                                <div class="col-span-2 text-center text-xs font-medium text-gray-900">문제 타입</div>
                                <div class="col-span-5 text-center text-xs font-medium text-gray-900">유형명</div>
                                <div class="col-span-2 text-center text-xs font-medium text-gray-900">순서 변경</div>
                            </div>
                        </div>
                        <!-- Scrollable Content -->
                        <div class="grow overflow-y-auto h-0">
                            <div wire:sortable="onOrderChanged" class="divide-y divide-gray-200">
                                @foreach ($questions as $index => $question)
                                    <div wire:key="question-{{ $question->id }}"
                                        wire:sortable.item="{{ $question->id }}"
                                        class="grid grid-cols-12 gap-2 px-2 py-3 bg-white hover:bg-gray-50 transition-colors">
                                        <div class="col-span-1 text-center text-xs text-gray-900">{{ $index + 1 }}
                                        </div>
                                        <div class="col-span-2 text-center text-xs text-gray-900">레벨
                                            {{ $question->level }}
                                        </div>

                                        <div class="col-span-2 text-center text-xs text-gray-900">
                                            {{ $question->answer_type === 'integer' ? '주관식 (정수형)' : '객관식' }}
                                        </div>
                                        <div class="col-span-5 text-center text-xs text-gray-900">
                                            {!! $question->questionType?->name !!}</div>
                                        <div wire:sortable.handle class="col-span-2  flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                fill="currentColor" class="size-4">
                                                <path fill-rule="evenodd"
                                                    d="M2 4.75A.75.75 0 0 1 2.75 4h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 4.75ZM2 10a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 10Zm0 5.25a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1-.75-.75Z"
                                                    clip-rule="evenodd" />
                                            </svg>

                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <form id="test-sheet-form"
                        @submit.prevent="
                    $event.target.disabled = true;
                    let preview = document.getElementById('preview');
                    preview.contentWindow.postMessage({ type: 'getPrintLayout' }, '*');
                  ">
                        {{ $this->form }}
                    </form>
                </div>
                <div class="flex-1 !grow-[25]">
                    <iframe onload="onPreviewLoaded()" id="preview" class="w-full h-full"
                        src="/preview-test-sheet?scale=1"></iframe>
                </div>
            </div>
        @endif
    </div>

    <style>
        .draggable-mirror {
            width: 500px !important;
            box-sizing: border-box !important;
        }
    </style>
</x-filament-panels::page>
