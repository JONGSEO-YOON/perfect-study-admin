@script
    <script>
        window.addEventListener('selectedQuestionCategoryChanged', (event) => {
            const data = event.data;
            const element = document.getElementById('category-selector');
            if (element) {
                let multiple = '{{ $multiple ?? false }}';
                let event = '{{ $event ?? false }}';
                if (multiple) {
                    Alpine.evaluate(element, 'state = ' + JSON.stringify(data.map(item => item.id)));
                    if (event) {
                        Livewire.dispatch(event, data.map(item => item.id))
                    }
                    return;
                }

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
            'maxDepth' => $maxDepth ?? null,
        ])
    </div>
</x-dynamic-component>
