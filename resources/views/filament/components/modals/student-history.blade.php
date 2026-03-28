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
            <div class="space-y-3">
                @foreach($timeline as $event)
                    @php
                        $colorClasses = match($event['color']) {
                            'blue' => 'text-blue-700',
                            'green' => 'text-green-700',
                            'emerald' => 'text-emerald-700',
                            'red' => 'text-red-700',
                            'orange' => 'text-orange-700',
                            'indigo' => 'text-indigo-700',
                            default => 'text-gray-700',
                        };
                    @endphp
                    <div>
                        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-sm {{ $colorClasses }}">
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
    @endif
</div>
