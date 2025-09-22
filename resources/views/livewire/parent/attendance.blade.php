<div class="min-h-screen">

    <!-- Main Content Area -->
    <main class="max-w-6xl mx-auto px-2 sm:px-4 lg:px-6 py-2 sm:py-4">
        <!-- Date Range Filter -->
        <div class="bg-violet-50 p-3 sm:p-4 rounded-lg shadow-sm mb-4">
            <h2 class="text-lg sm:text-xl font-bold text-stone-900 mb-3">출결 기록 조회</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-violet-600">시작 날짜</label>
                    <input type="date" 
                           wire:model.live="startDate"
                           id="start_date"
                           class="mt-1 block w-full rounded-md border-transparent bg-violet-500 text-white placeholder-white/70 shadow-sm focus:border-violet-300 focus:ring-violet-300 sm:text-sm text-sm"
                           style="color-scheme: dark;">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-violet-600">종료 날짜</label>
                    <input type="date" 
                           wire:model.live="endDate"
                           id="end_date"
                           class="mt-1 block w-full rounded-md border-transparent bg-violet-500 text-white placeholder-white/70 shadow-sm focus:border-violet-300 focus:ring-violet-300 sm:text-sm text-sm"
                           style="color-scheme: dark;">
                </div>
            </div>
        </div>

        <!-- Student Info -->
        @if($student)
        <div class="bg-white rounded-lg shadow-sm p-3 sm:p-4 mb-4">
            <div class="flex items-center">
                <div class="bg-violet-100 rounded-full p-2 sm:p-3 mr-3">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-stone-900">{{ $student?->user?->name }} 학생</h3>
                    <p class="text-xs sm:text-sm text-stone-600">{{ $startDate }} ~ {{ $endDate }} 출결 기록</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Attendance Records -->
        @if(count($attendances) > 0)
            <div class="space-y-3">
                @foreach($attendances as $dateKey => $dayAttendance)
                    <div class="bg-white rounded-lg shadow-sm p-3 sm:p-4">
                        <div class="flex items-center justify-between mb-2 sm:mb-3">
                            <div class="flex items-center">
                                <div class="bg-blue-100 rounded-full p-1.5 sm:p-2 mr-2 sm:mr-3">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-base sm:text-lg font-semibold text-stone-900">{{ $dayAttendance['date'] }} {{ $dayAttendance['day_of_week'] }}</h4>
                                    <p class="text-xs sm:text-sm text-stone-600">{{ count($dayAttendance['records']) }}개의 출결 기록</p>
                                </div>
                            </div>
                            
                        </div>
                        
                        @if(count($dayAttendance['records']) > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3">
                                @foreach($dayAttendance['records'] as $record)
                                    <div class="bg-stone-50 rounded-lg p-2 sm:p-3 border-l-4" style="border-left-color: {{ $record['color'] }};">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <div class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full mr-2 sm:mr-3" style="background-color: {{ $record['color'] }};"></div>
                                                <div>
                                                    <p class="text-xs sm:text-sm font-medium text-stone-900">{{ $record['title'] }}</p>
                                                    <p class="text-xs sm:text-sm text-stone-600">{{ $record['time'] }}</p>
                                                </div>
                                            </div>
                                            @if(!empty($record['memo']))
                                                <button wire:click="showMemo('{{ $loop->parent->index }}', '{{ $loop->index }}')"
                                                        class="inline-flex items-center px-1.5 py-1 text-xs font-medium text-gray-700 bg-gray-100 rounded hover:bg-gray-200 transition-colors ml-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-3 sm:py-4">
                                <p class="text-stone-500 text-sm">해당 날짜에 출결 기록이 없습니다.</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <!-- No Records -->
            <div class="bg-white rounded-lg shadow-sm p-6 sm:p-8">
                <div class="text-center">
                    <svg class="mx-auto h-10 w-10 sm:h-12 sm:w-12 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-stone-900">출결 기록이 없습니다</h3>
                    <p class="mt-1 text-sm text-stone-500">선택한 기간 동안의 출결 기록이 없습니다.</p>
                </div>
            </div>
        @endif

        <!-- Legend -->
        <div class="bg-white rounded-lg shadow-sm p-3 sm:p-4 mt-4 sm:mt-6">
            <h4 class="text-sm font-medium text-stone-900 mb-2 sm:mb-3">범례</h4>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3 text-xs sm:text-sm">
                <div class="flex items-center">
                    <div class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full mr-1.5 sm:mr-2" style="background-color: #8b5cf6;"></div>
                    <span class="text-stone-700">(정규)등원</span>
                </div>
                <div class="flex items-center">
                    <div class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full mr-1.5 sm:mr-2" style="background-color: #a78bfa;"></div>
                    <span class="text-stone-700">(정규)하원</span>
                </div>
                <div class="flex items-center">
                    <div class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full mr-1.5 sm:mr-2" style="background-color: #06b6d4;"></div>
                    <span class="text-stone-700">(보충)등원</span>
                </div>
                <div class="flex items-center">
                    <div class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full mr-1.5 sm:mr-2" style="background-color: #22d3ee;"></div>
                    <span class="text-stone-700">(보충)하원</span>
                </div>
                <div class="flex items-center">
                    <div class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full mr-1.5 sm:mr-2" style="background-color: #f59e0b;"></div>
                    <span class="text-stone-700">지각</span>
                </div>
            </div>
        </div>
    </main>

    <!-- Memo Modal -->
    @if($showMemoModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50" wire:click="closeMemoModal">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
                <div class="p-4 sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-stone-900">{{ $selectedDate }} 메모</h3>
                        <button wire:click="closeMemoModal" class="text-stone-400 hover:text-stone-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="bg-stone-50 rounded-lg p-3 sm:p-4">
                        <p class="text-sm text-stone-700 whitespace-pre-wrap">{{ $selectedMemo }}</p>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button wire:click="closeMemoModal"
                                class="px-4 py-2 bg-violet-600 text-white rounded-md hover:bg-violet-700 transition-colors text-sm font-medium">
                            닫기
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
