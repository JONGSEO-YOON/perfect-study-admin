<x-filament-panels::page>
    <form wire:submit="create">
        {{ $this->form }}
        <div class="mt-2">
            <x-filament::button icon="heroicon-m-sparkles" size="lg" type="submit">
                업로드
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
