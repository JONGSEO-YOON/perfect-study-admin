<x-filament-panels::page>
    <form wire:submit="create">
        {{ $this->form }}
        <x-filament::button icon="heroicon-m-sparkles" class="mt-4">
            업로드
        </x-filament::button>
    </form>
</x-filament-panels::page>
