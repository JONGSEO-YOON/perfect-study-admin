<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    {{--
        주소 검색은 카카오(다음) 우편번호 서비스(클라이언트 사이드 콜백)로 처리한다.
        - 우편번호 / 도로명주소: 검색으로 자동 입력 (읽기 전용)
        - 상세주소(동/호수/층 등): 사용자가 직접 입력 (address_detail 컬럼에 저장)
        주의: x-data 속성 안에는 escape 된 큰따옴표(\")를 절대 넣지 말 것.
              (HTML 속성이 조기 종료되어 Alpine 표현식 전체가 깨지고 버튼이 먹통이 됨.)
    --}}
    <div x-data="{
        state: {
            address: `{{ $getRecord()?->address ?? '' }}`,
            postal_code: `{{ $getRecord()?->postal_code ?? '' }}`,
            detail: `{{ $getRecord()?->address_detail ?? '' }}`,
        },

        init() {
            // 팝업 차단 방지를 위해 스크립트를 페이지 진입 시 미리 로드한다.
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
                    // 검색 시 도로명주소/우편번호만 갱신, 상세주소는 유지
                    this.state = { address, postal_code, detail: this.state.detail };
                    this.sync();
                    // 검색 직후 상세주소로 포커스 이동
                    this.$nextTick(() => { if (this.$refs.detail) this.$refs.detail.focus(); });
                }
            }).open();
        },

        sync() {
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
                            disabled="disabled" placeholder="도로명/지번 주소 (검색)" type="text">
                    </div>
                </div>
            </div>
            {{-- 상세주소: 직접 입력 (동/호수/층 등) --}}
            <div class="flex space-x-2">
                <div
                    class="fi-input-wrp grow flex rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 ring-gray-950/10 dark:ring-white/20 focus-within:ring-2 focus-within:ring-primary-600 fi-fo-text-input overflow-hidden">
                    <div class="min-w-0 flex-1">
                        <input x-model="state.detail" x-ref="detail" @change="sync()" @blur="sync()"
                            class="fi-input block w-full border-none py-1.5 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-0 dark:text-white dark:placeholder:text-gray-500 sm:text-sm sm:leading-6 bg-white/0 ps-3 pe-3"
                            placeholder="상세주소 (예: 101동 1203호, 3층 등 직접 입력)" type="text">
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dynamic-component>
