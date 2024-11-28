<script setup>
import { ref, computed } from "vue";
const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
    initialExpanded: {
        type: Boolean,
        default: false,
    },
    innerClass: {
        type: String,
        default: "",
    },
    selectedItems: {
        type: Array,
        default: () => [],
    },
    multiple: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["select"]);
const isExpanded = ref(props.initialExpanded);

const toggle = () => {
    if (props.item.children?.length) {
        isExpanded.value = !isExpanded.value;
    }
};

// 하위 아이템들 중 question_type인 것들만 가져오기
const getAllQuestionTypeItems = (item) => {
    let items = [];
    if (item.type === "question_type") {
        items.push(item);
    }
    if (item.children) {
        item.children.forEach((child) => {
            items = [...items, ...getAllQuestionTypeItems(child)];
        });
    }
    return items;
};

// scope의 모든 하위 아이템이 선택되었는지 확인
const allChildrenSelected = computed(() => {
    if (props.item.type !== "scope") return false;
    const questionTypeItems = getAllQuestionTypeItems(props.item);
    return (
        questionTypeItems.length > 0 &&
        questionTypeItems.every((item) =>
            props.selectedItems.some((selected) => selected.id === item.id)
        )
    );
});

// scope의 일부 하위 아이템이 선택되었는지 확인
const someChildrenSelected = computed(() => {
    if (props.item.type !== "scope") return false;
    const questionTypeItems = getAllQuestionTypeItems(props.item);
    return questionTypeItems.some((item) =>
        props.selectedItems.some((selected) => selected.id === item.id)
    );
});

const isSelected = computed(() => {
    if (props.item.type === "scope") {
        return allChildrenSelected.value;
    }
    return props.selectedItems.some((item) => item.id === props.item.id);
});

const handleSelect = (event) => {
    event.stopPropagation();
    if (props.item.type === "scope" && props.multiple) {
        const questionTypeItems = getAllQuestionTypeItems(props.item);
        if (allChildrenSelected.value) {
            // 모든 하위 아이템 선택 해제
            questionTypeItems.forEach((item) => {
                emit("select", item);
            });
        } else {
            // 모든 하위 아이템 선택
            questionTypeItems.forEach((item) => {
                if (
                    !props.selectedItems.some(
                        (selected) => selected.id === item.id
                    )
                ) {
                    emit("select", item);
                }
            });
        }
    } else {
        emit("select", props.item);
    }
};
</script>

<template>
    <div class="w-full">
        <div
            :class="innerClass"
            class="flex items-center gap-x-2 px-4 py-2 hover:bg-gray-50 cursor-pointer"
            @click="toggle"
        >
            <span v-if="item.children?.length" class="w-4 text-gray-500 mr-1">
                <svg
                    :class="{
                        'rotate-90': isExpanded,
                    }"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                    class="size-5 transition-all"
                >
                    <path
                        fill-rule="evenodd"
                        d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z"
                        clip-rule="evenodd"
                    />
                </svg>
            </span>
            <span v-else class="w-4"></span>
            <div
                v-if="multiple || item.type !== 'scope'"
                class="flex items-center"
                @click="handleSelect"
            >
                <input
                    type="checkbox"
                    :checked="isSelected"
                    :indeterminate="
                        item.type === 'scope' &&
                        !allChildrenSelected &&
                        someChildrenSelected
                    "
                    class="h-4 w-4 text-primary-600 rounded border-gray-300"
                />
            </div>
            <span
                @click="
                    (e) =>
                        multiple || item.type !== 'scope' ? handleSelect(e) : ''
                "
                class="flex-1"
                >{{ item.name }}</span
            >
        </div>
        <div v-if="isExpanded" class="pl-6">
            <QuestionCategoryItem
                v-for="child in item.children"
                :key="child.id"
                :item="child"
                :selectedItems="selectedItems"
                :multiple="multiple"
                @select="$emit('select', $event)"
            />
        </div>
    </div>
</template>
