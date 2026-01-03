<!-- FolderTreeItem.vue -->
<script setup>
import { ref, computed } from "vue";

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
    depth: {
        type: Number,
        default: 0,
    },
    selectedItem: {
        type: Object,
        default: null,
    },
    onSelect: {
        type: Function,
        required: true,
    },
    histories: {
        type: Array,
        default: null,
    },
});

const isExpanded = ref(true);

const toggleExpand = () => {
    if (
        props.item.type === "folder" &&
        props.selectedItem?.id === props.item.id
    ) {
        isExpanded.value = !isExpanded.value;
    }
};

const isWatched = computed(() => {
    return props.histories?.some(
        (history) => history.video_id === props.item.id
    );
});
</script>

<template>
    <div class="folder-tree-item">
        <div
            :class="[
                'flex items-center flex-row px-4  py-2 hover:bg-gray-100 cursor-pointer transition-all text-gray-700 group',
                selectedItem?.id === item.id
                    ? '!bg-gray-200 border-l-4 border-primary-400'
                    : '',
                isWatched ? 'bg-[#8570C2]/30 border-[#8570C2] border-l-4 ' : '',
            ]"
            :style="{ paddingLeft: `${depth * 16 + 16}px` }"
            @click="
                () => {
                    toggleExpand();
                    onSelect(item);
                }
            "
        >
            <div class="text-gray-500">
                <svg
                    v-if="item.type === 'folder' && !isExpanded"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                    class="size-4"
                >
                    <path
                        d="M3.75 3A1.75 1.75 0 0 0 2 4.75v3.26a3.235 3.235 0 0 1 1.75-.51h12.5c.644 0 1.245.188 1.75.51V6.75A1.75 1.75 0 0 0 16.25 5h-4.836a.25.25 0 0 1-.177-.073L9.823 3.513A1.75 1.75 0 0 0 8.586 3H3.75ZM3.75 9A1.75 1.75 0 0 0 2 10.75v4.5c0 .966.784 1.75 1.75 1.75h12.5A1.75 1.75 0 0 0 18 15.25v-4.5A1.75 1.75 0 0 0 16.25 9H3.75Z"
                    />
                </svg>
                <svg
                    v-if="item.type === 'folder' && isExpanded"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                    class="size-4"
                >
                    <path
                        d="M4.75 3A1.75 1.75 0 0 0 3 4.75v2.752l.104-.002h13.792c.035 0 .07 0 .104.002V6.75A1.75 1.75 0 0 0 15.25 5h-3.836a.25.25 0 0 1-.177-.073L9.823 3.513A1.75 1.75 0 0 0 8.586 3H4.75ZM3.104 9a1.75 1.75 0 0 0-1.673 2.265l1.385 4.5A1.75 1.75 0 0 0 4.488 17h11.023a1.75 1.75 0 0 0 1.673-1.235l1.384-4.5A1.75 1.75 0 0 0 16.896 9H3.104Z"
                    />
                </svg>
                <svg
                    v-if="item.type === 'video'"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                    class="size-4"
                >
                    <path
                        d="M3.25 4A2.25 2.25 0 0 0 1 6.25v7.5A2.25 2.25 0 0 0 3.25 16h7.5A2.25 2.25 0 0 0 13 13.75v-7.5A2.25 2.25 0 0 0 10.75 4h-7.5ZM19 4.75a.75.75 0 0 0-1.28-.53l-3 3a.75.75 0 0 0-.22.53v4.5c0 .199.079.39.22.53l3 3a.75.75 0 0 0 1.28-.53V4.75Z"
                    />
                </svg>

                <!-- <img
                    :src="getIcon(item.type, isExpanded)"
                    alt=""
                    class="w-6 h-6"
                /> -->
            </div>
            <h1 class="flex-1 ml-4 font-semibold">{{ item.name }}</h1>
            <div v-if="item.attachments?.length" class="mr-2 text-gray-500">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                    class="size-5"
                >
                    <path
                        fill-rule="evenodd"
                        d="M4.5 2A1.5 1.5 0 0 0 3 3.5v13A1.5 1.5 0 0 0 4.5 18h11a1.5 1.5 0 0 0 1.5-1.5V7.621a1.5 1.5 0 0 0-.44-1.06l-4.12-4.122A1.5 1.5 0 0 0 11.378 2H4.5Zm2.25 8.5a.75.75 0 0 0 0 1.5h6.5a.75.75 0 0 0 0-1.5h-6.5Zm0 3a.75.75 0 0 0 0 1.5h6.5a.75.75 0 0 0 0-1.5h-6.5Z"
                        clip-rule="evenodd"
                    />
                </svg>
            </div>
        </div>

        <div v-if="item.type === 'folder' && isExpanded && item.children">
            <StudentFolderTreeItem
                v-for="child in item.children"
                :key="child.id"
                :item="child"
                :histories="histories"
                :depth="depth + 1"
                :selectedItem="selectedItem"
                :onSelect="onSelect"
            />
        </div>
    </div>
</template>
