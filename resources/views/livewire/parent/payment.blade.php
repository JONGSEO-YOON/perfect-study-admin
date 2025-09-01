<div class="min-h-screen">
    <!-- Main Content Area -->
    <main class="max-w-6xl mx-auto px-2 sm:px-4 lg:px-6 py-2 sm:py-4">
        <!-- Date Range Filter -->
        <div class="bg-sky-50 p-3 sm:p-4 rounded-lg shadow-sm mb-4">
            <h2 class="text-lg sm:text-xl font-bold text-stone-900 mb-3">결제 내역 조회</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-sky-600">시작 날짜</label>
                    <input type="date" 
                           wire:model.live="startDate"
                           id="start_date"
                           class="mt-1 block w-full rounded-md border-transparent bg-sky-500 text-white placeholder-white/70 shadow-sm focus:border-sky-300 focus:ring-sky-300 sm:text-sm text-sm"
                           style="color-scheme: dark;">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-sky-600">종료 날짜</label>
                    <input type="date" 
                           wire:model.live="endDate"
                           id="end_date"
                           class="mt-1 block w-full rounded-md border-transparent bg-sky-500 text-white placeholder-white/70 shadow-sm focus:border-sky-300 focus:ring-sky-300 sm:text-sm text-sm"
                           style="color-scheme: dark;">
                </div>
            </div>
        </div>

        <!-- Payment Records -->
        @if(count($payments) > 0)
            <div class="space-y-3">
                @foreach($payments as $payment)
                    <div class="bg-white rounded-lg shadow-sm p-3 sm:p-4">
                        <div class="space-y-2">
                            <div class="flex items-start justify-between">
                                <h4 class="text-base font-semibold text-stone-900">{{ $payment->billing_name }}</h4>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                    @if($payment->payment_status === 'paid') bg-green-100 text-green-800
                                    @elseif($payment->payment_status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($payment->payment_status === 'cancelled') bg-red-100 text-red-800
                                    @elseif($payment->payment_status === 'failed') bg-gray-100 text-gray-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    @if($payment->payment_status === 'paid') 결제완료
                                    @elseif($payment->payment_status === 'pending') 대기중
                                    @elseif($payment->payment_status === 'cancelled') 취소됨
                                    @elseif($payment->payment_status === 'failed') 결제실패
                                    @else {{ $payment->payment_status }}
                                    @endif
                                </span>
                            </div>
                            
                            <div class="text-lg font-bold text-green-600">{{ number_format($payment->amount) }}원</div>
                            
                            <div class="space-y-1 text-sm text-stone-600">
                                <div>학생: {{ $payment->student->user->name ?? '정보없음' }}</div>
                                @if($payment->billing_memo)
                                    <div>메모: {{ $payment->billing_memo }}</div>
                                @endif
                                @if($payment->payment_method)
                                    <div>결제방법: {{ $payment->payment_method }}</div>
                                @endif
                                <div>결제일시: {{ $payment->created_at->format('Y-m-d H:i') }}</div>
                            </div>
                            
                            @if($payment->payment_status !== 'paid' && $payment->payment_status !== 'cancelled')
                                <div class="mt-3">
                                    <a href="{{ route('payment', ['paymentId' => $payment->id]) }}" target="_blank" class="inline-flex items-center justify-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                                        결제하기
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- No Records -->
            <div class="bg-white rounded-lg shadow-sm p-6 sm:p-8">
                <div class="text-center">
                    <svg class="mx-auto h-10 w-10 sm:h-12 sm:w-12 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-stone-900">결제 내역이 없습니다</h3>
                    <p class="mt-1 text-sm text-stone-500">선택한 기간 동안의 결제 내역이 없습니다.</p>
                </div>
            </div>
        @endif
    </main>
</div>
