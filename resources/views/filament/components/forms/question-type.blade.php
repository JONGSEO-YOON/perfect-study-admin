@script
    <script>
        window.addEventListener('selectedQuestionCategoryChanged', (event) => {
            const data = event.data;
            const element = document.getElementById('category-selector');
            if (element) {
                const selectedId = data[0]?.id ?? null;
                Alpine.evaluate(element, 'state = ' + selectedId);
            }

        });
    </script>
@endscript
<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div id="category-selector" x-data="{ state: $wire.$entangle('{{ $getStatePath() }}') }">
        @livewire(\App\Livewire\QuestionCategorySelector::class)
    </div>
</x-dynamic-component>
