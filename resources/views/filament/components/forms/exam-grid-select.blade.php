@php
    $items = $getItems();
    $statePath = $getStatePath();
    // form state 가 변하면 items 도 다시 평가되므로, items 시그니처를 wire:key 에 넣어
    // Livewire 가 컴포넌트를 정확히 re-mount 하도록 함 (Alpine x-data 캐시 문제 해결)
    $itemsKey = md5(json_encode($items));
@endphp
<div
    wire:key="exam-grid-{{ $statePath }}-{{ $itemsKey }}"
    x-data="{
        selected: $wire.entangle('{{ $statePath }}'),
        multiple: @js($isMultiple()),
        statePath: @js($statePath),
        toggle(val) {
            if (!this.multiple) {
                const next = (String(this.selected) === String(val)) ? null : val;
                this.selected = next;
                this.$wire.set(this.statePath, next);
                return;
            }
            // 불변(immutable) 배열로 새로 만들어서 set 한다.
            // 기존엔 entangle(...).live + push/splice + $wire.set 이 중복 발사되어
            // 여러 개를 빠르게 선택하면 일부 선택이 누락(한 개만 남는)되는 race 가 있었다.
            let arr = Array.isArray(this.selected) ? this.selected.map(x => x) : [];
            let idx = arr.findIndex(x => String(x) === String(val));
            if (idx > -1) {
                arr.splice(idx, 1);
            } else {
                arr.push(val);
            }
            this.selected = arr;
            this.$wire.set(this.statePath, arr);
        },
        isSelected(val) {
            if (this.multiple) {
                return Array.isArray(this.selected)
                    && this.selected.some(x => String(x) === String(val));
            }
            return String(this.selected) === String(val);
        },
        isAllSelected() {
            if (this.multiple) return !Array.isArray(this.selected) || this.selected.length === 0;
            return this.selected === null || this.selected === '' || this.selected === undefined;
        },
        selectAll() {
            if (this.multiple) { this.selected = []; this.$wire.set(this.statePath, []); }
            else { this.selected = null; this.$wire.set(this.statePath, null); }
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
        @if ($getMaxHeight()) style="max-height: {{ $getMaxHeight() }}px" @endif
    >
        <div
            class="grid"
            style="grid-template-columns: repeat({{ $getCols() }}, 1fr)"
        >
            {{-- 전체 버튼 --}}
            <button
                type="button"
                @click="selectAll()"
                class="px-2 py-2.5 text-sm border border-gray-100 transition-all cursor-pointer text-center"
                :class="isAllSelected() ? 'bg-green-50 text-green-700 font-semibold' : 'bg-white text-gray-500 hover:bg-gray-50'"
            >전체</button>
            {{-- 항목 버튼: 서버에서 직접 렌더링 (Alpine x-for 가 items 캐시되는 문제 회피) --}}
            @foreach ($items as $item)
                @php
                    $val = $item['value'];
                    $jsVal = is_string($val)
                        ? "'" . addslashes($val) . "'"
                        : (int) $val;
                @endphp
                <button
                    type="button"
                    @click="toggle({{ $jsVal }})"
                    class="px-2 py-2.5 text-sm border border-gray-100 transition-all cursor-pointer text-center"
                    :class="isSelected({{ $jsVal }}) ? 'bg-blue-50 text-blue-700 font-semibold' : 'bg-white text-gray-700 hover:bg-gray-50'"
                >{{ $item['label'] }}</button>
            @endforeach
        </div>
    </div>
</div>
