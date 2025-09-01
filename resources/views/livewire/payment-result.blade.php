<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            @if($payment->payment_status === 'success' || $payment->payment_status === 'paid')
                <!-- 성공 아이콘 -->
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-violet-100">
                    <svg class="h-8 w-8 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                
                <!-- 성공 메시지 -->
                <h2 class="mt-6 text-2xl font-bold text-gray-900">
                    결제가 완료되었습니다!
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    결제 정보를 확인해주세요.
                </p>
            @else
                <!-- 실패 아이콘 -->
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100">
                    <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                
                <!-- 실패 메시지 -->
                <h2 class="mt-6 text-2xl font-bold text-gray-900">
                    결제에 실패했습니다
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    결제 정보를 확인해주세요.
                </p>
            @endif
        </div>

        <!-- 결제 정보 카드 -->
        <div class="bg-white space-y-4">
            <div class="border-b pb-4">
                <h3 class="text-lg font-medium text-gray-900">결제 정보</h3>
            </div>
            
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">결제명</span>
                    <span class="font-medium text-gray-900">{{ $payment->billing_name }}</span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-gray-600">결제 금액</span>
                    <span class="font-medium text-gray-900">{{ number_format($payment->amount) }}원</span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-gray-600">결제 상태</span>
                    @if($payment->payment_status === 'success' || $payment->payment_status === 'paid')
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-violet-100 text-violet-800">
                            완료
                        </span>
                    @else
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                            실패
                        </span>
                    @endif
                </div>
            </div>
        </div>


        {{-- <!-- 버튼 영역 -->
        <div class="space-y-3">
            <button 
                onclick="window.close()" 
                class="w-full bg-violet-600 text-white py-3 px-4 rounded-md hover:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 transition duration-200"
            >
                창 닫기
            </button>

        </div> --}}
    </div>
</div>