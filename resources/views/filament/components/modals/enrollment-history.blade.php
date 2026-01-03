<div class="space-y-4">
    @if($record->enrollmentHistory->count() > 0)
        <div class="overflow-hidden border border-gray-200 dark:border-gray-700 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">구분</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">일자</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">사유</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">담임</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">처리자</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($record->enrollmentHistory as $history)
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @switch($history->action)
                                    @case('enroll') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @break
                                    @case('withdraw') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @break
                                    @case('re_enroll') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 @break
                                @endswitch
                            ">
                                {{ $history->action_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            {{ $history->action_date->format('Y-m-d') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                            @if($history->reason)
                                {{ \App\Models\Student::WITHDRAWAL_REASONS[$history->reason] ?? $history->reason }}
                                @if($history->reason_detail)
                                    <br><span class="text-xs text-gray-400">{{ Str::limit($history->reason_detail, 30) }}</span>
                                @endif
                            @elseif($history->memo)
                                <span class="text-xs text-gray-400">{{ Str::limit($history->memo, 30) }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $history->homeroomTeacher?->user?->name ?? '-' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $history->processedBy?->name ?? '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">입퇴원 이력이 없습니다.</p>
        </div>
    @endif
</div>
