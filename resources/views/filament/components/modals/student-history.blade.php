<div class="p-4">
    <div class="mb-4 flex items-center gap-3">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $student->user->name }}</h3>
            <p class="text-sm text-gray-500">
                {{ $student->school?->name }} · {{ $student->gradeSystem?->display_name }}
            </p>
        </div>
    </div>

    @if(empty($timeline))
        <div class="text-center py-8 text-gray-500">이력이 없습니다.</div>
    @else
        <div class="relative">
            {{-- 타임라인 세로선 --}}
            <div class="absolute left-5 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>

            <div class="space-y-4">
                @foreach($timeline as $event)
                    @php
                        $colorClasses = match($event['color']) {
                            'blue' => ['bg' => 'bg-blue-100 text-blue-600', 'text' => 'text-blue-700'],
                            'green' => ['bg' => 'bg-green-100 text-green-600', 'text' => 'text-green-700'],
                            'emerald' => ['bg' => 'bg-emerald-100 text-emerald-600', 'text' => 'text-emerald-700'],
                            'red' => ['bg' => 'bg-red-100 text-red-600', 'text' => 'text-red-700'],
                            'orange' => ['bg' => 'bg-orange-100 text-orange-600', 'text' => 'text-orange-700'],
                            'indigo' => ['bg' => 'bg-indigo-100 text-indigo-600', 'text' => 'text-indigo-700'],
                            default => ['bg' => 'bg-gray-100 text-gray-600', 'text' => 'text-gray-700'],
                        };
                    @endphp
                    <div class="relative flex items-start gap-4 pl-12">
                        {{-- 아이콘 원 --}}
                        <div class="absolute left-2 w-7 h-7 rounded-full flex items-center justify-center {{ $colorClasses['bg'] }}">
                            @svg('heroicon-s-' . $event['icon'], 'w-4 h-4')
                        </div>

                        {{-- 내용 --}}
                        <div class="flex-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-sm {{ $colorClasses['text'] }}">
                                    {{ $event['title'] }}
                                </span>
                                <span class="text-xs text-gray-400">
                                    {{ $event['date']->format('Y-m-d H:i') }}
                                </span>
                            </div>
                            @if($event['description'])
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $event['description'] }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
