<x-filament-widgets::widget>
    <x-filament::section>
        <div class="font-semibold px-4 mb-2">
            교재 목록
        </div>
        <div class="px-4 font-medium text-sm text-gray-500 flex flex-row items-center justify-between h-6 mb-2">
            <div>
                @php
                    $path = $this->getBreadcrumbPath();
                @endphp
                {{ collect($path)->pluck('name')->join(' > ') }}
            </div>
            @if ($selectedMaterialId && $selectedMaterial?->is_editable)
                <div class="flex flex-row gap-x-2">
                    {{-- {{ $this->deleteMaterialAction }} --}}
                    {{ $this->editMaterialAction }}
                    {{ $this->deleteMaterialAction }}
                </div>
            @endif
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 px-4">
            @if ($hasUpperLevel)
                <div class="flex flex-col items-center space-y-2 hover:brightness-90 cursor-pointer transition-all"
                    wire:click="redirectTo(location.href, 'up') ">
                    <div @class([
                        'aspect-[2/3] w-full flex items-center justify-center bg-gray-100 rounded-lg text-gray-400 transition-all ',
                        '!bg-primary-500 !text-white' => $selectedMaterialId === 'up',
                    ])>

                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-10">
                            <path fill-rule="evenodd"
                                d="M16 16.25a.75.75 0 0 0-.75-.75h-7.5V4.56l1.97 1.97a.75.75 0 1 0 1.06-1.06L7.53 2.22a.75.75 0 0 0-1.06 0L3.22 5.47a.75.75 0 0 0 1.06 1.06l1.97-1.97v11.69c0 .414.336.75.75.75h8.25a.75.75 0 0 0 .75-.75Z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
            @endif
            @foreach ($materials->where('type', 'folder')->sortBy('created_at') as $material)
                <div class="flex flex-col items-center space-y-2 hover:brightness-90 cursor-pointer transition-all"
                    wire:click="setSelectedMaterial({{ $material->id }})"
                    wire:dblclick="redirectTo(location.href, {{ $material->id }}) ">
                    <div class="aspect-[2/3] w-full flex items-center justify-center bg-gray-100 rounded-lg">
                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                    </div>

                    <p @class([
                        'text-sm select-none font-medium text-center line-clamp-2 px-2 py-1 rounded',
                        'bg-primary-500 text-white' => $selectedMaterialId === $material->id,
                    ])>
                        {{ $material->name }}
                    </p>
                </div>
            @endforeach
            @foreach ($materials->where('type', 'book')->sortBy('created_at') as $material)
                <div class="flex flex-col items-center space-y-2 hover:brightness-90 cursor-pointer transition-all relative"
                    wire:click="redirectTo(location.href, {{ $material->id }})">
                    @if ($material->image_path)
                        <div class="aspect-[2/3] w-full bg-gray-200 rounded-lg overflow-hidden shadow">
                            <img src="{{ Storage::url($material->image_path) }}" alt="{{ $material->name }}"
                                class="w-full h-full object-cover boz">
                        </div>
                    @else
                        <div
                            class="aspect-[2/3] w-full flex items-center justify-center bg-gray-100 rounded-lg text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                class="size-16">
                                <path
                                    d="M11.25 4.533A9.707 9.707 0 0 0 6 3a9.735 9.735 0 0 0-3.25.555.75.75 0 0 0-.5.707v14.25a.75.75 0 0 0 1 .707A8.237 8.237 0 0 1 6 18.75c1.995 0 3.823.707 5.25 1.886V4.533ZM12.75 20.636A8.214 8.214 0 0 1 18 18.75c.966 0 1.89.166 2.75.47a.75.75 0 0 0 1-.708V4.262a.75.75 0 0 0-.5-.707A9.735 9.735 0 0 0 18 3a9.707 9.707 0 0 0-5.25 1.533v16.103Z" />
                            </svg>
                        </div>
                    @endif
                    <p @class([
                        'text-sm select-none font-medium text-center line-clamp-2 px-2 py-1 rounded',
                        'bg-primary-500 text-white' => $selectedMaterialId === $material->id,
                    ])>
                        {{ $material->name }}
                    </p>
                    @if ($material->user_id !== auth()->id())
                        <div class="absolute top-0 right-2 p-1 text-white rounded-full shadow bg-primary-500">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                class="size-4">
                                <path
                                    d="M13 4.5a2.5 2.5 0 1 1 .702 1.737L6.97 9.604a2.518 2.518 0 0 1 0 .792l6.733 3.367a2.5 2.5 0 1 1-.671 1.341l-6.733-3.367a2.5 2.5 0 1 1 0-3.475l6.733-3.366A2.52 2.52 0 0 1 13 4.5Z" />
                            </svg>
                        </div>
                    @endif
                </div>
            @endforeach

            {{-- 추가 버튼 --}}
            <div wire:click="mountAction('addNewBookOrFolder')"
                class="flex flex-col items-center space-y-2 hover:brightness-90 cursor-pointer transition-all">
                <div class="aspect-[2/3] w-full flex items-center justify-center bg-gray-100 rounded-lg">
                    <svg class="w-16 h-16 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path
                            d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
                    </svg>
                </div>
            </div>
        </div>
        <x-filament-actions::modals />
    </x-filament::section>
</x-filament-widgets::widget>
