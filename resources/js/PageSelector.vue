<script setup>
import { ref, computed, onMounted } from "vue";
import { defineProps } from "vue";
import PageRenderer from "./PageRenderer.vue";

const props = defineProps({
    wire: {},
    mingleData: {},
});

const { wire, mingleData } = props;
const pages = ref(mingleData.pages);
const showMarginModal = ref(false);
const id = mingleData.id;

const extracting = ref(false);
const completing = ref(false);
const status = ref("page-select");
const cache = ref(mingleData.cache);
const extractionMargin = ref({
    top: 5,
    right: 3,
    bottom: 7,
    left: 3,
});

const selectedPages = ref([]);
for (const page of pages.value) {
    selectedPages.value.push(page);
}
const lastSelectedPage = ref(null);
const extractedPages = ref([]);
const croppedQuestions = ref([]);

const togglePageSelection = (page) => {
    const index = selectedPages.value.findIndex(
        (p) => p.number === page.number
    );
    if (index > -1) {
        selectedPages.value.splice(index, 1);
    } else {
        selectedPages.value.push(page);
    }
    lastSelectedPage.value = page;
};

const extractQuestions = async () => {
    showMarginModal.value = false;
    extracting.value = true;
    extractedPages.value = [];
    // sort selected pages
    selectedPages.value.sort((a, b) => a.number - b.number);
    for (const selectedPage of selectedPages.value) {
        const questions = await wire.extractQuestions(selectedPage, {
            margin: extractionMargin.value,
        });
        if (questions?.length) {
            extractedPages.value.push({
                page: selectedPage,
                data: questions,
            });
        } else {
            extractedPages.value.push({
                page: selectedPage,
                data: [],
            });
        }
        // const _path = `/Users/choeintag/Repositories/math-bank-proto/storage/app/public/converted-pdfs/${id}/page_${selectedPage.number}.jpg`;
        // const response = await fetch(
        //     `http://172.30.1.59:8088/detect_problems?input_path=${_path}`
        // );
        // const data = await response.json();

        // extractedPages.value.push({
        //     page: selectedPage,
        //     data: [],
        // });
    }
    extracting.value = false;
    status.value = "extracted";
};

const onUpdateData = (newData, pageNumber) => {
    const pageIndex = extractedPages.value.findIndex(
        (page) => page.page.number === pageNumber
    );
    if (pageIndex !== -1) {
        extractedPages.value[pageIndex].data = newData;
    }
};

const editQuestions = async () => {
    const response = await wire.editQuestions(extractedQuestions.value);
    croppedQuestions.value = response.questions;
    status.value = "editing";
};

const editQuestion = async (question) => {
    const event = new Event("editQuestion");
    event.data = { ...question, ...question.data, seq: question.number };
    event.callback = (editedQuestion) => {
        question.data = editedQuestion;
    };
    window.dispatchEvent(event);
};

const editSubQuestion1 = async (question) => {
    const event = new Event("editQuestion");
    event.data = { ...question.sub1_data, is_sub_question: true };
    event.callback = (editedQuestion) => {
        question.sub1_data = editedQuestion;
    };
    window.dispatchEvent(event);
};

const editSubQuestion2 = async (question) => {
    const event = new Event("editQuestion");
    event.data = { ...question.sub2_data, is_sub_question: true };
    event.callback = (editedQuestion) => {
        question.sub2_data = editedQuestion;
    };
    window.dispatchEvent(event);
};

const confirmDeleteQuestion = async (question) => {
    const event = new Event("deleteQuestion");
    event.data = question;
    event.callback = () => {
        deleteQuestion(question);
    };
    window.dispatchEvent(event);
};

const confirmAddQuestions = async () => {
    const event = new Event("addQuestions");
    event.data = editedQuestions.value;
    event.callback = async () => {
        await wire.addQuestions(editedQuestions.value);
    };
    window.dispatchEvent(event);
};

const deleteQuestion = async (question) => {
    const index = croppedQuestions.value.findIndex(
        (q) => q.number === question.number
    );
    if (index > -1) {
        croppedQuestions.value.splice(index, 1);
    }
};

const extractedQuestions = computed(() => {
    return extractedPages.value.reduce((acc, page) => {
        return acc.concat(
            page.data.map((rect) => ({
                ...rect,
                pageNumber: page.page.number,
            }))
        );
    }, []);
});

const editedQuestions = computed(() => {
    return croppedQuestions.value.filter((q) => q.data);
    // return croppedQuestions.value.filter(
    //     (q) => q.data && q.sub1_data && q.sub2_data
    // );
});

const isDragging = ref(false);
const currentHandle = ref(null);
const startPos = ref({ x: 0, y: 0 });
const startMargin = ref({ top: 0, right: 0, bottom: 0, left: 0 });

const startDrag = (event, handle) => {
    isDragging.value = true;
    currentHandle.value = handle;
    startPos.value = {
        x: event.clientX,
        y: event.clientY,
    };
    startMargin.value = { ...extractionMargin.value };

    // Add event listeners
    document.addEventListener("mousemove", handleDrag);
    document.addEventListener("mouseup", stopDrag);
};

const handleDrag = (event) => {
    if (!isDragging.value) return;

    const containerRect = event.target
        .closest(".relative")
        .getBoundingClientRect();
    const deltaX = event.clientX - startPos.value.x;
    const deltaY = event.clientY - startPos.value.y;

    // Calculate percentage movement
    const percentX = (deltaX / containerRect.width) * 100;
    const percentY = (deltaY / containerRect.height) * 100;

    switch (currentHandle.value) {
        case "left":
            extractionMargin.value.left = Math.max(
                0,
                Math.min(
                    100 - extractionMargin.value.right,
                    startMargin.value.left + percentX
                )
            );
            break;
        case "right":
            extractionMargin.value.right = Math.max(
                0,
                Math.min(
                    100 - extractionMargin.value.left,
                    startMargin.value.right - percentX
                )
            );
            break;
        case "top":
            extractionMargin.value.top = Math.max(
                0,
                Math.min(
                    100 - extractionMargin.value.bottom,
                    startMargin.value.top + percentY
                )
            );
            break;
        case "bottom":
            extractionMargin.value.bottom = Math.max(
                0,
                Math.min(
                    100 - extractionMargin.value.top,
                    startMargin.value.bottom - percentY
                )
            );
            break;
    }
};

const stopDrag = () => {
    isDragging.value = false;
    currentHandle.value = null;

    // Remove event listeners
    document.removeEventListener("mousemove", handleDrag);
    document.removeEventListener("mouseup", stopDrag);
};

onMounted(() => {
    window.addEventListener("reloadPages", async (event) => {
        const data = await wire.refreshPages();
        pages.value = data.pages;
        cache;
    });
});
</script>

<template>
    <ol
        style="
            --c-400: var(--primary-400);
            --c-500: var(--primary-500);
            --c-600: var(--primary-600);
        "
        class="flex items-center w-full p-3 space-x-2 text-sm font-medium text-center text-gray-500 bg-white border border-gray-200 rounded-lg shadow-sm dark:text-gray-400 sm:text-base dark:bg-gray-800 dark:border-gray-700 sm:p-4 sm:space-x-4 rtl:space-x-reverse mb-4"
    >
        <li
            class="flex items-center"
            :class="{
                'text-custom-600 dark:text-custom-500':
                    status === 'page-select',
            }"
        >
            <span
                class="flex items-center justify-center w-5 h-5 me-2 text-xs border rounded-full shrink-0 dark:border-gray-400"
                :class="{
                    'border-custom-500': status === 'page-select',
                }"
            >
                1
            </span>
            페이지 선택<span class="hidden sm:inline-flex sm:ms-2">Info</span>
            <svg
                class="w-3 h-3 ms-2 sm:ms-4 rtl:rotate-180"
                aria-hidden="true"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 12 10"
            >
                <path
                    stroke="currentColor"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="m7 9 4-4-4-4M1 9l4-4-4-4"
                />
            </svg>
        </li>
        <li
            class="flex items-center"
            :class="{
                'text-custom-600 dark:text-custom-500': status === 'extracted',
            }"
        >
            <span
                class="flex items-center justify-center w-5 h-5 me-2 text-xs border border-gray-500 rounded-full shrink-0 dark:border-gray-400"
                :class="{
                    '!border-custom-500': status === 'extracted',
                }"
            >
                2
            </span>
            문제 추출<span class="hidden sm:inline-flex sm:ms-2">Info</span>
            <svg
                class="w-3 h-3 ms-2 sm:ms-4 rtl:rotate-180"
                aria-hidden="true"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 12 10"
            >
                <path
                    stroke="currentColor"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="m7 9 4-4-4-4M1 9l4-4-4-4"
                />
            </svg>
        </li>
        <li
            class="flex items-center"
            :class="{
                'text-custom-600 dark:text-custom-500': status === 'editing',
            }"
        >
            <span
                class="flex items-center justify-center w-5 h-5 me-2 text-xs border border-gray-500 rounded-full shrink-0 dark:border-gray-400"
                :class="{
                    '!border-custom-500': status === 'editing',
                }"
            >
                3
            </span>
            문제 편집<span class="hidden sm:inline-flex sm:ms-2">Info</span>
            <svg
                class="w-3 h-3 ms-2 sm:ms-4 rtl:rotate-180"
                aria-hidden="true"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 12 10"
            >
                <path
                    stroke="currentColor"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="m7 9 4-4-4-4M1 9l4-4-4-4"
                />
            </svg>
        </li>
        <li
            class="flex items-center"
            :class="{
                'text-custom-600 dark:text-custom-500': status === 'complete',
            }"
        >
            <span
                class="flex items-center justify-center w-5 h-5 me-2 text-xs border border-gray-500 rounded-full shrink-0 dark:border-gray-400"
                :class="{
                    '!border-custom-500': status === 'complete',
                }"
            >
                4
            </span>
            문제 확인
        </li>
    </ol>
    <Transition
        enter-from-class="translate-x-1/3 opacity-0"
        leave-to-class="-translate-x-1/3 opacity-0"
        mode="out-in"
    >
        <div
            v-if="status === 'page-select'"
            class="flex flex-col transition duration-300"
        >
            <div
                class="flex justify-between gap-x-2"
                v-show="cache.is_completed"
            >
                <div class="flex gap-x-2">
                    <button
                        v-if="!extracting"
                        :disabled="selectedPages.length === pages.length"
                        style="
                            --c-400: var(--primary-400);
                            --c-500: var(--primary-500);
                            --c-600: var(--primary-600);
                        "
                        class="fi-btn relative grid-flow-col disabled:opacity-50 items-center justify-center font-semibold outline-none transition-all duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-lg fi-btn-size-lg gap-1.5 px-3.5 py-2.5 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50"
                        @click="selectedPages = pages"
                    >
                        <span class="fi-btn-label"> 전체 선택 </span>
                    </button>
                    <button
                        v-if="!extracting"
                        :disabled="!selectedPages?.length"
                        style="
                            --c-400: var(--primary-400);
                            --c-500: var(--primary-500);
                            --c-600: var(--primary-600);
                        "
                        class="fi-btn relative grid-flow-col disabled:opacity-50 items-center justify-center font-semibold outline-none transition-all duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-lg fi-btn-size-lg gap-1.5 px-3.5 py-2.5 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50"
                        @click="selectedPages = []"
                    >
                        <span class="fi-btn-label"> 전체 선택 해제 </span>
                    </button>
                </div>

                <button
                    style="
                        --c-400: var(--primary-400);
                        --c-500: var(--primary-500);
                        --c-600: var(--primary-600);
                    "
                    :disabled="!selectedPages?.length || extracting"
                    class="fi-btn relative grid-flow-col disabled:opacity-50 items-center justify-center font-semibold outline-none transition-all duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-lg fi-btn-size-lg gap-1.5 px-3.5 py-2.5 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50"
                    @click="showMarginModal = true"
                >
                    <svg
                        class="fi-btn-icon transition duration-75 h-5 w-5 text-white"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        aria-hidden="true"
                        data-slot="icon"
                    >
                        <path
                            d="M15.98 1.804a1 1 0 0 0-1.96 0l-.24 1.192a1 1 0 0 1-.784.785l-1.192.238a1 1 0 0 0 0 1.962l1.192.238a1 1 0 0 1 .785.785l.238 1.192a1 1 0 0 0 1.962 0l.238-1.192a1 1 0 0 1 .785-.785l1.192-.238a1 1 0 0 0 0-1.962l-1.192-.238a1 1 0 0 1-.785-.785l-.238-1.192ZM6.949 5.684a1 1 0 0 0-1.898 0l-.683 2.051a1 1 0 0 1-.633.633l-2.051.683a1 1 0 0 0 0 1.898l2.051.684a1 1 0 0 1 .633.632l.683 2.051a1 1 0 0 0 1.898 0l.683-2.051a1 1 0 0 1 .633-.633l2.051-.683a1 1 0 0 0 0-1.898l-2.051-.683a1 1 0 0 1-.633-.633L6.95 5.684ZM13.949 13.684a1 1 0 0 0-1.898 0l-.184.551a1 1 0 0 1-.632.633l-.551.183a1 1 0 0 0 0 1.898l.551.183a1 1 0 0 1 .633.633l.183.551a1 1 0 0 0 1.898 0l.184-.551a1 1 0 0 1 .632-.633l.551-.183a1 1 0 0 0 0-1.898l-.551-.184a1 1 0 0 1-.633-.632l-.183-.551Z"
                        ></path>
                    </svg>
                    <span class="fi-btn-label" v-if="!extracting">
                        {{ selectedPages?.length }} / {{ pages.length }} 페이지
                        문제 추출
                    </span>
                    <span class="fi-btn-label" v-else>
                        {{ extractedPages?.length }} /
                        {{ selectedPages.length }} 문제 추출중...
                    </span>
                </button>
            </div>
            <div class="flex flex-col bg-gray-200 rounded border p-4 mt-4">
                <div class="flex overflow-x-auto flex-nowrap gap-x-2">
                    <div
                        class="flex flex-col items-center justify-center gap-y-2 min-w-max"
                        v-for="page in pages"
                        :key="page.number"
                        @click="togglePageSelection(page)"
                    >
                        <div
                            :class="[
                                'rounded border p-2 cursor-pointer transition-all',
                                selectedPages.includes(page)
                                    ? 'bg-blue-300'
                                    : 'bg-white hover:bg-gray-200',
                            ]"
                        >
                            <img :src="page.url" class="h-32 w-auto" />
                        </div>
                        <h1 class="text-sm">{{ page.number }}</h1>
                    </div>
                </div>
                <div class="flex h-[800px] items-center justify-center mt-10">
                    <img
                        class="h-full"
                        v-if="lastSelectedPage"
                        :src="lastSelectedPage?.url"
                    />
                </div>
            </div>
        </div>
        <div
            v-else-if="status === 'extracted'"
            class="flex flex-col transition duration-300"
        >
            <div class="flex justify-end">
                <button
                    :disabled="!extractedQuestions?.length || completing"
                    style="
                        --c-400: var(--primary-400);
                        --c-500: var(--primary-500);
                        --c-600: var(--primary-600);
                    "
                    class="fi-btn relative grid-flow-col disabled:opacity-50 items-center justify-center font-semibold outline-none transition-all duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-lg fi-btn-size-lg gap-1.5 px-3.5 py-2.5 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50"
                    @click="editQuestions"
                >
                    <svg
                        class="fi-btn-icon transition duration-75 h-5 w-5 text-white"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        aria-hidden="true"
                        data-slot="icon"
                    >
                        <path
                            d="M15.98 1.804a1 1 0 0 0-1.96 0l-.24 1.192a1 1 0 0 1-.784.785l-1.192.238a1 1 0 0 0 0 1.962l1.192.238a1 1 0 0 1 .785.785l.238 1.192a1 1 0 0 0 1.962 0l.238-1.192a1 1 0 0 1 .785-.785l1.192-.238a1 1 0 0 0 0-1.962l-1.192-.238a1 1 0 0 1-.785-.785l-.238-1.192ZM6.949 5.684a1 1 0 0 0-1.898 0l-.683 2.051a1 1 0 0 1-.633.633l-2.051.683a1 1 0 0 0 0 1.898l2.051.684a1 1 0 0 1 .633.632l.683 2.051a1 1 0 0 0 1.898 0l.683-2.051a1 1 0 0 1 .633-.633l2.051-.683a1 1 0 0 0 0-1.898l-2.051-.683a1 1 0 0 1-.633-.633L6.95 5.684ZM13.949 13.684a1 1 0 0 0-1.898 0l-.184.551a1 1 0 0 1-.632.633l-.551.183a1 1 0 0 0 0 1.898l.551.183a1 1 0 0 1 .633.633l.183.551a1 1 0 0 0 1.898 0l.184-.551a1 1 0 0 1 .632-.633l.551-.183a1 1 0 0 0 0-1.898l-.551-.184a1 1 0 0 1-.633-.632l-.183-.551Z"
                        ></path>
                    </svg>
                    <span class="fi-btn-label">
                        {{ extractedQuestions?.length }}개 문제 편집
                    </span>
                </button>
            </div>
            <div
                class="flex flex-col bg-gray-200 rounded border p-4 h-[800px] overflow-y-auto gap-y-4 mt-4"
            >
                <div
                    class="items-center justify-center flex"
                    v-for="page in extractedPages"
                    :key="page.page.number"
                >
                    <PageRenderer
                        @update-data="
                            (newData) => onUpdateData(newData, page.page.number)
                        "
                        :page="page.page"
                        :data="page.data"
                    />
                </div>
            </div>
        </div>
        <div
            v-else-if="status === 'editing'"
            style="
                --c-400: var(--primary-400);
                --c-500: var(--primary-500);
                --c-600: var(--primary-600);
            "
        >
            <div
                class="flex flex-col bg-gray-200 p-4 rounded gap-y-3 max-w-2xl mx-auto"
            >
                <div class="flex justify-end">
                    <button
                        style="
                            --c-400: var(--primary-400);
                            --c-500: var(--primary-500);
                            --c-600: var(--primary-600);
                        "
                        :disabled="
                            editedQuestions?.length !== croppedQuestions?.length
                        "
                        class="fi-btn relative grid-flow-col disabled:opacity-50 items-center justify-center font-semibold outline-none transition-all duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-lg fi-btn-size-lg gap-1.5 px-3.5 py-2.5 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50"
                        @click="confirmAddQuestions"
                    >
                        <svg
                            class="fi-btn-icon transition duration-75 h-5 w-5 text-white"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            aria-hidden="true"
                            data-slot="icon"
                        >
                            <path
                                d="M15.98 1.804a1 1 0 0 0-1.96 0l-.24 1.192a1 1 0 0 1-.784.785l-1.192.238a1 1 0 0 0 0 1.962l1.192.238a1 1 0 0 1 .785.785l.238 1.192a1 1 0 0 0 1.962 0l.238-1.192a1 1 0 0 1 .785-.785l1.192-.238a1 1 0 0 0 0-1.962l-1.192-.238a1 1 0 0 1-.785-.785l-.238-1.192ZM6.949 5.684a1 1 0 0 0-1.898 0l-.683 2.051a1 1 0 0 1-.633.633l-2.051.683a1 1 0 0 0 0 1.898l2.051.684a1 1 0 0 1 .633.632l.683 2.051a1 1 0 0 0 1.898 0l.683-2.051a1 1 0 0 1 .633-.633l2.051-.683a1 1 0 0 0 0-1.898l-2.051-.683a1 1 0 0 1-.633-.633L6.95 5.684ZM13.949 13.684a1 1 0 0 0-1.898 0l-.184.551a1 1 0 0 1-.632.633l-.551.183a1 1 0 0 0 0 1.898l.551.183a1 1 0 0 1 .633.633l.183.551a1 1 0 0 0 1.898 0l.184-.551a1 1 0 0 1 .632-.633l.551-.183a1 1 0 0 0 0-1.898l-.551-.184a1 1 0 0 1-.633-.632l-.183-.551Z"
                            ></path>
                        </svg>
                        <span class="fi-btn-label" v-if="!extracting">
                            {{ editedQuestions?.length }} /
                            {{ croppedQuestions.length }} 문제 편집 완료
                        </span>
                        <span class="fi-btn-label" v-else>
                            문제 등록중...
                        </span>
                    </button>
                </div>
                <div
                    class="bg-white shadow flex flex-col rounded-lg"
                    v-for="question in croppedQuestions"
                >
                    <h1
                        class="text-2xl font-bold px-4 py-2.5 rounded-t-lg flex items-center"
                        :class="{
                            'bg-primary-400 text-white':
                                question.data &&
                                question.sub1_data &&
                                question.sub2_data,
                            'bg-gray-100':
                                !question.data ||
                                !question.sub1_data ||
                                !question.sub2_data,
                        }"
                    >
                        {{ question.number }}
                        <h2
                            class="flex items-center ml-4 text-lg"
                            v-if="question.data?.questionCategory"
                        >
                            {{ question.data.questionCategory.name }}
                        </h2>
                        <h2
                            class="flex items-center ml-4 text-sm"
                            v-if="question.data"
                        >
                            레벨 : {{ question.data?.level }}
                        </h2>
                        <h2
                            class="flex items-center ml-4 text-sm"
                            v-if="question.data"
                        >
                            정답 : {{ question.data?.answer }}
                        </h2>
                    </h1>
                    <img class="w-full" :src="question.url" />
                    <div class="flex flex-row">
                        <button
                            @click="editQuestion(question)"
                            class="flex flex-1 items-center justify-center text-white py-3 font-bold rounded-bl-lg gap-x-2.5 hover:bg-primary-500 transition-all"
                            :class="{
                                'bg-primary-400': question.data,
                                'bg-gray-400': !question.data,
                            }"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                class="size-5"
                            >
                                <path
                                    d="m5.433 13.917 1.262-3.155A4 4 0 0 1 7.58 9.42l6.92-6.918a2.121 2.121 0 0 1 3 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 0 1-.65-.65Z"
                                />
                                <path
                                    d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0 0 10 3H4.75A2.75 2.75 0 0 0 2 5.75v9.5A2.75 2.75 0 0 0 4.75 18h9.5A2.75 2.75 0 0 0 17 15.25V10a.75.75 0 0 0-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5Z"
                                />
                            </svg>

                            편집
                        </button>
                        <button
                            @click="editSubQuestion1(question)"
                            class="flex flex-1 items-center justify-center text-white py-3 font-bold gap-x-2.5 hover:bg-primary-500 transition-all"
                            :class="{
                                'bg-primary-400': question.sub1_data,
                                'bg-gray-400': !question.sub1_data,
                            }"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                class="size-5"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.25a.75.75 0 0 0-1.5 0v2.5h-2.5a.75.75 0 0 0 0 1.5h2.5v2.5a.75.75 0 0 0 1.5 0v-2.5h2.5a.75.75 0 0 0 0-1.5h-2.5v-2.5Z"
                                    clip-rule="evenodd"
                                />
                            </svg>

                            유사 문제 1
                        </button>
                        <button
                            @click="editSubQuestion2(question)"
                            class="flex flex-1 items-center justify-center text-white py-3 font-bold gap-x-2.5 hover:bg-primary-500 transition-all"
                            :class="{
                                'bg-primary-400': question.sub2_data,
                                'bg-gray-400': !question.sub2_data,
                            }"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                class="size-5"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.25a.75.75 0 0 0-1.5 0v2.5h-2.5a.75.75 0 0 0 0 1.5h2.5v2.5a.75.75 0 0 0 1.5 0v-2.5h2.5a.75.75 0 0 0 0-1.5h-2.5v-2.5Z"
                                    clip-rule="evenodd"
                                />
                            </svg>

                            유사 문제 2
                        </button>
                        <button
                            @click="confirmDeleteQuestion(question)"
                            class="flex flex-1 items-center justify-center bg-danger-400 text-white py-3 font-bold rounded-br-lg gap-x-2.5 hover:bg-danger-500 transition-all"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                class="size-5"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Zm4.34.06a.75.75 0 1 0-1.5-.06l-.3 7.5a.75.75 0 1 0 1.5.06l.3-7.5Z"
                                    clip-rule="evenodd"
                                />
                            </svg>

                            삭제
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div v-else-if="status === 'complete'">
            <div
                class="flex flex-col justify-center items-center gap-y-2 mt-10"
            >
                <svg
                    style="
                        --c-400: var(--primary-400);
                        --c-500: var(--primary-500);
                        --c-600: var(--primary-600);
                    "
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="size-12 text-custom-500"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z"
                    />
                </svg>

                <h1 class="text-xl font-bold">문제 등록이 완료되었습니다.</h1>
                <h2 class="text-base text-gray-600">
                    학습지 메뉴에서 학습지를 등록해보세요!
                </h2>
            </div>
        </div>
    </Transition>
    <Transition
        enter-active-class="transition duration-300 ease-in-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-300 ease-in-out"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="showMarginModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30"
        >
            <div class="bg-white rounded-lg shadow-lg p-6 w-1/2">
                <h2 class="text-xl font-bold mb-1">페이지 여백 설정</h2>
                <h2 class="text-sm text-gray-600 font-medium mb-4">
                    화살표를 움직여 여백을 조정해주세요.
                </h2>
                <div class="">
                    <div class="w-full max-w-[500px] mx-auto relative">
                        <img
                            class="w-full border rounded user-select-none select-none"
                            :src="selectedPages[0]?.url"
                        />
                        <div
                            class="absolute top-0 left-0 rounded border-2 border-dashed border-primary-500 bg-black bg-opacity-10"
                            :style="{
                                top: `${extractionMargin.top}%`,
                                left: `${extractionMargin.left}%`,
                                right: `${extractionMargin.right}%`,
                                bottom: `${extractionMargin.bottom}%`,
                            }"
                        ></div>
                        <div
                            class="text-white bg-primary-500 rounded-full absolute top-1/2 -translate-y-1/2 -translate-x-1/2 cursor-pointer"
                            :style="{
                                left: `${extractionMargin.left}%`,
                            }"
                            @mousedown="startDrag($event, 'left')"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                class="size-6"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <div
                            class="text-white bg-primary-500 rounded-full absolute top-1/2 -translate-y-1/2 translate-x-1/2 cursor-pointer"
                            :style="{
                                right: `${extractionMargin.right}%`,
                            }"
                            @mousedown="startDrag($event, 'right')"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                class="size-6"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <div
                            class="text-white bg-primary-500 rounded-full absolute top-0 -translate-y-1/2 left-1/2 -translate-x-1/2 cursor-pointer"
                            :style="{
                                top: `${extractionMargin.top}%`,
                            }"
                            @mousedown="startDrag($event, 'top')"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                class="size-6"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M9.47 6.47a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 1 1-1.06 1.06L10 8.06l-3.72 3.72a.75.75 0 0 1-1.06-1.06l4.25-4.25Z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <div
                            class="text-white bg-primary-500 rounded-full absolute bottom-0 translate-y-1/2 left-1/2 -translate-x-1/2 cursor-pointer"
                            :style="{
                                bottom: `${extractionMargin.bottom}%`,
                            }"
                            @mousedown="startDrag($event, 'bottom')"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                class="size-6"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end mt-4 gap-x-2">
                    <button
                        @click="showMarginModal = false"
                        class="fi-btn border text-sm px-4 py-2 rounded-lg hover:bg-gray-100"
                    >
                        취소
                    </button>
                    <button
                        style="
                            --c-400: var(--primary-400);
                            --c-500: var(--primary-500);
                            --c-600: var(--primary-600);
                        "
                        :disabled="!selectedPages?.length || extracting"
                        class="fi-btn relative grid-flow-col disabled:opacity-50 items-center justify-center font-semibold outline-none transition-all duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-lg fi-btn-size-lg gap-1.5 px-3.5 py-2.5 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50"
                        @click="extractQuestions"
                    >
                        <svg
                            class="fi-btn-icon transition duration-75 h-5 w-5 text-white"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            aria-hidden="true"
                            data-slot="icon"
                        >
                            <path
                                d="M15.98 1.804a1 1 0 0 0-1.96 0l-.24 1.192a1 1 0 0 1-.784.785l-1.192.238a1 1 0 0 0 0 1.962l1.192.238a1 1 0 0 1 .785.785l.238 1.192a1 1 0 0 0 1.962 0l.238-1.192a1 1 0 0 1 .785-.785l1.192-.238a1 1 0 0 0 0-1.962l-1.192-.238a1 1 0 0 1-.785-.785l-.238-1.192ZM6.949 5.684a1 1 0 0 0-1.898 0l-.683 2.051a1 1 0 0 1-.633.633l-2.051.683a1 1 0 0 0 0 1.898l2.051.684a1 1 0 0 1 .633.632l.683 2.051a1 1 0 0 0 1.898 0l.683-2.051a1 1 0 0 1 .633-.633l2.051-.683a1 1 0 0 0 0-1.898l-2.051-.683a1 1 0 0 1-.633-.633L6.95 5.684ZM13.949 13.684a1 1 0 0 0-1.898 0l-.184.551a1 1 0 0 1-.632.633l-.551.183a1 1 0 0 0 0 1.898l.551.183a1 1 0 0 1 .633.633l.183.551a1 1 0 0 0 1.898 0l.184-.551a1 1 0 0 1 .632-.633l.551-.183a1 1 0 0 0 0-1.898l-.551-.184a1 1 0 0 1-.633-.632l-.183-.551Z"
                            ></path>
                        </svg>
                        <span class="fi-btn-label"> 문제 추출 시작 </span>
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
