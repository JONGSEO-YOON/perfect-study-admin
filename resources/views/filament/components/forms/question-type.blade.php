@script
    <script>
        window.addEventListener('selectedQuestionCategoryChanged', (event) => {
            const data = event.data;
            const element = document.getElementById('category-selector');
            if (element) {
                let multiple = '{{ $multiple ?? false }}';
                if (multiple) {
                    console.log('here');
                    Alpine.evaluate(element, 'state = ' + JSON.stringify(data.map(item => item.id)));
                    return;
                }
                // @if ($multiple ?? false)
                //     console.log('here');
                //     Alpine.evaluate(element, 'state = ' + JSON.stringify(data.map(item => item.id)));
                //     return;
                // @endif
                const selectedId = data[0]?.id ?? null;
                Alpine.evaluate(element, 'state = ' + selectedId);
            }

        });
    </script>
@endscript
<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div id="category-selector" x-data="{ state: $wire.$entangle('{{ $getStatePath() }}') }">
        @livewire(\App\Livewire\QuestionCategorySelector::class, [
            'selectedId' => $getState(),
            'multiple' => $multiple ?? false,
        ])
    </div>
</x-dynamic-component>
