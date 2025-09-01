<div class="p-4" x-data="{ showSuccess: false }">
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">결제 링크</label>
        <div class="flex">
            <input 
                type="text" 
                x-ref="paymentInput"
                value="{{ $paymentUrl }}" 
                readonly 
                class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-l-md bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
            >
            <button 
                type="button" 
                @click="
                    $refs.paymentInput.select();
                    $refs.paymentInput.setSelectionRange(0, 99999);
                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText($refs.paymentInput.value).then(() => {
                            showSuccess = true;
                            setTimeout(() => showSuccess = false, 3000);
                        }).catch(() => {
                            document.execCommand('copy');
                            showSuccess = true;
                            setTimeout(() => showSuccess = false, 3000);
                        });
                    } else {
                        document.execCommand('copy');
                        showSuccess = true;
                        setTimeout(() => showSuccess = false, 3000);
                    }
                "
                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-r-md transition-colors duration-200"
            >
                복사
            </button>
        </div>
    </div>
    
    <div x-show="showSuccess" x-transition class="mb-4">
        <div class="p-3 bg-green-100 dark:bg-green-900 border border-green-300 dark:border-green-700 rounded-md">
            <p class="text-sm text-green-800 dark:text-green-200">✅ 결제 링크가 클립보드에 복사되었습니다!</p>
        </div>
    </div>
</div>