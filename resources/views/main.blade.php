<x-layouts.student>
    <div class="flex-1 flex flex-col h-0">
        <div class="border-b flex flex-row px-5 gap-x-1">
            <a href="/"
                class="text-xl md:text-2xl {{ $type !== 'homework' ? 'border-b-[3px] border-[#7256C2] font-bold' : 'text-[#A3A3A3] font-semibold' }} py-2.5 md:py-4 px-3">
                시험
                @if ($remaining_count['test'] > 0)
                    <span class="text-red-300 text-base">({{ $remaining_count['test'] }})</span>
                @endif
            </a>
            <a href="/?type=homework"
                class="text-xl md:text-2xl {{ $type === 'homework' ? 'border-b-[3px] border-[#7256C2] font-bold' : 'text-[#A3A3A3] font-semibold' }} py-2.5 md:py-4 px-3">
                숙제
                @if ($remaining_count['homework'] > 0)
                    <span class="text-red-300 text-base">({{ $remaining_count['homework'] }})</span>
                @endif
            </a>
        </div>
        <div class="flex-1 overflow-auto h-0">
            <div class="max-w-[740px] mx-auto w-full px-5 py-4">
                @if ($testsheets->isEmpty())
                    <div class="flex flex-col items-center justify-center h-[200px]">
                        <p class="text-[#A3A3A3] mt-4 text-lg">아직 등록된 문제지가 없습니다.</p>
                    </div>
                @endif
                <x-test-sheet-list :testsheets="$testsheets" />
                @if ($testsheets->hasMorePages())
                    <div class="flex flex-col justify-center mt-4">
                        <button id="load-more" data-type="{{ $type }}" data-per-page="{{ $perPage }}"
                            data-current-page="{{ $testsheets->currentPage() }}"
                            class="px-6 py-2 bg-[#F4F4F4] text-[#7D7D92] rounded-lg hover:bg-[#E9E9E9] transition-colors font-medium">
                            더보기
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loadMoreBtn = document.getElementById('load-more');
            if (loadMoreBtn) {
                loadMoreBtn.addEventListener('click', function() {
                    const nextPage = parseInt(this.dataset.currentPage) + 1;
                    const per_page = this.dataset.perPage;
                    const type = this.dataset.type;

                    fetch(`/?page=${nextPage}&per_page=${per_page}&type=${type}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.text())
                        .then(html => {
                            const testSheetList = document.getElementById('test-sheet-list');
                            testSheetList.insertAdjacentHTML('beforeend', html);

                            // 현재 페이지 업데이트
                            this.dataset.currentPage = nextPage;

                            // 더 이상 데이터가 없으면 버튼 숨기기
                            if (!html.trim()) {
                                this.style.display = 'none';
                            }
                        });
                });
            }
        });
    </script>
</x-layouts.student>
