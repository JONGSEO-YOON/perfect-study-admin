<div class="w-full h-full flex flex-col" wire:init="initAction()">
    <x-filament-actions::modals />
    <div class="flex flex-row gap-x-2 justify-end">
        {{ $this->sendAction }}
        {{ $this->printAction }}

    </div>
    <div>
        {{ $this->form }}
    </div>

</div>
