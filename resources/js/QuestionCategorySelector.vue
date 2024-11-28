<script setup>
import { ref, computed, watch } from "vue";
import { defineProps } from "vue";
import QuestionCategoryItem from "./QuestionCategoryItem.vue";

const props = defineProps({
    wire: {},
    mingleData: {},
});

const selectedDepth0 = ref("중"); // 초기 선택값을 '고'로 설정
const selectedDepth1 = ref("1-1");
const selectedItems = ref([]); // 선택된 항목들을 저장할 배열
if (props.mingleData.selectedId) {
    selectedItems.value.push({
        id: props.mingleData.selectedId,
        // name: props.mingleData.selectedName,
    });
}

const selectedCategories = computed(() => {
    const selected = props.mingleData.questionCategories.find(
        (category) => category.name === selectedDepth0.value
    );
    return selected ? selected.children : [];
});

const selectedChildren = computed(() => {
    if (!selectedDepth1.value) return [];
    return selectedCategories.value.filter(
        (category) => category.name === selectedDepth1.value
    );
});

const selectCategory = (categoryName) => {
    selectedDepth0.value = categoryName;
    selectedDepth1.value = null;
};

const selectDepth1 = (categoryName) => {
    selectedDepth1.value = categoryName;
};
const handleItemSelect = (item) => {
    if (selectedItems.value.some((selected) => selected.id === item.id)) {
        selectedItems.value = selectedItems.value.filter(
            (selected) => selected.id !== item.id
        );
        return;
    }
    selectedItems.value = [];
    if (item) selectedItems.value.push(item);
};

watch(selectedItems, (newVal) => {
    const event = new Event("selectedQuestionCategoryChanged");
    event.data = JSON.parse(JSON.stringify(newVal));
    window.dispatchEvent(event);
});
</script>

<template>
    <div class="flex flex-col border rounded text-gray-700 font-medium">
        <div class="flex flex-row border-b">
            <div
                class="overflow-auto flex-nowrap border-r flex flex-row gap-x-2 p-3"
            >
                <button
                    v-for="category in mingleData.questionCategories"
                    :key="category.id"
                    :class="[
                        'py-2 px-3 border rounded',
                        selectedDepth0 === category.name
                            ? 'bg-primary-100/50 text-primary-400 border-primary-400 font-bold'
                            : '',
                    ]"
                    type="button"
                    @click="selectCategory(category.name)"
                >
                    {{ category.name }}
                </button>
            </div>
            <div
                class="flex-1 overflow-auto flex-nowrap p-3 flex flex-row gap-x-1.5 text-sm"
            >
                <button
                    v-for="child in selectedCategories"
                    :key="child.id"
                    :class="[
                        'min-w-max py-2 px-4 border rounded-full',
                        selectedDepth1 === child.name
                            ? 'bg-primary-100/50 text-primary-400 border-primary-400'
                            : '',
                    ]"
                    type="button"
                    @click="selectDepth1(child.name)"
                >
                    {{ child.name }}
                </button>
            </div>
        </div>
        <div class="h-[300px] overflow-y-auto flex flex-col">
            <QuestionCategoryItem
                v-for="child in selectedChildren"
                initialExpanded
                innerClass="border-b bg-gray-100 font-semibold py-2"
                :key="child.id"
                :item="child"
                :selectedItems="selectedItems"
                @select="handleItemSelect"
            />
        </div>
    </div>
</template>
