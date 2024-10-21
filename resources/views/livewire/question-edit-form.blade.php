<div x-data="{ question: $wire.entangle('question') }" x-cloak>
    <div class="bg-white fixed right-0 top-0 w-[400px] h-screen z-50 shadow-lg flex flex-col p-4 overflow-y-auto transition "
        :class="{ 'translate-x-full': question == null }">
        <h1 class="text-lg font-bold">
            문제 편집
        </h1>
        <div class="flex flex-row justify-end">
            <button wire:click="saveQuestion" type="button"
                style="
                --c-400: var(--primary-400);
                --c-500: var(--primary-500);
                --c-600: var(--primary-600);
            "
                class="fi-btn relative grid-flow-col disabled:opacity-50 items-center justify-center font-semibold outline-none transition-all duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-lg fi-btn-size-lg gap-1.5 px-3.5 py-1.5 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50">
                <span class="fi-btn-label">
                    저장
                </span>
            </button>
        </div>
        <div class="mt-2">
            {{ $this->form }}
        </div>
    </div>
</div>
