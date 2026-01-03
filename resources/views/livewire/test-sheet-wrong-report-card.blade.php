<div class="w-full" wire:init="initAction()">
    <div class="overflow-x-auto flex flex-col items-start gap-y-4">
        <table class="w-full bg-white border-x border-t border-collapse">
            <thead>
                <tr class="bg-gray-100 text-center">
                    <th colspan="3" class="px-6 py-3 text-sm font-semibold text-gray-700 border-b">
                        {{ $student?->user?->name }}학생 {{ $testsheet->start_date->format('m월 d일') }}
                        {{ $testsheet->name }} 오답 문풀 분석표
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr class="bg-gray-100 text-center">
                    <th class="px-4 py-3 text-sm font-semibold text-gray-700 border-b">문항 번호</th>
                    <th class="px-4 py-3 text-sm font-semibold text-gray-700 border-b">오답 유사 유형</th>
                    <th class="px-4 py-3 text-sm font-semibold text-gray-700 border-b">오답 테스트</th>
                </tr>
                @foreach ($report ?? [] as $item)
                    <tr class="hover:bg-gray-50 text-sm text-gray-900 text-center">
                        <td class="px-4 py-3 border-b">{{ $item['original_seq'] }}번</td>
                        <td class="px-4 py-3 border-b">
                            @if ($item['first_retry_correct'])
                                <span class="text-blue-600">O</span>
                            @else
                                <span class="text-red-600">X</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 border-b">
                            @if ($item['second_retry_correct'] === true)
                                <span class="text-blue-600">O</span>
                            @elseif ($item['second_retry_correct'] === false)
                                <span class="text-red-600">X</span>
                            @else
                                <span class="text-gray-600">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
