<script setup>
import { ref, computed, watch, nextTick, onBeforeUnmount } from "vue";
import { defineProps } from "vue";
import FolderTreeItem from "./FolderTreeItem.vue";
import videojs from "video.js";
import "video.js/dist/video-js.css"; // CSS 추가

const props = defineProps({
    wire: {},
    mingleData: {},
});

const { wire, mingleData } = props;

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

// watch lastSelectedVideo
watch(lastSelectedVideo, (newVal) => {
    destroyPlayer();
    // video_url(외부 링크)가 있으면 iframe/링크로, 없고 video(파일)가 있으면 player로
    if (newVal?.video) {
        nextTick(() => initializePlayer(newVal.video));
    }
});

// 외부 영상 링크를 임베드 가능한 형태로 변환 (YouTube, Vimeo 등)
const toEmbedUrl = (url) => {
    if (!url) return null;
    try {
        // YouTube
        const ytMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([\w-]+)/);
        if (ytMatch) {
            return `https://www.youtube.com/embed/${ytMatch[1]}`;
        }
        // Vimeo
        const vimeoMatch = url.match(/vimeo\.com\/(\d+)/);
        if (vimeoMatch) {
            return `https://player.vimeo.com/video/${vimeoMatch[1]}`;
        }
        // 구글 드라이브 (file/d/{id}/view → preview)
        const driveMatch = url.match(/drive\.google\.com\/file\/d\/([\w-]+)/);
        if (driveMatch) {
            return `https://drive.google.com/file/d/${driveMatch[1]}/preview`;
        }
        // 기본: 그대로 반환 (iframe에 embed 가능한 URL 가정)
        return url;
    } catch (e) {
        return url;
    }
};

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
        // ref reactivity 보장 위해 새 객체로 할당
        lastSelectedVideo.value = { ...item };
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

const updateSiblingOrders = (items, startOrder) => {
    items
        .filter((item) => item.order >= startOrder)
        .sort((a, b) => a.order - b.order)
        .forEach((item, index) => {
            item.order = startOrder + index + 1;
        });
};

const addNewItem = (type = "folder") => {
    const event = new Event("addNewItem");
    event.data = { type };
    event.callback = (newItem) => {
        if (!newItem) return;

        const insertItem = {
            id: generateUUID(),
            // name: newItem.name,
            // type: newItem.type,
            order: 1,
            ...newItem,
        };

        if (type === "folder") {
            insertItem.children = [];
        }

        if (!selectedItem.value) {
            insertItem.order = folderTree.value.length + 1;
            folderTree.value.push(insertItem);
        } else {
            if (selectedItem.value.type === "folder") {
                if (!selectedItem.value.children) {
                    selectedItem.value.children = [];
                }
                insertItem.order = selectedItem.value.children.length + 1;
                selectedItem.value.children.push(insertItem);
            } else {
                const parent = findParent(
                    folderTree.value,
                    selectedItem.value.id
                );
                if (parent) {
                    insertItem.order = parent.children.length + 1;
                    parent.children.push(insertItem);
                } else {
                    insertItem.order = folderTree.value.length + 1;
                    folderTree.value.push(insertItem);
                }
            }
        }

        // Mingle로 데이터 전송
        wire.call("updateTree", folderTree.value);
    };
    window.dispatchEvent(event);
};

const editItem = (item) => {
    const event = new Event("editItem");
    event.data = item;
    event.callback = (newItem) => {
        if (!newItem) return;
        if (newItem.delete) {
            // delete item
            const parent = findParent(folderTree.value, item.id);
            if (parent) {
                parent.children = parent.children.filter(
                    (child) => child.id !== item.id
                );
                parent.children = reorderItems(parent.children);
            } else {
                folderTree.value = folderTree.value.filter(
                    (child) => child.id !== item.id
                );
                folderTree.value = reorderItems(folderTree.value);
            }
            if (item.id === selectedItem.value?.id) {
                setSelectedItem(null);
                lastSelectedVideo.value = null;
            }
        } else {
            const oldOrder = item.order;
            const newOrder = parseInt(newItem.order);
            item.name = newItem.name;
            if (newItem.video) {
                item.video = newItem.video;
                if (item.id === selectedItem.value?.id) {
                    lastSelectedVideo.value = null;
                    nextTick(() => setSelectedItem(item));
                }
            }
            if (newItem.attachments) {
                item.attachments = newItem.attachments;
            }
            // 외부 링크 모드 (퍼펙트 스터디 외 학원)
            if (newItem.video_url !== undefined) {
                item.video_url = newItem.video_url;
                if (item.id === selectedItem.value?.id) {
                    lastSelectedVideo.value = null;
                    nextTick(() => setSelectedItem(item));
                }
            }
            if (newItem.attachment_links !== undefined) {
                item.attachment_links = newItem.attachment_links;
            }

            // Get siblings
            const parent = findParent(folderTree.value, item.id);
            const siblings = parent ? parent.children : folderTree.value;

            // Validate and adjust new order
            const maxOrder = siblings.length;
            const validNewOrder = Math.min(Math.max(1, newOrder), maxOrder);

            // Update orders
            if (oldOrder !== validNewOrder) {
                if (oldOrder < validNewOrder) {
                    // Moving item to later position
                    siblings
                        .filter(
                            (sibling) =>
                                sibling.order > oldOrder &&
                                sibling.order <= validNewOrder
                        )
                        .forEach((sibling) => sibling.order--);
                } else {
                    // Moving item to earlier position
                    siblings
                        .filter(
                            (sibling) =>
                                sibling.order >= validNewOrder &&
                                sibling.order < oldOrder
                        )
                        .forEach((sibling) => sibling.order++);
                }
                item.order = validNewOrder;
            }

            // Ensure no gaps in order
            if (parent) {
                parent.children = reorderItems(parent.children);
            } else {
                folderTree.value = reorderItems(folderTree.value);
            }
        }
        wire.call("updateTree", folderTree.value);
    };
    window.dispatchEvent(event);
};

const viewHistory = (item) => {
    const event = new Event("viewHistory");
    event.data = item;
    window.dispatchEvent(event);
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
    <div class="p-6">
        <div class="w-full h-[740px] shadow bg-white rounded-lg flex flex-row">
            <div class="w-[320px] border-r flex flex-col">
                <div
                    class="border-b h-16 flex items-center pl-[12px] text-primary-400 justify-between"
                >
                    <button
                        @click="addNewItem('folder')"
                        class="flex flex-1 flex-row items-center justify-center gap-x-2 px-2 py-2 font-semibold"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            class="size-5"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M3.75 3A1.75 1.75 0 0 0 2 4.75v10.5c0 .966.784 1.75 1.75 1.75h12.5A1.75 1.75 0 0 0 18 15.25v-8.5A1.75 1.75 0 0 0 16.25 5h-4.836a.25.25 0 0 1-.177-.073L9.823 3.513A1.75 1.75 0 0 0 8.586 3H3.75ZM10 8a.75.75 0 0 1 .75.75v1.5h1.5a.75.75 0 0 1 0 1.5h-1.5v1.5a.75.75 0 0 1-1.5 0v-1.5h-1.5a.75.75 0 0 1 0-1.5h1.5v-1.5A.75.75 0 0 1 10 8Z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        폴더 추가
                    </button>
                    <div class="w-px h-full border-l"></div>
                    <button
                        @click="addNewItem('video')"
                        class="flex flex-1 flex-row items-center justify-center gap-x-2 px-2 py-2 font-semibold"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            class="size-5"
                        >
                            <path
                                d="M3.25 4A2.25 2.25 0 0 0 1 6.25v7.5A2.25 2.25 0 0 0 3.25 16h7.5A2.25 2.25 0 0 0 13 13.75v-7.5A2.25 2.25 0 0 0 10.75 4h-7.5ZM19 4.75a.75.75 0 0 0-1.28-.53l-3 3a.75.75 0 0 0-.22.53v4.5c0 .199.079.39.22.53l3 3a.75.75 0 0 0 1.28-.53V4.75Z"
                            />
                        </svg>

                        영상 추가
                    </button>
                </div>
                <div
                    class="overflow-auto flex-1 flex flex-col py-3"
                    v-if="folderTree?.length > 0"
                >
                    <FolderTreeItem
                        v-for="folder in folderTree"
                        :key="folder.id"
                        :item="folder"
                        :selectedItem="selectedItem"
                        :on-select="setSelectedItem"
                        :on-edit="editItem"
                    />
                </div>
                <div
                    class="flex flex-1 items-center mb-20 font-medium text-gray-600 justify-center"
                    v-else
                >
                    등록된 강의가 없습니다.
                </div>
            </div>
            <div
                class="flex-1 flex items-center justify-center overflow-hidden"
            >
                <div
                    class="flex-1 flex flex-col w-full h-full"
                    v-if="lastSelectedVideo"
                >
                    <div class="border-b min-h-16 flex items-center px-[24px]">
                        <h1 class="text-gray-700 text-lg font-bold flex-1">
                            {{ lastSelectedVideo.name }}
                        </h1>
                        <button
                            @click="viewHistory(lastSelectedVideo)"
                            class="flex flex-row items-center justify-center gap-x-2 px-2 py-2 font-semibold text-primary-400"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                class="size-5"
                            >
                                <path
                                    d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"
                                />
                                <path
                                    fill-rule="evenodd"
                                    d="M.664 10.59a1.651 1.651 0 0 1 0-1.186A10.004 10.004 0 0 1 10 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0 1 10 17c-4.257 0-7.893-2.66-9.336-6.41ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"
                                    clip-rule="evenodd"
                                />
                            </svg>

                            시청 내역
                        </button>
                        <!-- <div class="w-px h-full border-l"></div> -->
                        <!-- <button
                            @click="addNewItem('video')"
                            class="flex flex-1 flex-row items-center justify-center gap-x-2 px-2 py-2 font-semibold"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                class="size-5"
                            >
                                <path
                                    d="M3.25 4A2.25 2.25 0 0 0 1 6.25v7.5A2.25 2.25 0 0 0 3.25 16h7.5A2.25 2.25 0 0 0 13 13.75v-7.5A2.25 2.25 0 0 0 10.75 4h-7.5ZM19 4.75a.75.75 0 0 0-1.28-.53l-3 3a.75.75 0 0 0-.22.53v4.5c0 .199.079.39.22.53l3 3a.75.75 0 0 0 1.28-.53V4.75Z"
                                />
                            </svg>

                            영상 추가
                        </button> -->
                    </div>
                    <div
                        class="flex-1 flex items-center justify-center w-full h-[calc(100%-4rem)] relative"
                    >
                        <!-- 1. 업로드된 비디오 파일 (퍼펙트 스터디) -->
                        <div
                            class="flex flex-col items-center justify-center w-full h-full"
                            v-if="lastSelectedVideo?.video"
                        >
                            <div class="video-container w-full h-full"></div>
                        </div>
                        <!-- 2. 외부 영상 링크 (그 외 학원) -->
                        <div
                            class="flex flex-col w-full h-full"
                            v-else-if="lastSelectedVideo?.video_url"
                        >
                            <iframe
                                :src="toEmbedUrl(lastSelectedVideo.video_url)"
                                class="w-full flex-1"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                            ></iframe>
                            <a
                                :href="lastSelectedVideo.video_url"
                                target="_blank"
                                class="text-xs text-primary-500 mt-1 hover:underline truncate"
                            >
                                새 창에서 열기 ↗
                            </a>
                        </div>
                        <!-- 3. 영상 없음 -->
                        <div
                            class="flex flex-col items-center text-lg font-medium justify-center"
                            v-else
                        >
                            <div class="text-gray-600">
                                영상을 업로드하거나 링크를 등록해주세요.
                            </div>
                            <button
                                @click="editItem(lastSelectedVideo)"
                                class="flex flex-1 flex-row items-center justify-center gap-x-2 px-2 py-2 font-semibold text-primary-400 mt-1"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                    class="size-5"
                                >
                                    <path
                                        d="M9.25 13.25a.75.75 0 0 0 1.5 0V4.636l2.955 3.129a.75.75 0 0 0 1.09-1.03l-4.25-4.5a.75.75 0 0 0-1.09 0l-4.25 4.5a.75.75 0 1 0 1.09 1.03L9.25 4.636v8.614Z"
                                    />
                                    <path
                                        d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z"
                                    />
                                </svg>
                                등록하기
                            </button>
                        </div>
                    </div>
                </div>
                <div
                    v-else
                    class="flex flex-1 flex-col items-center text-lg font-medium justify-center"
                >
                    <div class="text-gray-600">강의를 선택해주세요.</div>
                </div>
            </div>
        </div>
    </div>
</template>
