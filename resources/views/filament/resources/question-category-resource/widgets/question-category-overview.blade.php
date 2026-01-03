<x-filament-widgets::widget>
    <x-filament::section>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-4 gap-4 px-4">
            @foreach ($categories as $category)
                <div class="flex flex-col items-center space-y-2 hover:brightness-90 cursor-pointer transition-all"
                    wire:click="redirectTo(location.href, {{ $category->id }})">
                    <div class="py-14 w-full flex items-center justify-center bg-gray-100 rounded-lg text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-10">
                            <path
                                d="M3.75 3A1.75 1.75 0 0 0 2 4.75v3.26a3.235 3.235 0 0 1 1.75-.51h12.5c.644 0 1.245.188 1.75.51V6.75A1.75 1.75 0 0 0 16.25 5h-4.836a.25.25 0 0 1-.177-.073L9.823 3.513A1.75 1.75 0 0 0 8.586 3H3.75ZM3.75 9A1.75 1.75 0 0 0 2 10.75v4.5c0 .966.784 1.75 1.75 1.75h12.5A1.75 1.75 0 0 0 18 15.25v-4.5A1.75 1.75 0 0 0 16.25 9H3.75Z" />
                        </svg>
                    </div>
                    <p @class([
                        'text-base font-bold select-none text-center line-clamp-2 px-2 py-1 rounded',
                    ])>
                        {!! $category->name !!}
                    </p>
                </div>
            @endforeach

        </div>
        <x-filament-actions::modals />
    </x-filament::section>
</x-filament-widgets::widget>
