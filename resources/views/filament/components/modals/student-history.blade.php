<div class="p-4">
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $student->user->name }}</h3>
        <p class="text-sm text-gray-500 mt-0.5">
            {{ $student->school?->name ?: '-' }} · {{ $student->gradeSystem?->display_name ?: '-' }}
            · <span class="font-medium {{ $student->status === 'withdrawn' ? 'text-red-600' : 'text-green-600' }}">
                {{ match($student->status) { 'active', 'enrolled' => '재원', 'pending' => '승인예정', 'withdrawn' => '퇴원', default => $student->status } }}
            </span>
        </p>
    </div>

    @if(empty($timeline))
        <div class="text-center py-8 text-gray-500">이력이 없습니다.</div>
    @else
        <div class="relative pl-6">
            {{-- 타임라인 세로선 --}}
            <div class="absolute left-[7px] top-3 bottom-3 w-0.5 bg-gray-200 dark:bg-gray-700"></div>

            <div class="space-y-3">
                @foreach($timeline as $event)
                    @php
                        $dotColor = match($event['color']) {
                            'blue' => 'bg-blue-500',
                            'green' => 'bg-green-500',
                            'red' => 'bg-red-500',
                            'orange' => 'bg-orange-500',
                            'indigo' => 'bg-indigo-500',
                            'yellow' => 'bg-yellow-500',
                            'amber' => 'bg-amber-500',
                            'emerald' => 'bg-emerald-500',
                            'gray' => 'bg-gray-500',
                            default => 'bg-gray-500',
                        };
                        $borderColor = match($event['color']) {
                            'blue' => 'border-blue-200 dark:border-blue-800',
                            'green' => 'border-green-200 dark:border-green-800',
                            'red' => 'border-red-200 dark:border-red-800',
                            'orange' => 'border-orange-200 dark:border-orange-800',
                            'indigo' => 'border-indigo-200 dark:border-indigo-800',
                            'yellow' => 'border-yellow-200 dark:border-yellow-800',
                            'amber' => 'border-amber-200 dark:border-amber-800',
                            'emerald' => 'border-emerald-200 dark:border-emerald-800',
                            'gray' => 'border-gray-300 dark:border-gray-600',
                            default => 'border-gray-200 dark:border-gray-700',
                        };
                        $bgColor = match($event['color']) {
                            'red' => 'bg-red-50 dark:bg-red-950/30',
                            'indigo' => 'bg-indigo-50 dark:bg-indigo-950/30',
                            'yellow' => 'bg-yellow-50 dark:bg-yellow-950/30',
                            'amber' => 'bg-amber-50 dark:bg-amber-950/30',
                            'emerald' => 'bg-emerald-50 dark:bg-emerald-950/30',
                            'gray' => 'bg-gray-50 dark:bg-gray-800',
                            default => 'bg-white dark:bg-gray-800',
                        };
                        $titleColor = match($event['color']) {
                            'blue' => 'text-blue-700 dark:text-blue-400',
                            'green' => 'text-green-700 dark:text-green-400',
                            'red' => 'text-red-700 dark:text-red-400',
                            'orange' => 'text-orange-700 dark:text-orange-400',
                            'indigo' => 'text-indigo-700 dark:text-indigo-400',
                            'yellow' => 'text-yellow-700 dark:text-yellow-400',
                            'amber' => 'text-amber-700 dark:text-amber-400',
                            'emerald' => 'text-emerald-700 dark:text-emerald-400',
                            'gray' => 'text-gray-700 dark:text-gray-400',
                            default => 'text-gray-700 dark:text-gray-400',
                        };

                        $details = array_filter($event['details'] ?? [], fn($v) => !empty($v));
                    @endphp

                    <div class="relative">
                        {{-- 타임라인 점 (제목 텍스트 중앙에 정렬) --}}
                        <div class="absolute -left-[21px] top-[14px] w-2.5 h-2.5 rounded-full {{ $dotColor }} ring-2 ring-white dark:ring-gray-900"></div>

                        <div class="{{ $bgColor }} border {{ $borderColor }} rounded-lg px-3 py-2.5">
                            {{-- 헤더: 제목 + 날짜 --}}
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-semibold text-sm {{ $titleColor }}">
                                    {{ $event['title'] }}
                                </span>
                                <span class="text-xs text-gray-400 dark:text-gray-500">
                                    {{ $event['date']->format('Y-m-d H:i') }}
                                </span>
                            </div>

                            {{-- 본문: description + details를 동일 grid로 정렬 --}}
                            @if($event['description'] || !empty($details))
                                <div class="grid grid-cols-[5rem_1fr] gap-x-3 gap-y-1 text-xs">
                                    @if($event['description'])
                                        <div class="col-span-2 text-sm text-gray-800 dark:text-gray-200 -mb-0.5">
                                            {{ $event['description'] }}
                                        </div>
                                    @endif

                                    @foreach($details as $label => $value)
                                        <div class="text-gray-500 dark:text-gray-500">{{ $label }}</div>
                                        <div class="text-gray-700 dark:text-gray-300 break-words">{{ $value }}</div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
