@php
    $colors = [
        '#0EA5E9', // sky-500
        '#F43F5E', // rose-500
        '#8B5CF6', // violet-500
        '#22C55E', // green-500
        '#EAB308', // yellow-500
        '#F97316', // orange-500
    ];
@endphp
<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div class="flex items-center space-x-2" x-data="{ state: $wire.$entangle('{{ $getStatePath() }}') }">
        @foreach ($colors as $color)
            <button type="button"
                class="w-8 h-8 rounded-full border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500"
                :class="{ 'ring-2 ring-primary-500': state === '{{ $color }}' }"
                style="background-color: {{ $color }}"
                @click="state = '{{ $color }}'; $wire.dispatch('onColorChanged', { color: '{{ $color }}' })"></button>
        @endforeach
    </div>
</x-dynamic-component>
