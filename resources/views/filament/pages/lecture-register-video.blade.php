@script
    <script>
        let callback = null;

        window.addEventListener('addNewItem', (event) => {
            callback = event.callback;
            Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'))
                .mountAction('addNewItem', {
                    ...event.data
                });
        });

        window.addEventListener('editItem', (event) => {
            callback = event.callback;
            Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'))
                .mountAction('editItem', {
                    ...event.data
                });
        });

        window.addEventListener('viewHistory', (event) => {
            Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'))
                .mountAction('viewHistory', {
                    ...event.data
                });
        });


        Livewire.on('add-new-item', (data) => {
            if (callback) {
                callback(data[0]);
            }
        });

        Livewire.on('edit-item', (data) => {
            if (callback) {
                callback(data[0]);
            }
        });
    </script>
@endscript

<div>
    @livewire(\App\Livewire\LectureVideoList::class, [
        'id' => $id,
    ])
    <x-filament-actions::modals />
</div>
