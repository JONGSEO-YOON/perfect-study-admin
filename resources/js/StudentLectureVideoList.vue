<script setup>
import { ref, computed, watch, nextTick, onBeforeUnmount } from "vue";
import { defineProps } from "vue";
import StudentFolderTreeItem from "./StudentFolderTreeItem.vue";
import videojs from "video.js";
import "video.js/dist/video-js.css"; // CSS 추가

const props = defineProps({
    wire: {},
    mingleData: {},
});

const { wire, mingleData } = props;

const histories = ref(mingleData.histories);

let player = null; // Video.js 플레이어 인스턴스 관리

// 플레이어 정리 함수
const destroyPlayer = () => {
    if (player) {
        player.dispose();
        player = null;
    }
};

// 세션 ID 생성 함수
const generateUUID = () => {
    return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(
        /[xy]/g,
        function (c) {
            const r = (Math.random() * 16) | 0;
            const v = c == "x" ? r : (r & 0x3) | 0x8;
            return v.toString(16);
        }
    );
};

const folderTree = ref(mingleData.tree);

const selectedItem = ref(null);
const lastSelectedVideo = ref(null);
const attachmentsVisible = ref(false);

// watch lastSelectedVideo
watch(lastSelectedVideo, (newVal) => {
    attachmentsVisible.value = false;
    destroyPlayer();
    if (newVal?.video) {
        nextTick(() => initializePlayer(newVal.video));
    }
});

const createVideoElement = () => {
    // 기존 video-container 찾기
    const container = document.querySelector(".video-container");
    if (!container) return;

    // 새로운 video 엘리먼트 생성
    const video = document.createElement("video");
    video.className =
        "video-js vjs-big-play-centered w-full h-full object-contain";
    video.controls = true;

    // container에 추가
    container.innerHTML = ""; // 기존 내용 제거
    container.appendChild(video);

    return video;
};

const initializePlayer = (videoSrc) => {
    const video = createVideoElement();
    if (video) {
        player = videojs(video, {
            controls: true,
            autoplay: false,
            preload: "auto",
            responsive: true,
            sources: [
                {
                    src: `/storage/${videoSrc}`,
                    type: "video/mp4",
                },
            ],
        });
    }
};

const setSelectedItem = (item) => {
    selectedItem.value = item;
    if (item?.type === "video") {
        lastSelectedVideo.value = item;
        Livewire?.dispatch("videoSelected", {
            video: item,
        });
        histories.value.push({
            video_id: item.id,
        });
    }
};

const reorderItems = (items) => {
    return items
        .sort((a, b) => a.order - b.order)
        .map((item, index) => {
            item.order = index + 1;
            if (item.children?.length > 0) {
                item.children = reorderItems(item.children);
            }
            return item;
        });
};

const findParent = (items, targetId, parent = null) => {
    for (const item of items) {
        if (item.children) {
            if (item.children.some((child) => child.id === targetId)) {
                return item;
            }
            const foundParent = findParent(item.children, targetId, item);
            if (foundParent) return foundParent;
        }
    }
    return null;
};

onBeforeUnmount(() => {
    destroyPlayer();
});
</script>

<template>
    <div
        class="w-full h-[740px] shadow bg-white rounded-lg flex-col flex sm:flex-row md:text-sm lg:text-base"
    >
        <div class="md:w-[200px] lg:w-[220px] border-r flex flex-col">
            <div
                class="overflow-auto flex-1 flex flex-col py-3"
                v-if="folderTree?.length > 0"
            >
                <StudentFolderTreeItem
                    v-for="folder in folderTree"
                    :key="folder.id"
                    :item="folder"
                    :selectedItem="selectedItem"
                    :on-select="setSelectedItem"
                    :histories="histories"
                />
            </div>
            <div
                class="flex flex-1 items-center mb-20 font-medium text-gray-600 justify-center"
                v-else
            >
                등록된 강의가 없습니다.
            </div>
        </div>
        <div class="flex-1 flex items-center justify-center overflow-hidden">
            <div
                class="flex-1 flex flex-col w-full h-full"
                v-if="lastSelectedVideo"
            >
                <div
                    class="border-b min-h-14 flex items-center px-[24px] text-base relative"
                >
                    <h1 class="text-gray-700 font-bold flex-1">
                        {{ lastSelectedVideo.name }}
                    </h1>
                    <button
                        v-if="lastSelectedVideo.attachments?.length > 0"
                        @click="attachmentsVisible = !attachmentsVisible"
                        class="text-gray-700 text-sm flex flex-row gap-x-2 items-center"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            class="size-4"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M15.621 4.379a3 3 0 0 0-4.242 0l-7 7a3 3 0 0 0 4.241 4.243h.001l.497-.5a.75.75 0 0 1 1.064 1.057l-.498.501-.002.002a4.5 4.5 0 0 1-6.364-6.364l7-7a4.5 4.5 0 0 1 6.368 6.36l-3.455 3.553A2.625 2.625 0 1 1 9.52 9.52l3.45-3.451a.75.75 0 1 1 1.061 1.06l-3.45 3.451a1.125 1.125 0 0 0 1.587 1.595l3.454-3.553a3 3 0 0 0 0-4.242Z"
                                clip-rule="evenodd"
                            />
                        </svg>

                        첨부파일
                    </button>
                    <div
                        @click.away="attachmentsVisible = false"
                        v-if="
                            lastSelectedVideo.attachments?.length > 0 &&
                            attachmentsVisible
                        "
                        class="shadow-xl py-1 divide-y text-sm bg-white rounded right-2 absolute top-full z-20 flex flex-col"
                    >
                        <a
                            class="py-2 px-4 flex text-left"
                            :key="attachment"
                            target="_blank"
                            :href="`/storage/${attachment}`"
                            v-for="attachment in lastSelectedVideo.attachments"
                            >{{ attachment }}</a
                        >
                    </div>
                </div>
                <div
                    class="flex-1 flex items-center justify-center w-full h-[calc(100%-4rem)] relative"
                >
                    <div
                        class="flex flex-col items-center justify-center w-full h-full"
                        v-if="lastSelectedVideo?.video"
                    >
                        <div class="video-container w-full h-full"></div>
                    </div>
                    <div
                        class="flex flex-col items-center font-medium justify-center"
                        v-else
                    >
                        <div class="text-gray-600">영상이 없습니다.</div>
                    </div>
                </div>
            </div>
            <div
                v-else
                class="flex flex-1 flex-col items-center font-medium justify-center"
            >
                <div class="text-gray-600">강의를 선택해주세요.</div>
            </div>
        </div>
    </div>
</template>
