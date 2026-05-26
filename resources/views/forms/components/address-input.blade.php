<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="{
        state: {
            address: `{{ $getRecord()?->address ?? '' }}`,
            postal_code: `{{ $getRecord()?->postal_code ?? '' }}`,
        },

        init() {
            // 부모 창의 jusoCallBack 등록. 팝업 종료 시 한 번만 호출됨.
            // 기존에 $watch(state, ...) 로 매 변경 시 $wire.set 을 호출했더니
            // CSRF token 갱신 충돌로 419 (this page has expired) 가 발생하던 문제 해결을 위해
            // watch 를 제거하고 callback 안에서만 한 번만 동기화한다.
            window.jusoCallBack = (...args) => {
                this.jusoCallBack(...args);
            };
        },

        goPopup() {
            const width = 570;
            const height = 420;
            const left = (window.screen.width / 2) - (width / 2);
            const top = (window.screen.height / 2) - (height / 2);

            window.open('/juso-popup', 'pop',
                `width=${width},height=${height},left=${left},top=${top},scrollbars=yes,resizable=yes`);
        },

        jusoCallBack(roadFullAddr, roadAddrPart1, addrDetail, roadAddrPart2, engAddr, jibunAddr, zipNo,
            admCd, rnMgtSn, bdMgtSn, detBdNmList, bdNm, bdKdcd, siNm, sggNm, emdNm, liNm,
            rn, udrtYn, buldMnnm, buldSlno, mtYn, lnbrMnnm, lnbrSlno, emdNo) {
            this.state = {
                address: roadFullAddr || '',
                postal_code: zipNo || '',
            };
            // 한 번만 form state 에 반영. (afterStateUpdated 가 hidden address/postal_code 에 set)
            try {
                this.$wire.set('{{ $getStatePath() }}', this.state);
            } catch (e) {
                // 토큰 만료 등 livewire 호출 실패 시, hidden field 들을 DOM 직접 조작으로 채워
                // 사용자가 저장 버튼을 누를 때 form 제출이 정상 동작하도록 fallback.
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

            {{-- <input type="text" x-model="state.roadAddrPart2"
                class="block w-full border-gray-300 rounded-lg shadow-sm" placeholder="상세주소"> --}}

            {{-- <div class="grid grid-cols-3 gap-2">
                <input type="text" x-model="state.zipNo" readonly
                    class="block w-full border-gray-300 rounded-lg shadow-sm" placeholder="우편번호">
                <input type="text" x-model="state.siNm" readonly
                    class="block w-full border-gray-300 rounded-lg shadow-sm" placeholder="시도">
                <input type="text" x-model="state.sggNm" readonly
                    class="block w-full border-gray-300 rounded-lg shadow-sm" placeholder="시군구">
            </div> --}}
        </div>
    </div>
</x-dynamic-component>
