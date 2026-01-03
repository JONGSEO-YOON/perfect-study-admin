<div class="min-h-screen">
    <!-- Main Content Area -->
    <main class="max-w-6xl mx-auto px-2 sm:px-6 lg:px-8 py-2 sm:py-8">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="{{ route('parent.report') }}" class="inline-flex items-center text-fuchsia-600 hover:text-fuchsia-800" wire:navigate>
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                뒤로가기
            </a>
        </div>

        <!-- 주차 헤더 -->
        <div class="bg-white border border-purple-200 rounded-lg shadow-sm overflow-hidden mb-6">
            <div class="relative">
                <!-- 배경 -->
                <div class="absolute inset-0 bg-fuchsia-100"></div>
                
                <!-- 메인 컨테이너 -->
                <div class="relative px-6 py-4">
                    <div class="flex items-center justify-center">
                        <!-- 왼쪽 장식 -->
                        <div class="w-12 h-px bg-gradient-to-r from-transparent to-fuchsia-400"></div>
                        
                        <!-- 주간 라벨 -->
                        <div class="px-6 text-center">
                            <div class="text-fuchsia-800 text-lg font-bold tracking-wide">
                                {{ $weekLabel }}
                            </div>
                            <div class="text-fuchsia-600 text-sm mt-1">주간 학습 보고서 상세</div>
                        </div>
                        
                        <!-- 오른쪽 장식 -->
                        <div class="w-12 h-px bg-gradient-to-l from-transparent to-violet-400"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 탭 네비게이션 -->
        <div class="bg-white border border-purple-200 rounded-lg shadow-sm mb-6">
            <!-- 모바일에서 스크롤 가능한 탭 -->
            <div class="flex border-b border-gray-200 overflow-x-auto scrollbar-hide">
                <button 
                    wire:click="setActiveTab(1)"
                    class="flex-shrink-0 px-3 sm:px-4 py-3 text-xs sm:text-sm font-medium transition-colors whitespace-nowrap {{ $activeTab === 1 ? 'text-fuchsia-600 border-b-2 border-fuchsia-600 bg-fuchsia-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}"
                >
                    <span class="hidden sm:inline">주간 학습표</span>
                    <span class="sm:hidden">학습표</span>
                </button>
                <button 
                    wire:click="setActiveTab(2)"
                    class="flex-shrink-0 px-3 sm:px-4 py-3 text-xs sm:text-sm font-medium transition-colors whitespace-nowrap {{ $activeTab === 2 ? 'text-fuchsia-600 border-b-2 border-fuchsia-600 bg-fuchsia-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}"
                >
                    <span class="hidden sm:inline">오답 유형분석표</span>
                    <span class="sm:hidden">유형분석</span>
                </button>
                <button 
                    wire:click="setActiveTab(3)"
                    class="flex-shrink-0 px-3 sm:px-4 py-3 text-xs sm:text-sm font-medium transition-colors whitespace-nowrap {{ $activeTab === 3 ? 'text-fuchsia-600 border-b-2 border-fuchsia-600 bg-fuchsia-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}"
                >
                    <span class="hidden sm:inline">오답 유형분석표 - 학부모</span>
                    <span class="sm:hidden">유형분석-학부모</span>
                </button>
                <button 
                    wire:click="setActiveTab(4)"
                    class="flex-shrink-0 px-3 sm:px-4 py-3 text-xs sm:text-sm font-medium transition-colors whitespace-nowrap {{ $activeTab === 4 ? 'text-fuchsia-600 border-b-2 border-fuchsia-600 bg-fuchsia-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}"
                >
                    <span class="hidden sm:inline">오답 문풀 분석표</span>
                    <span class="sm:hidden">문풀분석</span>
                </button>
            </div>
        </div>

        <!-- 탭 컨텐츠 -->
        <div class="tab-content">
            @if ($activeTab === 1)
                <livewire:parent.report-tab1 :weekKey="$weekKey" :student="$student" :classroomId="$classroomId" />
            @elseif ($activeTab === 2)
                <livewire:parent.report-tab2 :weekKey="$weekKey" :student="$student" :classroomId="$classroomId" />
            @elseif ($activeTab === 3)
                <livewire:parent.report-tab3 :weekKey="$weekKey" :student="$student" :classroomId="$classroomId" />
            @elseif ($activeTab === 4)
                <livewire:parent.report-tab4 :weekKey="$weekKey" :student="$student" :classroomId="$classroomId" />
            @endif
        </div>
    </main>
</div>