<div class="bg-white overflow-hidden">
    <div>
        <div class="flex items-center mb-4 p-6">
            <div class="w-2 h-6 bg-teal-500 rounded-full mr-3"></div>
            <h3 class="text-lg font-bold text-stone-800">오답 문풀 분석표</h3>
        </div>
        @forelse ($wrongReports as $index => $report)
            <div @class([
                // 'border-b' => $index < count($wrongReports) - 2,
                'border-r' => $index % 2 === 0,
            ])>
                <table class="w-full table-auto border-collapse border border-stone-200 rounded-lg overflow-hidden">
                    <thead>
                        <tr class="bg-stone-50 text-center">
                            <th colspan="4" class="px-1.5 py-3 text-xs font-medium text-stone-700 border border-stone-200">
                                {{ $report['date'] }} {{ $report['name'] }} 오답
                            </th>
                        </tr>
                        <tr class="bg-stone-50 text-center">
                            <th class="px-1.5 py-3 text-xs font-medium text-stone-700 border border-stone-200">
                                오답문항
                            </th>
                            <th class="px-1.5 py-3 text-xs font-medium text-stone-700 border border-stone-200">
                                오답유사유형 정답
                            </th>
                            <th class="px-1.5 py-3 text-xs font-medium text-stone-700 border border-stone-200">
                                오답테스트
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($report['report'] as $item)
                            <tr class="hover:bg-stone-50 transition-colors text-xs text-stone-900 text-center">
                                <td class="px-1 py-3 border border-stone-200">{{ $item['original_seq'] }}</td>
                                <td class="px-1 py-3 border border-stone-200">
                                    @if ($item['first_retry_correct'])
                                        <span class="text-blue-600">O</span>
                                    @else
                                        <span class="text-red-600">X</span>
                                    @endif
                                </td>
                                <td class="px-1 py-3 border border-stone-200">
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
        @empty
            <div class="w-full text-center py-4 text-gray-500 col-span-2">
                해당 기간에 검색된 주간 보고서가 없습니다.
            </div>
        @endforelse
    </div>
</div>