<div class="space-y-2">
    @if ($histories->isEmpty())
        <p class="text-center text-gray-500 dark:text-gray-400 py-4">히스토리가 없습니다.</p>
    @else
        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">일시</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">변경</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">사유</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">처리자</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($histories as $h)
                    @php
                        $statusLabel = fn($s) => match($s) {
                            'enrolled' => '재원',
                            'active' => '재원',
                            'pending' => '승인예정',
                            'withdrawn' => '퇴원',
                            default => $s,
                        };
                    @endphp
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-300">{{ $h->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-300">{{ $statusLabel($h->from_status) }} → {{ $statusLabel($h->to_status) }}</td>
                        @php
                            $reasonLabel = match($h->reason) {
                                'poor_performance' => '성적부진',
                                'change_of_atmosphere' => '분위기전환',
                                'teacher_mismatch' => '선생님맞지않음',
                                'academy_atmosphere' => '학원분위기안좋음',
                                'relocation' => '이사',
                                'family_circumstances' => '가정형편',
                                'friend_conflict' => '친구와의불화',
                                'gave_up_studying' => '공부포기',
                                'other' => '기타',
                                default => $h->reason,
                            };
                        @endphp
                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">{{ $reasonLabel ?? '-' }}</td>
                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">{{ $h->changedBy?->name ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
