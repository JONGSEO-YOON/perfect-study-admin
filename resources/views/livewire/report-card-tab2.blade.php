<div class="w-full">
    <div class="overflow-x-auto rounded-b-[0.8rem] grid grid-cols-2">
        @forelse ($wrongReports as $index => $report)
            <div @class([
                // 'border-b' => $index < count($wrongReports) - 2,
                'border-r' => $index % 2 === 0,
            ])>
                <table class="min-w-full bg-white">
                    <thead>
                        <tr class="bg-gray-100 text-center">
                            <th colspan="4" class="px-1.5 py-3 text-sm font-semibold text-gray-700 border-b">
                                {{ $report['date'] }} {{ $report['name'] }} 오답
                            </th>
                        </tr>
                        <tr class="bg-gray-100 text-center">
                            <th class="px-1.5 py-3 text-sm font-semibold text-gray-700 border-b">
                                오답문항
                            </th>
                            <th class="px-1.5 py-3 text-sm font-semibold text-gray-700 border-b">
                                오답유사유형 정답
                            </th>
                            <th class="px-1.5 py-3 text-sm font-semibold text-gray-700 border-b">
                                오답테스트
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($report['report'] as $item)
                            <tr class="hover:bg-gray-50 text-sm text-gray-900 text-center">
                                <td class="px-1 py-3 border-b">{{ $item['original_seq'] }}</td>
                                <td class="px-1 py-3 border-b">
                                    @if ($item['first_retry_correct'])
                                        <span class="text-blue-600">O</span>
                                    @else
                                        <span class="text-red-600">X</span>
                                    @endif
                                </td>
                                <td class="px-1 py-3 border-b">
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
