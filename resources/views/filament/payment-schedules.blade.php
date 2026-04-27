<div class="space-y-4">
    @if ($schedules->isEmpty())
        <div class="text-center py-8">
            <p class="text-gray-500 dark:text-gray-400">등록된 예약 알림이 없습니다.</p>
            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">"예약 추가" 버튼으로 새 예약을 등록하세요.</p>
        </div>
    @else
        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">대상</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">청구명</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400">금액</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400">발송일</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400">상태</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">마지막 발송</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400">관리</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($schedules as $schedule)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-300">
                            {{ $schedule->getTargetLabel() }}
                            <span class="text-xs text-gray-400">({{ count($schedule->target_ids) }}개)</span>
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-300">{{ $schedule->billing_name }}</td>
                        <td class="px-4 py-2 text-sm text-right text-gray-900 dark:text-gray-300">{{ number_format($schedule->amount) }}원</td>
                        <td class="px-4 py-2 text-sm text-center text-gray-900 dark:text-gray-300">매월 {{ $schedule->send_day }}일</td>
                        <td class="px-4 py-2 text-center">
                            @if ($schedule->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300">활성</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-300">비활성</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
                            {{ $schedule->last_sent_at?->format('Y-m-d') ?? '-' }}
                        </td>
                        <td class="px-4 py-2 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button
                                    type="button"
                                    wire:click="openEditScheduleModal({{ $schedule->id }})"
                                    class="text-xs text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 font-medium"
                                >
                                    수정
                                </button>
                                <span class="text-gray-300 dark:text-gray-600">|</span>
                                @if ($schedule->is_active)
                                    <button
                                        type="button"
                                        wire:click="togglePaymentSchedule({{ $schedule->id }})"
                                        wire:confirm="이 예약 알림을 비활성화하시겠습니까?"
                                        class="text-xs text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-300 font-medium"
                                    >
                                        비활성
                                    </button>
                                @else
                                    <button
                                        type="button"
                                        wire:click="togglePaymentSchedule({{ $schedule->id }})"
                                        wire:confirm="이 예약 알림을 다시 활성화하시겠습니까?"
                                        class="text-xs text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 font-medium"
                                    >
                                        활성
                                    </button>
                                @endif
                                <span class="text-gray-300 dark:text-gray-600">|</span>
                                <button
                                    type="button"
                                    wire:click="deletePaymentSchedule({{ $schedule->id }})"
                                    wire:confirm="이 예약 알림을 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다."
                                    class="text-xs text-danger-600 hover:text-danger-800 dark:text-danger-400 dark:hover:text-danger-300 font-medium"
                                >
                                    삭제
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
