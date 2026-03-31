<div
    x-data="{
        items: @js($getItems()),
        selected: $wire.entangle('{{ $getStatePath() }}'),
        multiple: @js($isMultiple()),
        cols: @js($getCols()),
        maxHeight: @js($getMaxHeight()),
        toggle(val) {
            if (!this.multiple) {
                this.selected = (this.selected === val) ? null : val;
                return;
            }
            if (!Array.isArray(this.selected)) this.selected = [];
            let idx = this.selected.indexOf(val);
            if (idx > -1) {
                this.selected.splice(idx, 1);
            } else {
                this.selected.push(val);
            }
        },
        isSelected(val) {
            if (this.multiple) {
                return Array.isArray(this.selected) && this.selected.includes(val);
            }
            return this.selected === val;
        },
        isAllSelected() {
            if (this.multiple) return !Array.isArray(this.selected) || this.selected.length === 0;
            return this.selected === null || this.selected === '' || this.selected === undefined;
        },
        selectAll() {
            if (this.multiple) { this.selected = []; }
            else { this.selected = null; }
        }
    }"
    class="space-y-1"
>
    <div class="text-sm font-bold text-gray-900" style="margin-bottom: 4px;">
        {{ $getLabel() }}
        @if ($isMultiple())
            <span class="text-xs font-normal text-gray-400 ml-1">복수 선택 가능</span>
        @endif
    </div>
    <div
        class="border border-gray-200 rounded-lg overflow-y-auto"
        :style="maxHeight ? 'max-height: ' + maxHeight + 'px' : ''"
    >
        <div
            class="grid"
            :style="'grid-template-columns: repeat(' + cols + ', 1fr)'"
        >
            {{-- 전체 버튼 --}}
            <button
                type="button"
                @click="selectAll()"
                class="px-2 py-2.5 text-sm border border-gray-100 transition-all cursor-pointer text-center"
                :class="isAllSelected() ? 'bg-green-50 text-green-700 font-semibold' : 'bg-white text-gray-500 hover:bg-gray-50'"
            >전체</button>
            {{-- 항목 버튼 --}}
            <template x-for="item in items" :key="item.value">
                <button
                    type="button"
                    @click="toggle(item.value)"
                    class="px-2 py-2.5 text-sm border border-gray-100 transition-all cursor-pointer text-center"
                    :class="isSelected(item.value) ? 'bg-blue-50 text-blue-700 font-semibold' : 'bg-white text-gray-700 hover:bg-gray-50'"
                    x-text="item.label"
                ></button>
            </template>
        </div>
    </div>
</div>
