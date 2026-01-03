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
    @if (!$cache['is_completed'] ?? true)
        <div class="flex flex-col scan-progress">
            <div class="bg-gray-200 h-3 w-full rounded-full relative">
                <div class="progress-bar bg-primary-500 h-3 rounded-full" style="width: 0%"></div>
            </div>
            <div class="flex justify-between mt-2 text-sm text-gray-600">
                <span class="progress-text">페이지 변환중 ...</span>
            </div>
        </div>
        <script>
            function initializeProgress() {
                let attachment = @json($id);
                let intervalId = null;

                function checkProgress() {
                    fetch(`/api/pdf-conversion-progress/${attachment}`)
                        .then(response => response.json())
                        .then(data => {
                            // 프로그레스바 업데이트
                            const progressBar = document.querySelector('.progress-bar');
                            const progressText = document.querySelector('.progress-text');
                            if (progressBar && progressText) {
                                progressBar.style.width = `${data.progress}%`;
                                progressText.textContent = `${data.currentPage} / ${data.totalPages} 변환중 ...`;
                            }
                            const event = new Event("reloadPages");
                            window.dispatchEvent(event);

                            // 100% 완료되면 리다이렉트
                            if (data.progress >= 100) {
                                clearInterval(intervalId);
                                setTimeout(() => {
                                    document.querySelector('.scan-progress').classList.remove('flex');
                                    document.querySelector('.scan-progress').classList.add('hidden');
                                    location.reload();
                                }, 100);
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching progress:', error);
                        });
                }
                intervalId = setInterval(checkProgress, 1000);
            }
            window.addEventListener('DOMContentLoaded', () => {
                initializeProgress();
            });
        </script>
    @endif
    @livewire(\App\Livewire\PageSelector::class, ['id' => $this->id])
</x-filament-panels::page>
