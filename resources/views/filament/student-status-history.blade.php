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
                            'active' => '재원',
                            'pending' => '승인예정',
                            'withdrawn' => '퇴원',
                            default => $s,
                        };
                    @endphp
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-300">{{ $h->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-300">{{ $statusLabel($h->from_status) }} → {{ $statusLabel($h->to_status) }}</td>
                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">{{ $h->reason ?? '-' }}</td>
                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">{{ $h->changedBy?->name ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
