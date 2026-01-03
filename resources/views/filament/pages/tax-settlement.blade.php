<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-filament::section>
                <x-slot name="heading">조회 기간</x-slot>
                <div class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ \Carbon\Carbon::parse($startDate)->format('Y.m.d') }} ~ {{ \Carbon\Carbon::parse($endDate)->format('Y.m.d') }}
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">거래 건수</x-slot>
                <div class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                    {{ number_format($transactionCount) }}건
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">총 매출액</x-slot>
                <div class="text-2xl font-bold text-success-600 dark:text-success-400">
                    ₩{{ number_format($totalAmount) }}
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">부가세 (VAT 10%)</x-slot>
                <div class="text-2xl font-bold text-danger-600 dark:text-danger-400">
                    ₩{{ number_format($taxAmount) }}
                </div>
            </x-filament::section>
        </div>

        <x-filament::section>
            <x-slot name="heading">부가세 상세</x-slot>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">항목</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">금액</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">총 매출액 (공급대가)</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">₩{{ number_format($totalAmount) }}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">공급가액 (VAT 제외)</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">₩{{ number_format($supplyAmount) }}</td>
                        </tr>
                        <tr class="bg-gray-50 dark:bg-gray-900">
                            <td class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-300">부가세 (VAT 10%)</td>
                            <td class="px-4 py-3 text-right font-bold text-danger-600 dark:text-danger-400">₩{{ number_format($taxAmount) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        @if(count($monthlyBreakdown) > 0)
        <x-filament::section>
            <x-slot name="heading">월별 상세 내역</x-slot>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">기간</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">거래 건수</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">총 매출액</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">공급가액</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">부가세</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($monthlyBreakdown as $month)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $month['display'] }}</td>
                            <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400">{{ number_format($month['count']) }}건</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">₩{{ number_format($month['total']) }}</td>
                            <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400">₩{{ number_format($month['supply']) }}</td>
                            <td class="px-4 py-3 text-right text-danger-600 dark:text-danger-400">₩{{ number_format($month['tax']) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">합계</td>
                            <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">{{ number_format($transactionCount) }}건</td>
                            <td class="px-4 py-3 text-right font-bold text-success-600 dark:text-success-400">₩{{ number_format($totalAmount) }}</td>
                            <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">₩{{ number_format($supplyAmount) }}</td>
                            <td class="px-4 py-3 text-right font-bold text-danger-600 dark:text-danger-400">₩{{ number_format($taxAmount) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-filament::section>
        @else
        <x-filament::section>
            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                선택한 기간에 결제 완료된 내역이 없습니다.
            </div>
        </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
