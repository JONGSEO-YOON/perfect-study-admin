@script
    <script>
        let callback = null;

        window.addEventListener('editQuestion', (event) => {
            callback = event.callback;
            Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'))
                .mountAction('editQuestion', {
                    ...event.data
                });
        });

        window.addEventListener('deleteQuestion', (event) => {
            callback = event.callback;
            Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'))
                .mountAction('deleteQuestion');
        });

        window.addEventListener('addQuestions', (event) => {
            callback = event.callback;
            Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'))
                .mountAction('addQuestions');
        });

        Livewire.on('edit-question', (data) => {
            if (callback) {
                callback(data[0]);
            }
        });

        Livewire.on('delete-question', () => {
            if (callback) {
                callback();
            }
        });

        Livewire.on('add-questions', () => {
            if (callback) {
                callback();
            }
        });
    </script>
@endscript
<x-filament-panels::page>
    @livewire(\App\Livewire\PageSelector::class, ['id' => $this->id])
</x-filament-panels::page>
