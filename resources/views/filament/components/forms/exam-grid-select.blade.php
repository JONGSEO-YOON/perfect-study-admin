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
        selected: $wire.entangle('{{ $statePath }}').live,
        multiple: @js($isMultiple()),
        statePath: @js($statePath),
        toggle(val) {
            if (!this.multiple) {
                this.selected = (this.selected === val) ? null : val;
                $wire.set(this.statePath, this.selected);
                return;
            }
            if (!Array.isArray(this.selected)) this.selected = [];
            // 비교 시 값 형변환을 통일 (int/string 혼동 방지)
            let idx = this.selected.findIndex(x => String(x) === String(val));
            if (idx > -1) {
                this.selected.splice(idx, 1);
            } else {
                this.selected.push(val);
            }
            $wire.set(this.statePath, this.selected);
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
            if (this.multiple) { this.selected = []; }
            else { this.selected = null; }
            $wire.set(this.statePath, this.selected);
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
