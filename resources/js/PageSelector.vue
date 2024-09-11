<script setup>
import { ref } from "vue";
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
// const status = ref("page-select");
const status = ref("extracted");

const selectedPages = ref([]);
const lastSelectedPage = ref(null);
const extractedPages = ref([
    {
        page: {
            number: 15,
            url: "http://localhost/storage/converted-pdfs/01J7FCWCMKMTBCV3305GSNY04K/page_15.jpg",
        },
        data: [
            {
                height: 264,
                width: 418,
                x: 107,
                y: 254,
            },
            {
                height: 151,
                width: 505,
                x: 107,
                y: 778,
            },
            {
                height: 265,
                width: 436,
                x: 107,
                y: 1189,
            },
            {
                height: 135,
                width: 500,
                x: 684,
                y: 254,
            },
            {
                height: 205,
                width: 411,
                x: 684,
                y: 747,
            },
            {
                height: 166,
                width: 426,
                x: 684,
                y: 1159,
            },
        ],
    },
    {
        page: {
            number: 16,
            url: "http://localhost/storage/converted-pdfs/01J7FCWCMKMTBCV3305GSNY04K/page_16.jpg",
        },
        data: [
            {
                height: 114,
                width: 491,
                x: 144,
                y: 236,
            },
            {
                height: 327,
                width: 503,
                x: 142,
                y: 836,
            },
            {
                height: 364,
                width: 506,
                x: 715,
                y: 236,
            },
            {
                height: 114,
                width: 440,
                x: 715,
                y: 852,
            },
            {
                height: 219,
                width: 486,
                x: 715,
                y: 1176,
            },
        ],
    },
]);

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
    for (const selectedPage of selectedPages.value) {
        const _path = `/Users/choeintag/Repositories/math-bank-proto/storage/app/public/converted-pdfs/${id}/page_${selectedPage.number}.jpg`;
        const response = await fetch(
            `http://172.30.1.64:8088/detect_problems?input_path=${_path}`
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
</script>

<template>
    <div v-if="status === 'page-select'" class="flex flex-col">
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
                    {{ selectedPages?.length }} / {{ pages.length }} 페이지 문제
                    추출
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
    <div v-else class="flex flex-col">
        <div
            class="flex flex-col bg-gray-200 rounded border p-4 h-[800px] overflow-y-auto gap-y-4"
        >
            <div
                class="items-center justify-center flex"
                v-for="page in extractedPages"
                :key="page.page.number"
            >
                <PageRenderer :page="page.page" :data="page.data" />
            </div>
        </div>
    </div>
</template>
