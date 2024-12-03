<script setup>
import { ref, onMounted, computed } from "vue";

const props = defineProps({
    wire: {},
    mingleData: {},
});

const questions = ref([]);

const MARGIN_BOTTOM = 128;

const PAGE_CONTENT_HEIGHT = 215; // 297mm - 40mm (padding) in mm, converted to appropriate unit

const imageHeights = ref(new Map());
const pages = ref([]);

const calculateImageHeight = (img) => {
    return new Promise((resolve) => {
        const image = new Image();
        image.onload = () => {
            const aspectRatio = image.width / image.height;
            // Assuming the column width is 50% of the page width (210mm - 40mm padding) / 2
            const columnWidth = (210 - 40) / 2;
            const height = (columnWidth / aspectRatio) * 3.779527559; // Convert mm to px
            resolve(height);
        };
        image.src = img;
    });
};

const calculatePages = async () => {
    await Promise.all(
        questions.value.map(async (question) => {
            const img = "/storage/" + question.image_path;
            const height = await calculateImageHeight(img);
            imageHeights.value.set(img, height);
        })
    );

    const result = [];
    let currentPage = { left: [], right: [] };
    let leftColumnHeight = 0;
    let rightColumnHeight = 0;
    const maxColumnHeight = PAGE_CONTENT_HEIGHT * 3.779527559; // Convert mm to px

    for (let i = 0; i < questions.value.length; i++) {
        const currentImage = "/storage/" + questions.value[i].image_path;
        const imageHeight =
            imageHeights.value.get(currentImage) + MARGIN_BOTTOM;

        // Try to add to left column first
        if (leftColumnHeight + imageHeight <= maxColumnHeight) {
            currentPage.left.push(currentImage);
            leftColumnHeight += imageHeight;
        }
        // Then try right column
        else if (rightColumnHeight + imageHeight <= maxColumnHeight) {
            currentPage.right.push(currentImage);
            rightColumnHeight += imageHeight;
        }
        // If both columns are full, create new page
        else {
            result.push(currentPage);
            currentPage = { left: [currentImage], right: [] };
            leftColumnHeight = imageHeight;
            rightColumnHeight = 0;
        }
    }

    // Add the last page if it has any content
    if (currentPage.left.length > 0 || currentPage.right.length > 0) {
        result.push(currentPage);
    }

    pages.value = result;
};

const getQuestionNumber = (pageIndex, isLeft, imgIndex) => {
    let number = 1;
    for (let i = 0; i < pageIndex; i++) {
        number += pages.value[i].left.length + pages.value[i].right.length;
    }
    if (isLeft) {
        return number + imgIndex;
    } else {
        return number + pages.value[pageIndex].left.length + imgIndex;
    }
};

const handleImageError = (event) => {
    console.error("이미지 로드 실패:", event.target.src);
    event.target.src = "/path/to/fallback-image.jpg";
};

onMounted(() => {
    calculatePages();
    //add window event listener for "setQuestions" event
    window.addEventListener("message", (event) => {
        // 보안을 위해 출처 확인이 필요할 수 있습니다
        if (event.data.type === "setQuestions") {
            console.log(event.data.questions);
            questions.value = event.data.questions;
            // filter question where image_path is not null
            questions.value = questions.value.filter(
                (question) => question.image_path
            );
            calculatePages();
        }
    });
});
</script>

<template>
    <div class="exam-container">
        <div
            v-for="(page, pageIndex) in pages"
            :key="pageIndex"
            class="page flex flex-col"
        >
            <!-- 첫 페이지일 경우 헤더 표시 -->
            <template v-if="pageIndex === 0">
                <div
                    class="flex items-center justify-center text-2xl tracking-tighter"
                >
                    2023년 대학수학능력시험 실전 모의고사 22회
                </div>
                <div
                    class="flex items-center justify-center text-4xl tracking-tighter font-semibold mt-1.5"
                >
                    수학 영역 (미적분)
                </div>
                <div class="flex flex-row items-center justify-between mt-2">
                    <div
                        class="border border-black text-lg px-3 rounded font-bold"
                    >
                        2교시
                    </div>
                    <div class="flex flex-row gap-x-6">
                        <div class="flex flex-row border border-black">
                            <div class="border-r border-black py-0.5 px-2">
                                성 명
                            </div>
                            <div class="w-[80px]"></div>
                        </div>
                        <div class="flex flex-row border border-black">
                            <div class="border-r border-black py-0.5 px-2">
                                수험번호
                            </div>
                            <div class="w-[180px]"></div>
                        </div>
                    </div>
                    <div class="border border-black px-4 rounded-full">
                        홀수형
                    </div>
                </div>
            </template>
            <!-- 다른 페이지일 경우 간단한 헤더 -->
            <template v-else>
                <div
                    class="pb-1 flex items-center relative justify-center text-3xl tracking-tighter font-semibold mt-1.5 border-b-[2px] border-black"
                >
                    <div
                        class="font-bold"
                        :class="pageIndex % 2 === 0 ? 'left-0' : 'right-0'"
                        absolute
                    >
                        {{ pageIndex + 1 }}
                    </div>
                    수학 영역(가형)
                </div>
            </template>

            <div
                class="flex-1 border-black flex flex-row"
                :class="{
                    'border-t-[2px] border-black mt-4': pageIndex === 0,
                }"
            >
                <div class="flex-1 pt-5 pr-4 question-columns">
                    <div
                        class="flex flex-row gap-x-1"
                        v-for="(img, imgIndex) in page.left"
                        :key="`left-${imgIndex}`"
                    >
                        <h1 class="mt-2">
                            {{ getQuestionNumber(pageIndex, true, imgIndex) }}.
                        </h1>
                        <img
                            :src="img"
                            :alt="`Question ${getQuestionNumber(
                                pageIndex,
                                true,
                                imgIndex
                            )}`"
                            class="flex-1 w-0"
                            :style="{
                                'margin-bottom': `${MARGIN_BOTTOM}px`,
                            }"
                        />
                    </div>
                </div>
                <div
                    class="bg-black"
                    :class="{
                        'w-[2px]': pageIndex === 0,
                        'w-px mt-10 mb-4': pageIndex !== 0,
                    }"
                ></div>
                <div class="flex-1 pt-5 pl-4 question-columns">
                    <div
                        class="flex flex-row gap-x-1"
                        v-for="(img, imgIndex) in page.right"
                        :key="`right-${imgIndex}`"
                    >
                        <h1 class="mt-2">
                            {{ getQuestionNumber(pageIndex, true, imgIndex) }}.
                        </h1>
                        <img
                            :src="img"
                            :alt="`Question ${getQuestionNumber(
                                pageIndex,
                                false,
                                imgIndex
                            )}`"
                            class="flex-1 w-0"
                            :style="{
                                'margin-bottom': `${MARGIN_BOTTOM}px`,
                            }"
                        />
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-center mt-1">
                <div
                    class="border border-black diag flex flex-row w-[80px] leading-6 justify-between font-bold px-1.5 text-lg"
                >
                    <div class="mb-2">{{ pageIndex + 1 }}</div>
                    <div class="mt-2">12</div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.diag {
    background: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' version='1.1' preserveAspectRatio='none' viewBox='0 0 100 100'><path d='M0 99 L99 0 L100 1 L1 100' fill='black' /></svg>");
    background-repeat: no-repeat;
    -webkit-print-color-adjust: exact;
    background-position: center center;
    background-size: 100% 100%, auto;
}

.page {
    width: 210mm;
    min-height: 297mm;
    padding: 20mm;
    margin: 10mm auto;
    background: white;
    box-sizing: border-box;
    position: relative;
    page-break-after: always;
}

.page:last-child {
    page-break-after: avoid;
}

@media print {
    .page {
        margin: 0;
        box-shadow: none;
    }
}

@media screen {
    .page {
        box-shadow: 0 0 10mm rgba(0, 0, 0, 0.1);
    }
}
</style>
