@script
    <script>
        // Livewire.on('onScanStarted', (data) => {
        //     console.log(data[0]);
        //     setTimeout(() => {
        //         document.querySelector('.scan-progress').classList.remove('hidden');
        //         document.querySelector('.scan-progress').classList.add('flex');
        //     }, 10);

        //     initializeProgress(data[0].attachmentName);
        // });

        function initializeProgress(attachment) {
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
                            progressText.textContent = `${data.currentPage} / ${data.totalPages} 처리중 ...`;
                        }

                        // 100% 완료되면 리다이렉트
                        if (data.progress >= 100) {
                            clearInterval(intervalId);
                            setTimeout(() => {
                                document.querySelector('.scan-progress').classList.remove('flex');
                                document.querySelector('.scan-progress').classList.add('hidden');
                            }, 100);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching progress:', error);
                    });
            }
            intervalId = setInterval(checkProgress, 1000);
        }
    </script>
@endscript

<div class="hidden flex-col scan-progress">
    <div class="bg-gray-200 h-2 w-full rounded-full relative">
        <div class="progress-bar bg-primary-500 h-2 rounded-full" style="width: 0%"></div>
    </div>
    <div class="flex justify-between mt-2 text-sm text-gray-600">
        <span class="progress-text">처리중 ...</span>
    </div>
</div>
