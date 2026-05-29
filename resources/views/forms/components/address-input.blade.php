<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    {{--
        주소 검색을 카카오(다음) 우편번호 서비스로 처리한다.
        기존 juso.go.kr 팝업은 외부 도메인이 우리 서버(/juso-popup)로 cross-site POST 를 보내는데,
        SameSite=Lax 세션 쿠키가 전송되지 않아 새 게스트 세션이 만들어지고 → 관리자 세션이 덮어써져
        "this page has expired (419)" + 로그아웃 이 발생했다.
        Daum Postcode 는 100% 클라이언트 사이드 콜백이라 우리 서버로 POST 가 없으므로 세션이 유지된다.
    --}}
    <div x-data="{
        state: {
            address: `{{ $getRecord()?->address ?? '' }}`,
            postal_code: `{{ $getRecord()?->postal_code ?? '' }}`,
        },

        ensureScript() {
            return new Promise((resolve, reject) => {
                if (window.daum && window.daum.Postcode) { resolve(); return; }
                let s = document.getElementById('daum-postcode-script');
                if (s) {
                    s.addEventListener('load', () => resolve());
                    s.addEventListener('error', () => reject());
                    return;
                }
                s = document.createElement('script');
                s.id = 'daum-postcode-script';
                s.src = 'https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js';
                s.onload = () => resolve();
                s.onerror = () => reject();
                document.head.appendChild(s);
            });
        },

        async goPopup() {
            try {
                await this.ensureScript();
            } catch (e) {
                alert('주소 검색 서비스를 불러오지 못했습니다. 인터넷 연결을 확인한 뒤 다시 시도해주세요.');
                return;
            }
            new window.daum.Postcode({
                oncomplete: (data) => {
                    const address = data.roadAddress || data.address || data.jibunAddress || '';
                    const postal_code = data.zonecode || '';
                    this.apply(address, postal_code);
                }
            }).open();
        },

        apply(address, postal_code) {
            this.state = { address, postal_code };
            // form state 에 반영 (AddressInput 의 afterStateUpdated 가 hidden address/postal_code 에 set)
            try {
                this.$wire.set('{{ $getStatePath() }}', this.state);
            } catch (e) {
                // livewire 호출 실패 시 hidden field 들을 DOM 직접 조작으로 채워 저장이 동작하도록 fallback
                const addrInput = document.querySelector('input[wire\\:model=\"data.address\"], input[name=\"address\"]');
                const zipInput = document.querySelector('input[wire\\:model=\"data.postal_code\"], input[name=\"postal_code\"]');
                if (addrInput) addrInput.value = this.state.address;
                if (zipInput) zipInput.value = this.state.postal_code;
            }
        }
    }">
        <div class="space-y-2">
            <div class="flex space-x-2">
                <div
                    class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 fi-disabled bg-gray-50 dark:bg-transparent ring-gray-950/10 dark:ring-white/10 fi-fo-text-input overflow-hidden">
                    <div class="min-w-0 flex-1">
                        <input x-model="state.postal_code"
                            class="fi-input block w-full border-none py-1.5 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] dark:disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.500)] sm:text-sm sm:leading-6 bg-white/0 ps-3 pe-3"
                            disabled="disabled" placeholder="우편번호" type="text">
                    </div>
                </div>
                <x-filament::button type="button" x-on:click="goPopup">
                    주소 검색
                </x-filament::button>
            </div>
            <div class="flex space-x-2">
                <div
                    class="fi-input-wrp grow flex rounded-lg shadow-sm ring-1 transition duration-75 fi-disabled bg-gray-50 dark:bg-transparent ring-gray-950/10 dark:ring-white/10 fi-fo-text-input overflow-hidden">
                    <div class="min-w-0 flex-1">
                        <input x-model="state.address"
                            class="fi-input block w-full border-none py-1.5 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] dark:disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.500)] sm:text-sm sm:leading-6 bg-white/0 ps-3 pe-3"
                            disabled="disabled" placeholder="주소" type="text">
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dynamic-component>
