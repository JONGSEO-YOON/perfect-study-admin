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

        init() {
            // 팝업 차단(popup blocker) 방지를 위해 스크립트를 페이지 진입 시 미리 로드한다.
            // (클릭 핸들러 안에서 await 후 open() 하면 사용자 제스처 컨텍스트를 벗어나 팝업이 차단됨)
            this.loadScript();
        },

        loadScript() {
            if (window.daum && window.daum.Postcode) return;
            if (document.getElementById('daum-postcode-script')) return;
            const s = document.createElement('script');
            s.id = 'daum-postcode-script';
            s.src = 'https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js';
            document.head.appendChild(s);
        },

        goPopup() {
            // 동기적으로 open() 을 호출해야 팝업 차단을 피할 수 있다.
            if (!(window.daum && window.daum.Postcode)) {
                this.loadScript();
                alert('주소 검색을 준비 중입니다. 잠시 후 다시 눌러주세요.');
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
            // Daum 은 세션을 건드리지 않으므로 $wire.set 이 안전하게 동작한다.
            // 주의: x-data 속성 안에는 escape 된 큰따옴표를 절대 넣지 말 것
            //       (HTML 속성이 조기 종료되어 Alpine 표현식 전체가 깨지고 버튼이 먹통이 됨).
            this.$wire.set('{{ $getStatePath() }}', this.state);
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
