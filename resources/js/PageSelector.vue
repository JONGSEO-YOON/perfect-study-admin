<script setup>
import { ref, computed } from "vue";
import { defineProps } from "vue";
import PageRenderer from "./PageRenderer.vue";

const props = defineProps({
    wire: {},
    mingleData: {},
});

const { wire, mingleData } = props;
const message = mingleData.message;
const pages = mingleData.pages;
const id = mingleData.id;

const extracting = ref(false);
const completing = ref(false);
const status = ref("page-select");
// const status = ref("extracted");

const selectedPages = ref([]);
const lastSelectedPage = ref(null);
const extractedPages = ref([]);

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
    extracting.value = true;
    extractedPages.value = [];
    for (const selectedPage of selectedPages.value) {
        const _path = `/Users/choeintag/Repositories/math-bank-proto/storage/app/public/converted-pdfs/${id}/page_${selectedPage.number}.jpg`;
        const response = await fetch(
            `http://172.30.1.59:8088/detect_problems?input_path=${_path}`
        );

        const data = await response.json();
        extractedPages.value.push({
            page: selectedPage,
            data,
        });
    }
    extracting.value = false;
    console.log(extractedPages.value);
    status.value = "extracted";
};

const saveQuestions = async () => {
    completing.value = true;
    setTimeout(() => {
        completing.value = false;
        status.value = "complete";
    }, 1000);
};

const onQuestionClicked = async (rectData) => {
    wire.onQuestionClicked(rectData);
};

const extractedQuestions = computed(() => {
    return extractedPages.value.reduce((acc, page) => {
        return acc.concat(page.data);
    }, []);
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
        <li class="flex items-center">
            <span
                class="flex items-center justify-center w-5 h-5 me-2 text-xs border border-gray-600 rounded-full shrink-0 dark:border-custom-500"
            >
                1
            </span>
            교재 업로드<span class="hidden sm:inline-flex sm:ms-2">Info</span>
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
                'text-custom-600 dark:text-custom-500': status === 'extracted',
            }"
        >
            <span
                class="flex items-center justify-center w-5 h-5 me-2 text-xs border border-gray-500 rounded-full shrink-0 dark:border-gray-400"
                :class="{
                    'border-custom-500': status === 'extracted',
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
                    'border-custom-500': status === 'complete',
                }"
            >
                4
            </span>
            등록 완료
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
            <div class="flex justify-end">
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
                    <span class="fi-btn-label" v-if="!extracting">
                        {{ selectedPages?.length }} / {{ pages.length }} 페이지
                        문제 추출
                    </span>
                    <span class="fi-btn-label" v-else> 문제 추출중... </span>
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
                    :disabled="completing"
                    style="
                        --c-400: var(--primary-400);
                        --c-500: var(--primary-500);
                        --c-600: var(--primary-600);
                    "
                    class="fi-btn relative grid-flow-col disabled:opacity-50 items-center justify-center font-semibold outline-none transition-all duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-lg fi-btn-size-lg gap-1.5 px-3.5 py-2.5 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50"
                    @click="saveQuestions"
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
                        {{ extractedQuestions?.length }} 문제 등록
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
                        @question-clicked="onQuestionClicked"
                        :page="page.page"
                        :data="page.data"
                    />
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
</template>
