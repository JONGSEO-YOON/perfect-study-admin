<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="{
        state: $wire.$entangle('{{ $getStatePath() }}'),
        formatPhone() {
            if (this.state[1] && this.state[1].length > 4) {
                this.state[1] = this.state[1].substring(0, 4);
            }
            if (this.state[2] && this.state[2].length > 4) {
                this.state[2] = this.state[2].substring(0, 4);
            }
        }
    }" class="flex flex-row gap-x-3">
        <!-- Area Code Select -->
        <div
            class="flex-1 fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-2 ring-gray-950/10 dark:ring-white/20 [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-600 dark:[&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-500 fi-fo-select">
            <div class="min-w-0 flex-1">
                <select x-model="state[0]"
                    class="flex-1 fi-select-input block w-full border-none bg-transparent py-1.5 pe-8 text-base text-gray-950 transition duration-75 focus:ring-0 disabled:text-gray-500 dark:text-white sm:text-sm sm:leading-6"
                    required>
                    <option value="010" selected>010</option>
                    <option value="011">011</option>
                    <option value="016">016</option>
                    <option value="019">019</option>
                    <option value="02">02</option>
                </select>
            </div>
        </div>
        <!-- Middle Number -->
        <div
            class="flex-1 fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-2 ring-gray-950/10 dark:ring-white/20 [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-600 dark:[&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-500 fi-fo-text-input">
            <div class="min-w-0 flex-1">
                <input x-model="state[1]" x-on:input="formatPhone"
                    class="fi-input block w-full border-none py-1.5 text-base text-gray-950 transition duration-75 focus:ring-0 disabled:text-gray-500 dark:text-white sm:text-sm sm:leading-6 bg-white/0 ps-3 pe-3"
                    maxlength="4" required type="text" pattern="\d*" inputmode="numeric" placeholder="1234">
            </div>
        </div>
        <!-- Last Number -->
        <div
            class="flex-1 fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-2 ring-gray-950/10 dark:ring-white/20 [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-600 dark:[&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-500 fi-fo-text-input">
            <div class="min-w-0 flex-1">
                <input x-model="state[2]" x-on:input="formatPhone"
                    class="fi-input block w-full border-none py-1.5 text-base text-gray-950 transition duration-75 focus:ring-0 disabled:text-gray-500 dark:text-white sm:text-sm sm:leading-6 bg-white/0 ps-3 pe-3"
                    maxlength="4" required type="text" pattern="\d*" inputmode="numeric" placeholder="1234">
            </div>
        </div>


    </div>
</x-dynamic-component>
