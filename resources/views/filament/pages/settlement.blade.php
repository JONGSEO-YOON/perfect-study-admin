<x-filament-panels::page>
    <div class="space-y-6">

        {{-- 기간 필터 --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <div class="flex items-end gap-4">
                <div class="flex-1">
                    {{ $this->form }}
                </div>
                <div class="pb-1">
                    <button wire:click="search" wire:loading.attr="disabled"
                        class="fi-btn fi-btn-size-md relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-primary-600 text-white hover:bg-primary-500 focus-visible:ring-primary-500/50 dark:bg-primary-500 dark:hover:bg-primary-400 dark:focus-visible:ring-primary-400/50">
                        <span wire:loading.remove wire:target="search">조회</span>
                        <span wire:loading wire:target="search">조회 중...</span>
                    </button>
                </div>
            </div>
        </div>

        @if ($errorMessage)
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
                <p class="text-red-700 dark:text-red-300 text-sm">{{ $errorMessage }}</p>
            </div>
        @endif

        {{-- 결산 요약 --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">총 매출</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($summary['total_amount']) }}원</p>
                <p class="text-xs text-gray-400 mt-1">{{ $summary['total_count'] }}건</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">취소/환불</p>
                <p class="text-2xl font-bold text-red-500">{{ number_format($summary['cancel_amount']) }}원</p>
                <p class="text-xs text-gray-400 mt-1">{{ $summary['cancel_count'] }}건</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">공급가액</p>
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($summary['supply_amount']) }}원</p>
                <p class="text-xs text-gray-400 mt-1">매출 - 취소 기준</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">부가세 (10%)</p>
                <p class="text-2xl font-bold text-orange-500">{{ number_format($summary['vat']) }}원</p>
                <p class="text-xs text-gray-400 mt-1">순매출 기준</p>
            </div>
        </div>

        {{-- 결제수단별 요약 --}}
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">카드 결제</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($summary['card_amount']) }}원</p>
                <p class="text-xs text-gray-400 mt-1">{{ $summary['card_count'] }}건</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">기타 결제</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($summary['transfer_amount']) }}원</p>
                <p class="text-xs text-gray-400 mt-1">{{ $summary['transfer_count'] }}건</p>
            </div>
        </div>

        {{-- 거래 내역 테이블 --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">거래 내역 ({{ count($transactions) }}건)</h3>
            </div>

            @if (count($transactions) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">거래일시</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">학생</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">청구명</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">메모</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">결제수단</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400">금액</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400">상태</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($transactions as $tx)
                                @php
                                    $status = $tx['status'] ?? '';
                                    $statusLabel = match($status) {
                                        'DONE' => '완료',
                                        'CANCELED' => '취소',
                                        'PARTIAL_CANCELED' => '부분취소',
                                        'WAITING_FOR_DEPOSIT' => '입금대기',
                                        'IN_PROGRESS' => '진행중',
                                        'EXPIRED' => '만료',
                                        'ABORTED' => '중단',
                                        default => $status,
                                    };
                                    $statusColor = match($status) {
                                        'DONE' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                                        'CANCELED', 'PARTIAL_CANCELED' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
                                        'WAITING_FOR_DEPOSIT', 'IN_PROGRESS' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
                                        default => 'bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-300',
                                    };
                                    $transactionAt = isset($tx['transactionAt']) ? \Carbon\Carbon::parse($tx['transactionAt'])->format('Y-m-d H:i') : '-';
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300">{{ $transactionAt }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300">{{ $tx['_student_name'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300">{{ $tx['_billing_name'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 text-xs">{{ $tx['_billing_memo'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300">{{ $tx['method'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-medium {{ $status === 'CANCELED' || $status === 'PARTIAL_CANCELED' ? 'text-red-500' : 'text-gray-900 dark:text-gray-300' }}">
                                        {{ number_format($tx['amount'] ?? 0) }}원
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <p class="text-gray-500 dark:text-gray-400">해당 기간의 거래 내역이 없습니다.</p>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
