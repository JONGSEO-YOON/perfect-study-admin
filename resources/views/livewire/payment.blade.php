<div class="min-h-screen bg-white">
    @if($payment)
        <div class=" mx-auto">
            <div class="bg-white overflow-hidden">
                <!-- 로고 -->
                <div class="text-center pt-6 pb-4">
                    <img src="{{ isset($currentAcademy) && $currentAcademy->logo_path ? \Illuminate\Support\Facades\Storage::url($currentAcademy->logo_path) : asset('logo.png') }}" alt="{{ $currentAcademy->name ?? '학원' }}" class="max-w-[220px] max-h-12 w-auto object-contain mx-auto" style="height: auto; width: auto;">
                </div>
                
                <!-- 상품 정보 (빌링 네임) -->
                <div class="bg-white px-6 pb-6 text-center">
                    <h1 class="text-xl font-bold text-gray-900 mb-2">{{ $payment->billing_name }}</h1>
                    @if($payment->billing_memo)
                    <p class="text-gray-600 text-sm leading-relaxed">{{ $payment->billing_memo }}</p>
                    @endif
                </div>

                <div class="p-6 space-y-4">
                    <!-- 학생 정보 -->
                    @if($payment->student)
                    <div class="py-3 border-b border-gray-100">
                        <div class="mb-1">
                            <span class="text-gray-500 text-sm">학생</span>
                        </div>
                        <div>
                            <p class="text-lg font-semibold text-gray-900">{{ $payment->student->user->name }}</p>
                            @if($payment->student->grade)
                            <p class="text-gray-500 text-sm">{{ $payment->student->grade }}학년</p>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- 결제 방법 -->
                    @if($payment->payment_method)
                    <div class="py-3 border-b border-gray-100">
                        <div class="mb-1">
                            <span class="text-gray-500 text-sm">결제 방법</span>
                        </div>
                        <div>
                            <span class="text-gray-900 font-medium">{{ $payment->payment_method }}</span>
                        </div>
                    </div>
                    @endif

                    <!-- 총 결제 금액 -->
                    <div class="bg-violet-50 rounded-lg p-4 mt-6">
                        <div class="mb-1">
                            <span class="text-violet-700 font-semibold">총 결제 금액</span>
                        </div>
                        <div>
                            <span class="text-2xl font-bold text-violet-600">{{ number_format($payment->amount) }}원</span>
                        </div>
                    </div>
                </div>
 
                <div id="payment-method"></div>
                <!-- 이용약관 UI -->
                <div id="agreement"></div>
                <!-- 결제하기 버튼 -->
                <div class="p-6">
                    <button class="w-full bg-violet-600 hover:bg-violet-700 text-white font-bold py-4 px-6 rounded-xl transition-colors duration-200 text-lg shadow-lg" id="payment-button">결제하기</button>
                </div>
                    </div>
                </div>


    @else
        <div class="max-w-sm mx-auto text-center">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <p class="text-gray-500">결제 정보를 찾을 수 없습니다.</p>
            </div>
        </div>
    @endif

</div>

@assets
    <script src="https://js.tosspayments.com/v2/standard"></script>
@endassets

@script
<script>
        console.log($wire.amount)
      main();

      async function main() {
        const button = document.getElementById("payment-button");
        // const coupon = document.getElementById("coupon-box");
        // ------  결제위젯 초기화 ------
        const clientKey = $wire.TOSS_CLIENT_KEY;
        const tossPayments = TossPayments(clientKey);
        // 회원 결제
        const customerKey = $wire.TOSS_CUSTOMER_KEY;
        const widgets = tossPayments.widgets({
          customerKey,
        });
        // 비회원 결제
        // const widgets = tossPayments.widgets({ customerKey: TossPayments.ANONYMOUS });

        // ------ 주문의 결제 금액 설정 ------
        await widgets.setAmount({
          currency: "KRW",
          value: $wire.amount,
        });

        await Promise.all([
          // ------  결제 UI 렌더링 ------
          widgets.renderPaymentMethods({
            selector: "#payment-method",
            variantKey: "DEFAULT",
          }),
          // ------  이용약관 UI 렌더링 ------
          widgets.renderAgreement({ selector: "#agreement", variantKey: "AGREEMENT" }),
        ]);

        // ------  주문서의 결제 금액이 변경되었을 경우 결제 금액 업데이트 ------
        // coupon.addEventListener("change", async function () {
        // //   if (coupon.checked) {
        // //     await widgets.setAmount({
        // //       currency: "KRW",
        // //       value: 50000 - 5000,
        // //     });

        // //     return;
        // //   }

        //   await widgets.setAmount({
        //     currency: "KRW",
        //     value: 50000,
        //   });
        // });
  
        // ------ '결제하기' 버튼 누르면 결제창 띄우기 ------
        button.addEventListener("click", async function () {
          await widgets.requestPayment({
            orderId: $wire.orderId,
            orderName: $wire.billing_name,
            successUrl: window.location.origin + "/toss-payments/success",
            failUrl: window.location.origin + "/toss-payments/fail",
            customerEmail: "customer123@gmail.com",
            customerName: "김토스",
            customerMobilePhone: "01012341234",
          });
        });
      }
    </script>
@endscript
