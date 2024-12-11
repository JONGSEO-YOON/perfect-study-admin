<div class="w-full">
    <div class="overflow-x-auto flex flex-col items-start gap-y-4">
        <table class="w-full bg-white border-x border-t">
            <thead>
                <tr class="bg-gray-100 text-center">
                    <th colspan="8" class="px-6 py-3 text-sm font-semibold text-gray-700 border-b">
                        {{ $testsheet->start_date->format('m월 d일') }} {{ $testsheet->name }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr class="bg-gray-100 text-center">
                    <th class="px-6 py-3 text-sm font-semibold text-gray-700 border-b">
                        범위
                    </th>
                    <th class="px-6 py-3 text-sm font-semibold text-gray-700 border-b">
                        개인점수
                    </th>
                    <th class="px-6 py-3 text-sm font-semibold text-gray-700 border-b">
                        반평균
                    </th>
                    <th class="px-6 py-3 text-sm font-semibold text-gray-700 border-b">
                        레벨평균
                    </th>
                    <th class="px-6 py-3 text-sm font-semibold text-gray-700 border-b">
                        반별 등수
                    </th>
                </tr>
                @foreach ($questionTypes as $type)
                    <tr class="hover:bg-gray-50 text-sm text-gray-900 text-center">
                        <td class="px-6 py-3 border-b">{{ $type['name'] }}</td>
                        <td class="px-6 py-3 border-b">
                            {{ $type['personal_score'] }}/{{ $type['personal_total'] }}
                            ({{ $type['personal_score_percentage'] }}%)
                        </td>
                        <td class="px-6 py-3 border-b">
                            {{ $type['classroom_average'] }}/{{ $type['classroom_total'] }}
                            ({{ $type['classroom_average_percentage'] }}%)
                        </td>
                        <td class="px-6 py-3 border-b">
                            {{ $type['level_average'] }}/{{ $type['level_total'] }}
                            ({{ $type['level_average_percentage'] }}%)
                        </td>
                        <td class="px-6 py-3 border-b">
                            {{ $type['classroom_rank'] }}위
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
