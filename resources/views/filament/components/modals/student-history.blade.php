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
                    <div class="relative flex items-start gap-4 pl-12">
                        {{-- 아이콘 원 --}}
                        <div class="absolute left-2 w-7 h-7 rounded-full flex items-center justify-center
                            @switch($event['color'])
                                @case('blue') bg-blue-100 text-blue-600 @break
                                @case('green') bg-green-100 text-green-600 @break
                                @case('emerald') bg-emerald-100 text-emerald-600 @break
                                @case('red') bg-red-100 text-red-600 @break
                                @case('orange') bg-orange-100 text-orange-600 @break
                                @case('indigo') bg-indigo-100 text-indigo-600 @break
                                @default bg-gray-100 text-gray-600
                            @endswitch
                        ">
                            @switch($event['icon'])
                                @case('user-plus')
                                    <x-heroicon-m-user-plus class="w-4 h-4" />
                                    @break
                                @case('user-minus')
                                    <x-heroicon-m-user-minus class="w-4 h-4" />
                                    @break
                                @case('credit-card')
                                    <x-heroicon-m-credit-card class="w-4 h-4" />
                                    @break
                                @case('x-circle')
                                    <x-heroicon-m-x-circle class="w-4 h-4" />
                                    @break
                                @case('arrow-path')
                                    <x-heroicon-m-arrow-path class="w-4 h-4" />
                                    @break
                                @case('check-circle')
                                    <x-heroicon-m-check-circle class="w-4 h-4" />
                                    @break
                                @case('academic-cap')
                                    <x-heroicon-m-academic-cap class="w-4 h-4" />
                                    @break
                                @default
                                    <x-heroicon-m-clock class="w-4 h-4" />
                            @endswitch
                        </div>

                        {{-- 내용 --}}
                        <div class="flex-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-sm
                                    @switch($event['color'])
                                        @case('blue') text-blue-700 @break
                                        @case('green') text-green-700 @break
                                        @case('emerald') text-emerald-700 @break
                                        @case('red') text-red-700 @break
                                        @case('orange') text-orange-700 @break
                                        @case('indigo') text-indigo-700 @break
                                        @default text-gray-700
                                    @endswitch
                                ">
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
