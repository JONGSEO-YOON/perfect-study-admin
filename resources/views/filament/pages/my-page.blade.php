<x-filament-panels::page class="py-4">
    <form wire:submit.prevent="updateUser" class="flex flex-col">
        {{ $this->form }}
        <div class="mt-4 flex  space-x-3">
            <button style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50"
                type="submit">
                <span class="fi-btn-label">
                    저장하기
                </span>
            </button>
        </div>
    </form>

</x-filament-panels::page>
