<div class="bg-gradient-to-br from-violet-400 to-fuchsia-600">
    <div class="mx-auto max-w-7xl px-4">
        <x-slot:title>
            출석체크 -
            {{ config("app.name", "Laravel") }}
        </x-slot>

        <div
            class="flex h-dvh w-full flex-col items-center justify-center tracking-tight"
        >
            <div
                class="grid w-full max-w-sm grid-cols-3 gap-5 rounded-3xl border border-stone-200 bg-white p-6 shadow-md"
            >
                <div class="col-span-3">
                    <h1
                        class="mb-2 flex items-center gap-1 text-xl font-bold text-violet-600"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="size-6"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m4.5 12.75 6 6 9-13.5"
                            />
                        </svg>
                        출석체크
                    </h1>
                    <div
                        class="flex items-center gap-1 text-lg font-semibold text-stone-400"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="size-6"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"
                            />
                        </svg>
                        {{ now()->format("Y년 n월 j일") }}
                    </div>
                </div>

                <input
                    id="phone"
                    type="text"
                    maxlength="13"
                    placeholder="010-xxxx-xxxx"
                    class="col-span-3 h-14 w-full max-w-sm !rounded-2xl border-2 !border-violet-200 text-center text-2xl font-bold text-violet-600 transition-all focus:!border-violet-400 focus:ring-2 focus:!ring-violet-300"
                    x-model="$wire.phone"
                    readonly
                />
                <div
                    class="col-span-3 mb-6 text-center text-base font-medium"
                    :class="{
                    'text-green-600': $wire.messageType === 'success-in',
                    'text-orange-600': $wire.messageType === 'success-out',
                    'text-red-600': $wire.messageType === 'error',
                    'text-violet-600': $wire.messageType === 'info'
                }"
                >
                    <span
                        x-text="$wire.message || '휴대전화번호 뒤 8자리를 입력하세요'"
                    ></span>
                </div>
                <button
                    type="button"
                    class="rounded-xl bg-stone-50 py-3 text-2xl font-bold text-violet-500 shadow transition-all hover:bg-stone-100 active:bg-stone-200"
                    @click="$js.addNumber(1)"
                >
                    1
                </button>
                <button
                    type="button"
                    class="rounded-xl bg-stone-50 py-3 text-2xl font-bold text-violet-500 shadow transition-all hover:bg-stone-100 active:bg-stone-200"
                    @click="$js.addNumber(2)"
                >
                    2
                </button>
                <button
                    type="button"
                    class="rounded-xl bg-stone-50 py-3 text-2xl font-bold text-violet-500 shadow transition-all hover:bg-stone-100 active:bg-stone-200"
                    @click="$js.addNumber(3)"
                >
                    3
                </button>
                <button
                    type="button"
                    class="rounded-xl bg-stone-50 py-3 text-2xl font-bold text-violet-500 shadow transition-all hover:bg-stone-100 active:bg-stone-200"
                    @click="$js.addNumber(4)"
                >
                    4
                </button>
                <button
                    type="button"
                    class="rounded-xl bg-stone-50 py-3 text-2xl font-bold text-violet-500 shadow transition-all hover:bg-stone-100 active:bg-stone-200"
                    @click="$js.addNumber(5)"
                >
                    5
                </button>
                <button
                    type="button"
                    class="rounded-xl bg-stone-50 py-3 text-2xl font-bold text-violet-500 shadow transition-all hover:bg-stone-100 active:bg-stone-200"
                    @click="$js.addNumber(6)"
                >
                    6
                </button>
                <button
                    type="button"
                    class="rounded-xl bg-stone-50 py-3 text-2xl font-bold text-violet-500 shadow transition-all hover:bg-stone-100 active:bg-stone-200"
                    @click="$js.addNumber(7)"
                >
                    7
                </button>
                <button
                    type="button"
                    class="rounded-xl bg-stone-50 py-3 text-2xl font-bold text-violet-500 shadow transition-all hover:bg-stone-100 active:bg-stone-200"
                    @click="$js.addNumber(8)"
                >
                    8
                </button>
                <button
                    type="button"
                    class="rounded-xl bg-stone-50 py-3 text-2xl font-bold text-violet-500 shadow transition-all hover:bg-stone-100 active:bg-stone-200"
                    @click="$js.addNumber(9)"
                >
                    9
                </button>
                <button
                    type="button"
                    class="rounded-xl bg-stone-50 py-3 text-base font-bold text-violet-400 shadow transition-all hover:bg-stone-100 active:bg-stone-200"
                    @click="$js.clearAll()"
                >
                    전체삭제
                </button>
                <button
                    type="button"
                    class="rounded-xl bg-stone-50 py-3 text-2xl font-bold text-violet-500 shadow transition-all hover:bg-stone-100 active:bg-stone-200"
                    @click="$js.addNumber(0)"
                >
                    0
                </button>
                <button
                    type="button"
                    class="rounded-xl bg-stone-50 py-3 text-2xl font-bold text-violet-400 shadow transition-all hover:bg-stone-100 active:bg-stone-200"
                    @click="$js.backspace()"
                >
                    ←
                </button>
                <div class="col-span-3 flex gap-2">
                      <button
                    type="button"
                    class="col-span-1 w-full rounded-2xl bg-gradient-to-r from-green-500 to-emerald-500 py-4 text-lg font-bold text-white shadow-md transition-all hover:from-green-600 hover:to-emerald-600 active:from-green-700 active:to-emerald-700"
                    wire:click="checkAttendance('in')"
                >
                    등원
                </button>
                <div class="col-span-1"></div>
                <button
                    type="button"
                    class="col-span-1 w-full rounded-2xl bg-gradient-to-r from-orange-500 to-red-500 py-4 text-lg font-bold text-white shadow-md transition-all hover:from-orange-600 hover:to-red-600 active:from-orange-700 active:to-red-700"
                    wire:click="checkAttendance('out')"
                >
                    하원
                </button>
                </div>
              
            </div>
        </div>

        @script
            <script>
                $js('addNumber', (number) => {
                    const currentNumbers = $wire.phone.replace(/[^0-9]/g, '');
                    if (currentNumbers.length < 11) {
                        const newNumbers = currentNumbers + number;
                        formatPhone(newNumbers);
                    }
                });

                $js('clearAll', () => {
                    $wire.phone = '010-';
                    clearMessage();
                });

                $js('backspace', () => {
                    const currentNumbers = $wire.phone.replace(/[^0-9]/g, '');
                    if (currentNumbers.length > 3) {
                        const newNumbers = currentNumbers.slice(0, -1);
                        formatPhone(newNumbers);
                    } else {
                        $wire.phone = '010-';
                    }
                });

                function formatPhone(numbers) {
                    if (numbers.length >= 3) {
                        $wire.phone = numbers.substring(0, 3) + '-';
                        if (numbers.length >= 7) {
                            $wire.phone +=
                                numbers.substring(3, 7) +
                                '-' +
                                numbers.substring(7);
                        } else {
                            $wire.phone += numbers.substring(3);
                        }
                    } else {
                        $wire.phone = numbers;
                    }
                }

                function clearMessage() {
                    $wire.message = '';
                    $wire.messageType = 'info';
                }

                // 3초 후 메시지 초기화 이벤트 리스너
                $wire.on('clearMessageAfterDelay', () => {
                    setTimeout(() => {
                        clearMessage();
                    }, 3000); // 3초 후 초기화
                });
            </script>
        @endscript
    </div>
</div>
