<script setup>
import { ref, computed, watch } from "vue";
import { defineProps } from "vue";
import QuestionCategoryItem from "./QuestionCategoryItem.vue";

const props = defineProps({
    wire: {},
    mingleData: {},
});

const selectedDepth0 = ref(); // 초기 선택값을 '고'로 설정
const selectedDepth1 = ref([]);
const selectedItems = ref([]); // 선택된 항목들을 저장할 배열

const multiple = ref(props.mingleData.multiple);
const maxDepth = ref(props.mingleData.maxDepth ?? Infinity);

// 재귀적으로 아이템을 찾는 함수
const findCategoryPath = (categories, targetIds, currentPath = []) => {
    for (const category of categories) {
        // 현재 카테고리를 경로에 추가
        const newPath = [...currentPath, category.name];

        // items가 있는 경우 체크
        if (category.children?.some((item) => targetIds.includes(item.id))) {
            return newPath;
        }

        // children이 있는 경우 재귀적으로 검색
        if (category.children?.length) {
            const foundPath = findCategoryPath(
                category.children,
                targetIds,
                newPath
            );
            if (foundPath) {
                return foundPath;
            }
        }
    }
    return null;
};

// 초기 선택된 아이템 설정 및 카테고리 자동 선택
if (props.mingleData.selectedId) {
    const selectedIds = Array.isArray(props.mingleData.selectedId)
        ? props.mingleData.selectedId
        : [props.mingleData.selectedId];

    // selectedItems 설정
    selectedIds.forEach((id) => {
        selectedItems.value.push({ id });
    });

    // 재귀적으로 카테고리 경로 찾기
    const categoryPath = findCategoryPath(
        props.mingleData.questionCategories,
        selectedIds
    );

    if (categoryPath) {
        // 첫 번째 depth 설정
        selectedDepth0.value = categoryPath[0];

        // 두 번째 이후의 depth들을 selectedDepth1에 추가
        if (categoryPath.length > 1) {
            selectedDepth1.value = categoryPath.slice(1);
        }
    }
}

// if (props.mingleData.selectedId) {
//     if (Array.isArray(props.mingleData.selectedId)) {
//         props.mingleData.selectedId.forEach((id) => {
//             selectedItems.value.push({
//                 id,
//             });
//         });
//     } else {
//         selectedItems.value.push({
//             id: props.mingleData.selectedId,
//         });
//     }
// }

const selectedCategories = computed(() => {
    const selected = props.mingleData.questionCategories.find(
        (category) => category.name === selectedDepth0.value
    );
    return selected ? selected.children : [];
});

const selectedChildren = computed(() => {
    if (!selectedDepth1.value.length) return [];
    // getall 1depth categories of all not only from selected.
    const allCategories = props.mingleData.questionCategories.reduce(
        (acc, category) => {
            acc.push(...category.children);
            return acc;
        },
        []
    );

    return allCategories.filter((category) =>
        selectedDepth1.value.includes(category.name)
    );
});

const selectCategory = (categoryName) => {
    selectedDepth0.value = categoryName;
    // selectedDepth1.value = null;
};

const selectDepth1 = (categoryName) => {
    const index = selectedDepth1.value.indexOf(categoryName);
    if (index === -1) {
        selectedDepth1.value.push(categoryName);
    } else {
        selectedDepth1.value.splice(index, 1);
    }
};

const handleItemSelect = (item) => {
    if (selectedItems.value.some((selected) => selected.id === item.id)) {
        selectedItems.value = selectedItems.value.filter(
            (selected) => selected.id !== item.id
        );
        return;
    }
    if (!multiple.value) {
        selectedItems.value = [];
    }
    if (item) selectedItems.value.push(item);
};
//should be deep
watch(
    selectedItems,
    (newVal) => {
        const event = new Event("selectedQuestionCategoryChanged");
        event.data = JSON.parse(JSON.stringify(newVal));
        window.dispatchEvent(event);
    },
    { deep: true }
);
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
                        selectedDepth1.includes(child.name)
                            ? 'bg-primary-100/50 text-primary-400 border-primary-400'
                            : '',
                    ]"
                    type="button"
                    @click="selectDepth1(child.name)"
                >
                    <span v-html="child.name"></span>
                </button>
            </div>
        </div>
        <div class="h-[400px] overflow-y-auto flex flex-col">
            <QuestionCategoryItem
                v-for="child in selectedChildren"
                initialExpanded
                innerClass="border-b bg-gray-100 font-semibold py-2"
                :key="child.id"
                :item="child"
                :selectedItems="selectedItems"
                :multiple="multiple"
                :currentDepth="1"
                :maxDepth="maxDepth"
                @select="handleItemSelect"
            />
        </div>
    </div>
</template>
