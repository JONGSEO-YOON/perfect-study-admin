<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="{
        state: $wire.entangle('{{ $getStatePath() }}'),
        dayLabels: {
            'mon': '월',
            'tue': '화',
            'wed': '수',
            'thu': '목',
            'fri': '금',
            'sat': '토',
            'sun': '일'
        },
        init() {
            if (!this.state) {
                this.state = {
                    mon: { enabled: false, start: '09:00', end: '18:00' },
                    tue: { enabled: false, start: '09:00', end: '18:00' },
                    wed: { enabled: false, start: '09:00', end: '18:00' },
                    thu: { enabled: false, start: '09:00', end: '18:00' },
                    fri: { enabled: false, start: '09:00', end: '18:00' },
                    sat: { enabled: false, start: '09:00', end: '18:00' },
                    sun: { enabled: false, start: '09:00', end: '18:00' }
                }
            }
        }
    }" class="space-y-2">
        <template x-for="(day, code) in dayLabels" :key="code">
            <div class="flex items-center space-x-4">
                <!-- Checkbox -->
                <label class="flex items-center space-x-2">
                    <input type="checkbox" x-model="state[code].enabled"
                        class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                    <span class="text-sm font-medium text-gray-700" x-text="day"></span>
                </label>

                <!-- Start Time -->
                <div class="fi-input-wrp flex-1  rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 [&amp;:not(:has(.fi-ac-action:focus))]:focus-within:ring-2 ring-gray-950/10 dark:ring-white/20 [&amp;:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-600 dark:[&amp;:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-500 "
                    :class="{ 'opacity-50': !state[code].enabled }">
                    <input type="time" x-model="state[code].start" :disabled="!state[code].enabled"
                        class="fi-input block w-full rounded-lg border-none bg-white py-1.5 text-base text-gray-950 shadow-sm outline-none transition duration-75 focus:ring-2 focus:ring-primary-600 disabled:opacity-70 dark:bg-white/5 dark:text-white dark:focus:ring-primary-500 sm:text-sm">
                </div>

                <span class="text-gray-500">~</span>

                <!-- End Time -->
                {{-- <div class="flex-1 fi-input-wrp max-w-[120px]" > --}}
                <div class="fi-input-wrp flex-1  rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 [&amp;:not(:has(.fi-ac-action:focus))]:focus-within:ring-2 ring-gray-950/10 dark:ring-white/20 [&amp;:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-600 dark:[&amp;:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-500 "
                    :class="{ 'opacity-50': !state[code].enabled }">
                    <input type="time" x-model="state[code].end" :disabled="!state[code].enabled"
                        class="fi-input block w-full rounded-lg border-none bg-white py-1.5 text-base text-gray-950 shadow-sm outline-none transition duration-75 focus:ring-2 focus:ring-primary-600 disabled:opacity-70 dark:bg-white/5 dark:text-white dark:focus:ring-primary-500 sm:text-sm">
                </div>
            </div>
        </template>
    </div>
</x-dynamic-component>
