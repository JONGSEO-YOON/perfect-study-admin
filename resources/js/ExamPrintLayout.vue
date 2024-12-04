<script setup>
import { ref, onMounted, nextTick, watch } from "vue";
import { defineProps } from "vue";
import html2canvas from "html2canvas";

const props = defineProps({
    wire: {},
    mingleData: {},
});

const { wire, mingleData } = props;
console.log(props);
const scale = ref(props.mingleData.scale);

const selectedIndex = ref(null);
const questions = ref([]);
const marginRights = ref(new Map()); // 각 이미지의 margin-right 값을 저장
const isDragging = ref(false);
const startX = ref(0);
const currentImageId = ref(null);
const MARGIN_BOTTOM = 130;
const CHOICE_ITEM_HEIGHT = 28;

// in mm
const PAGE_WIDTH = 210; // A4 width in mm
const PAGE_PADDING = 40; // Total horizontal padding in mm
const PAGE_CONTENT_HEIGHT = 235; // 297mm - 40mm (padding) in mm, converted to appropriate unit
const COLUMN_WIDTH = (PAGE_WIDTH - PAGE_PADDING) / 2; // Single column width in mm

const globalLayoutMode = ref("default");
const pageLayoutModes = ref(new Map()); // 페이지별 레이아웃 모드를 저장

const manualSplitPoints = ref([]); // 사용자가 지정한 분할점 저장

const imageHeights = ref(new Map());
const pages = ref([]);
const explanationContainer = ref(null);
const explanationPages = ref([]);

const calculateInitialMargin = (question) => {
    if (!question.metadata) {
        return 0;
    }

    const { sourceWidth, width } = question.metadata;

    // 1. 원본에서의 컬럼 너비 계산
    // sourceWidth에서 패딩 비율을 제외하고 2로 나눔
    const paddingRatio = 70 / PAGE_WIDTH; // 40/210
    const originalColumnWidth = (sourceWidth * (1 - paddingRatio)) / 2;

    // 2. 원본에서의 마진 비율 계산
    // (컬럼너비 - 실제너비) / 컬럼너비
    const originalMarginRatio =
        (originalColumnWidth - width) / originalColumnWidth;

    // 3. 현재 페이지에서의 마진 픽셀 계산
    const currentColumnWidth = COLUMN_WIDTH * 3.779527559; // mm를 px로 변환
    const currentMargin = currentColumnWidth * originalMarginRatio;

    return Math.max(currentMargin, 0);
};

const calculateContentHeight = (content) => {
    // 임시 측정용 div 생성
    const tempDiv = document.createElement("div");

    // 실제 렌더링될 때와 같은 스타일 적용
    tempDiv.style.position = "absolute";
    tempDiv.style.visibility = "hidden";
    tempDiv.style.width = `${COLUMN_WIDTH * 3.779527559}px`; // 실제 컬럼 너비와 동일하게

    // 컨텐츠 주입
    tempDiv.innerHTML = content;

    // DOM에 임시로 추가하여 높이 측정
    document.body.appendChild(tempDiv);
    const height = tempDiv.offsetHeight;

    // 임시 요소 제거
    document.body.removeChild(tempDiv);

    return height;
};

const calculateImageHeight = (img, question) => {
    if (question.question_display_type === "content") {
        return calculateContentHeight(question.content);
    }

    return new Promise((resolve) => {
        const image = new Image();
        image.onload = () => {
            const aspectRatio = image.width / image.height;
            // Column width calculation considering marginRight
            const columnWidth = (210 - 40) / 2; // Base column width in mm
            const columnWidthPx = columnWidth * 3.779527559; // Convert to pixels

            const imageId = question.id;
            const marginRight = marginRights.value.get(imageId) || 0;
            // Adjust available width by subtracting marginRight
            const availableWidth = columnWidthPx - marginRight;
            const height = availableWidth / aspectRatio;

            resolve(height);
        };
        image.src = img;
    });
};

// 페이지의 레이아웃 모드를 가져오는 함수
const getPageLayoutMode = (pageIndex) => {
    return pageLayoutModes.value.get(pageIndex) || globalLayoutMode.value;
};

// 페이지의 레이아웃 모드를 설정하는 함수
const setPageLayoutMode = (pageIndex, mode) => {
    pageLayoutModes.value.set(pageIndex, mode);
    calculatePages(); // 레이아웃 변경 시 페이지 재계산
};

const calculatePages = async () => {
    await Promise.all(
        questions.value.map(async (question) => {
            const img = "/storage/" + question.image_path;
            const height = await calculateImageHeight(img, question);
            imageHeights.value.set(img, height);
        })
    );

    const result = [];
    const maxColumnHeight = PAGE_CONTENT_HEIGHT * 3.779527559;

    let currentQuestions = [...questions.value];

    while (currentQuestions.length > 0) {
        let pageSize;
        let leftCount, rightCount;
        const currentPageIndex = result.length;
        const currentLayoutMode = getPageLayoutMode(currentPageIndex);
        switch (currentLayoutMode) {
            case "2Items":
                pageSize = 2;
                leftCount = 1;
                rightCount = 1;
                break;
            case "4Items":
                pageSize = 4;
                leftCount = 2;
                rightCount = 2;
                break;
            case "6Items":
                pageSize = 6;
                leftCount = 3;
                rightCount = 3;
                break;
            case "3-1Items":
                pageSize = 3;
                leftCount = 1;
                rightCount = 2;
                break;
            case "3-2Items":
                pageSize = 3;
                leftCount = 2;
                rightCount = 1;
                break;
            default:
                pageSize = Infinity;
                leftCount = Infinity;
                rightCount = Infinity;
        }

        if (currentLayoutMode === "default") {
            // 기존 로직 유지
            let currentPage = { left: [], right: [] };
            let leftColumnHeight = 0;
            let rightColumnHeight = 0;

            for (let i = 0; i < currentQuestions.length; i++) {
                const currentQuestion = currentQuestions[i];
                const currentImage = "/storage/" + currentQuestion.image_path;
                let choicesHeight = 0;
                if (
                    currentQuestion.choices_display_type === "seperate" &&
                    currentQuestion.answer_type === "multiple_choice"
                ) {
                    choicesHeight =
                        CHOICE_ITEM_HEIGHT *
                        Math.ceil(currentQuestion.choices.length / 3);
                }

                const imageHeight =
                    imageHeights.value.get(currentImage) +
                    MARGIN_BOTTOM +
                    choicesHeight;

                if (leftColumnHeight + imageHeight <= maxColumnHeight) {
                    currentPage.left.push(currentQuestion);
                    leftColumnHeight += imageHeight;
                } else if (rightColumnHeight + imageHeight <= maxColumnHeight) {
                    currentPage.right.push(currentQuestion);
                    rightColumnHeight += imageHeight;
                } else {
                    result.push(currentPage);
                    currentQuestions = currentQuestions.slice(i);
                    break;
                }

                if (i === currentQuestions.length - 1) {
                    result.push(currentPage);
                    currentQuestions = [];
                }
            }
        } else {
            // Items 모드일 때는 지정된 개수로 계산
            const pageQuestions = currentQuestions.slice(0, pageSize);

            result.push({
                left: pageQuestions.slice(0, leftCount),
                right: pageQuestions.slice(leftCount, leftCount + rightCount),
            });

            currentQuestions = currentQuestions.slice(pageSize);
        }
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

const handleExplanationClick = (e, pageIndex, side) => {
    const container = e.currentTarget;
    const rect = container.getBoundingClientRect();
    const clickY = e.clientY - rect.top;

    // const containerParent = container.parentElement;
    // const parentRect = containerParent.getBoundingClientRect();
    // const columnHeight = parentRect.height;
    const columnHeight = rect.height;

    // 마지막 페이지가 아니면 항상 columnHeight 사용
    const isLastColumn =
        pageIndex === explanationPages.value.length - 1 &&
        (side === "right" || !explanationPages.value[pageIndex].right);

    // ignore last column
    if (isLastColumn) {
        return;
    }
    const baseHeight = isLastColumn ? rect.height : columnHeight;

    const yPercent = (clickY / baseHeight) * 100;

    // 이전 분할점들 삭제
    manualSplitPoints.value = manualSplitPoints.value.filter((point) => {
        if (point.pageIndex < pageIndex) {
            return true;
        }
        if (point.pageIndex > pageIndex) {
            return false;
        }
        if (point.pageIndex === pageIndex) {
            if (side === "left") {
                return false;
            } else {
                return point.side === "left";
            }
        }
        return false;
    });

    // 새로운 분할점 추가
    if (yPercent <= 100) {
        manualSplitPoints.value.push({
            pageIndex,
            side,
            yPercent,
        });
        calculateExplanationPages();
    }
};

const handleDragStart = (e, imageId) => {
    isDragging.value = true;
    startX.value = e.clientX;
    currentImageId.value = imageId;
    if (!marginRights.value.has(imageId)) {
        marginRights.value.set(imageId, 0);
    }

    // 드래그 중에 텍스트 선택 방지
    document.body.style.userSelect = "none";
};

const handleDrag = (e) => {
    if (!isDragging.value || !currentImageId.value) return;

    const deltaX = e.clientX - startX.value;
    let newMargin =
        (marginRights.value.get(currentImageId.value) || 0) - deltaX;

    // margin-right 값을 0 이하로 제한
    newMargin = Math.min(Math.max(newMargin, 0), 150);

    marginRights.value.set(currentImageId.value, newMargin);
    startX.value = e.clientX;
};

const handleDragEnd = () => {
    isDragging.value = false;
    currentImageId.value = null;
    document.body.style.userSelect = "";
};

const calculateExplanationPages = async () => {
    await nextTick();
    if (!explanationContainer.value) return;

    document.querySelector(
        ".temp-explanation-container"
    ).style.width = `304.77px`;

    // document.querySelector(".temp-explanation-container").style.width = `${
    //     COLUMN_WIDTH * 3.779527559
    // }px`;

    const canvas = await html2canvas(explanationContainer.value, {
        // dpi: 2000,
        scale: 1,
    });
    // const columnWidth = COLUMN_WIDTH * 3.779527559;
    const columnWidth = explanationContainer.value.offsetWidth;
    // const columnHeight = PAGE_CONTENT_HEIGHT * 3.779527559;
    const columnHeight = 885.36;

    // const columnWidth = COLUMN_WIDTH * 3.779527559;
    // const columnHeight = PAGE_CONTENT_HEIGHT * 3.779527559;
    const pages = [];

    let currentY = 0;
    let remainingHeight = canvas.height;

    while (remainingHeight > 0) {
        const pageIndex = pages.length;
        const leftSplitPoint = manualSplitPoints.value.find(
            (point) => point.pageIndex === pageIndex && point.side === "left"
        );
        const rightSplitPoint = manualSplitPoints.value.find(
            (point) => point.pageIndex === pageIndex && point.side === "right"
        );

        // 왼쪽 컬럼 처리
        const leftHeight = leftSplitPoint
            ? (leftSplitPoint.yPercent / 100) *
              Math.min(columnHeight, remainingHeight)
            : Math.min(columnHeight, remainingHeight);

        const leftColumn = createColumnImage(
            canvas,
            columnWidth,
            leftHeight,
            currentY
        );

        // 왼쪽 컬럼에서 처리된 높이만큼 갱신
        currentY += leftHeight;
        remainingHeight = canvas.height - currentY;

        // 오른쪽 컬럼 처리
        let rightColumn = null;
        if (remainingHeight > 0) {
            const rightHeight = rightSplitPoint
                ? (rightSplitPoint.yPercent / 100) *
                  Math.min(columnHeight, remainingHeight)
                : Math.min(columnHeight, remainingHeight);

            rightColumn = createColumnImage(
                canvas,
                columnWidth,
                rightHeight,
                currentY
            );

            // 오른쪽 컬럼에서 처리된 높이만큼 갱신
            currentY += rightHeight;
            remainingHeight = canvas.height - currentY;
        }

        pages.push({
            left: leftColumn,
            right: rightColumn,
        });
    }

    explanationPages.value = pages;
};

// 컬럼 이미지 생성 함수
const createColumnImage = (sourceCanvas, width, height, startY) => {
    const columnCanvas = document.createElement("canvas");
    columnCanvas.width = width;

    // 남은 높이가 columnHeight보다 작은 경우 처리
    const remainingHeight = sourceCanvas.height - startY;
    columnCanvas.height = Math.min(height, remainingHeight);

    const ctx = columnCanvas.getContext("2d");
    ctx.drawImage(
        sourceCanvas,
        0, // sourceX
        startY, // sourceY
        width, // sourceWidth
        Math.min(height, remainingHeight), // sourceHeight
        0, // destX
        0, // destY
        width, // destWidth
        Math.min(height, remainingHeight) // destHeight
    );

    return columnCanvas.toDataURL();
};

const onPageSelected = (data) => {
    //window postmessage
    window.parent.postMessage(
        {
            type: "onPageSelected",
            data: data ? data : null,
        },
        "*"
    );
};

// watch selectedIndex
watch(selectedIndex, () => {
    if (selectedIndex.value === null) {
        onPageSelected({
            pageIndex: -1,
            layoutMode: globalLayoutMode.value,
        });
        return;
    }
    // get layoutmode
    const layoutMode = getPageLayoutMode(selectedIndex.value);
    onPageSelected({
        pageIndex: selectedIndex.value,
        layoutMode,
    });
});

onMounted(() => {
    window.addEventListener("message", async (event) => {
        // 보안을 위해 출처 확인이 필요할 수 있습니다
        if (event.data.type === "setQuestions") {
            questions.value = event.data.questions;
            await calculatePages();
            pages.value.forEach((page, pageIndex) => {
                page.left.forEach((question, idx) => {
                    const margin = calculateInitialMargin(question);
                    marginRights.value.set(question.id, margin);
                });

                page.right.forEach((question, idx) => {
                    const margin = calculateInitialMargin(question);
                    marginRights.value.set(question.id, margin);
                });
            });
            console.log(marginRights.value);
            await calculatePages();
            calculateExplanationPages();
        } else if (event.data.type === "onSplitChanged") {
            const split = event.data.data.layoutMode;
            const pageIndex = event.data.data.pageIndex;
            console.log("split", split, pageIndex);
            if (pageIndex === -1) {
                globalLayoutMode.value = split;
            } else {
                setPageLayoutMode(pageIndex, split);
            }
            //globalLayoutMode.value = split;
            //calculatePages();
            calculatePages();
        }
    });
    document.addEventListener("mousemove", handleDrag);
    document.addEventListener("mouseup", handleDragEnd);
});
</script>

<template>
    <div class="exam-container">
        <div
            v-for="(page, pageIndex) in pages"
            :key="pageIndex"
            @click="
                selectedIndex = selectedIndex === pageIndex ? null : pageIndex
            "
            class="page flex flex-col hover:scale-105 cursor-pointer transition-all hover:brightness-90 relative"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
                v-if="selectedIndex === pageIndex"
                class="size-16 absolute top-4 right-4 text-blue-500"
            >
                <path
                    fill-rule="evenodd"
                    d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z"
                    clip-rule="evenodd"
                />
            </svg>

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
                        class="font-bold absolute"
                        :class="pageIndex % 2 === 0 ? 'right-0' : 'left-0'"
                    >
                        {{ pageIndex + 1 }}
                    </div>
                    수학 영역(가형)
                </div>
            </template>

            <div
                class="flex-1 border-black flex flex-row overflow-hidden h-0 min-h-0"
                :class="{
                    'border-t-[2px] border-black mt-4': pageIndex === 0,
                }"
            >
                <div class="flex-1 pt-5 pr-4 question-columns flex flex-col">
                    <div
                        class="flex flex-row gap-x-1"
                        v-for="(question, imgIndex) in page.left"
                        :class="{
                            'flex-1 h-0 overflow-hidden':
                                getPageLayoutMode(pageIndex).includes('Items'),
                        }"
                        :key="`left-${imgIndex}`"
                    >
                        <h1 class="">
                            {{ getQuestionNumber(pageIndex, true, imgIndex) }}.
                        </h1>
                        <div
                            class="flex-1 w-0 hover:border hover:border-dashed border-gray-800 pt-1"
                            :style="{
                                'margin-bottom': `${MARGIN_BOTTOM}px`,
                                'margin-right': `${
                                    marginRights.get(question.id) || 0
                                }px`,
                            }"
                            @mousedown="(e) => handleDragStart(e, question.id)"
                        >
                            <!-- question_display_type에 따른 조건부 렌더링 -->
                            <template
                                v-if="
                                    question.question_display_type === 'image'
                                "
                            >
                                <img
                                    :src="'/storage/' + question.image_path"
                                    :alt="`Question ${getQuestionNumber(
                                        pageIndex,
                                        true,
                                        imgIndex
                                    )}`"
                                    draggable="false"
                                    class="w-full select-none"
                                />
                            </template>
                            <template
                                v-else-if="
                                    question.question_display_type === 'content'
                                "
                            >
                                <div
                                    v-html="question.content"
                                    class="content-display"
                                ></div>
                            </template>
                            <template
                                v-if="
                                    question.answer_type ===
                                        'multiple_choice' &&
                                    question.choices_display_type === 'seperate'
                                "
                            >
                                <div class="gap-x-1.5 grid grid-cols-3 mt-1.5">
                                    <div
                                        v-for="(choice, i) in question.choices"
                                        class="flex flex-row gap-x-1 items-center h-7"
                                    >
                                        <div>
                                            <span v-if="i === 0">①</span>
                                            <span v-else-if="i === 1">②</span>
                                            <span v-else-if="i === 2">③</span>
                                            <span v-else-if="i === 3">④</span>
                                            <span v-else-if="i === 4">⑤</span>
                                            <span v-else-if="i === 5">⑥</span>
                                        </div>
                                        <div
                                            v-if="
                                                choice.display_type ===
                                                'content'
                                            "
                                            v-html="choice.content"
                                            class="flex-1"
                                        ></div>
                                        <div
                                            class="flex-1"
                                            v-else-if="
                                                choice.display_type === 'image'
                                            "
                                        >
                                            <img
                                                :src="
                                                    '/storage/' +
                                                    choice.image_path
                                                "
                                                :alt="`Choice ${i + 1}`"
                                                class="w-full"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                <div
                    class="bg-black"
                    :class="{
                        'w-[2px]': pageIndex === 0,
                        'w-px mt-10 mb-4': pageIndex !== 0,
                    }"
                ></div>
                <div class="flex-1 pt-5 pl-4 question-columns flex flex-col">
                    <div
                        class="flex flex-row gap-x-1"
                        :class="{
                            'flex-1 h-0 overflow-hidden':
                                getPageLayoutMode(pageIndex).includes('Items'),
                        }"
                        v-for="(question, imgIndex) in page.right"
                        :key="`right-${imgIndex}`"
                    >
                        <h1 class="">
                            {{ getQuestionNumber(pageIndex, false, imgIndex) }}.
                        </h1>
                        <div
                            class="flex-1 w-0 hover:border hover:border-dashed border-gray-800 pt-1"
                            :style="{
                                'margin-bottom': `${MARGIN_BOTTOM}px`,
                                'margin-right': `${
                                    marginRights.get(question.id) || 0
                                }px`,
                            }"
                            @mousedown="(e) => handleDragStart(e, question.id)"
                        >
                            <template
                                v-if="
                                    question.question_display_type === 'image'
                                "
                            >
                                <img
                                    :src="'/storage/' + question.image_path"
                                    :alt="`Question ${getQuestionNumber(
                                        pageIndex,
                                        true,
                                        imgIndex
                                    )}`"
                                    draggable="false"
                                    class="w-full select-none"
                                />
                            </template>
                            <template
                                v-else-if="
                                    question.question_display_type === 'content'
                                "
                            >
                                <div
                                    v-html="question.content"
                                    class="content-display"
                                ></div>
                            </template>
                            <template
                                v-if="
                                    question.answer_type ===
                                        'multiple_choice' &&
                                    question.choices_display_type === 'seperate'
                                "
                            >
                                <div class="gap-x-1.5 grid grid-cols-3 mt-1.5">
                                    <div
                                        v-for="(choice, i) in question.choices"
                                        class="flex flex-row gap-x-1 items-center h-7"
                                    >
                                        <div>
                                            <span v-if="i === 0">①</span>
                                            <span v-else-if="i === 1">②</span>
                                            <span v-else-if="i === 2">③</span>
                                            <span v-else-if="i === 3">④</span>
                                            <span v-else-if="i === 4">⑤</span>
                                            <span v-else-if="i === 5">⑥</span>
                                        </div>
                                        <div
                                            v-if="
                                                choice.display_type ===
                                                'content'
                                            "
                                            v-html="choice.content"
                                            class="flex-1"
                                        ></div>
                                        <div
                                            class="flex-1"
                                            v-else-if="
                                                choice.display_type === 'image'
                                            "
                                        >
                                            <img
                                                :src="
                                                    '/storage/' +
                                                    choice.image_path
                                                "
                                                :alt="`Choice ${i + 1}`"
                                                class="w-full"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-center mt-1">
                <div
                    class="border border-black diag flex flex-row w-[80px] leading-6 justify-between font-bold px-1.5 text-lg"
                >
                    <div class="mb-2">{{ pageIndex + 1 }}</div>
                    <div class="mt-2">{{ pages.length }}</div>
                </div>
            </div>
        </div>
        <div
            class="flex items-center justify-center font-semibold text-gray-700 text-xl flex-row gap-x-4"
        >
            <div class="flex-1 h-px bg-gray-500"></div>
            해설 영역 입니다.
            <div class="flex-1 h-px bg-gray-500"></div>
        </div>
        <div
            class="flex items-center justify-center text-red-400 text-sm flex-row gap-x-4"
        >
            (클릭 시, 페이지가 분할됩니다.)
        </div>
        <div
            v-for="(page, pageIndex) in explanationPages"
            :key="'explanation-' + pageIndex"
            class="page explanation flex flex-col"
        >
            <!-- 페이지 헤더 -->
            <div
                class="pb-1 flex items-center relative justify-center text-3xl tracking-tighter font-semibold mt-1.5 border-b-[2px] border-black"
            >
                <div
                    class="font-bold absolute"
                    :class="
                        (pages.length + pageIndex) % 2 === 0
                            ? 'right-0'
                            : 'left-0'
                    "
                >
                    {{ pages.length + pageIndex + 1 }}
                </div>
                수학 영역(가형)
            </div>

            <!-- 2단 레이아웃의 해설 내용 -->
            <div
                class="flex-1 border-black flex flex-row overflow-hidden h-0 min-h-0"
            >
                <!-- 왼쪽 컬럼 -->
                <div
                    class="flex-1 pr-4 relative"
                    @click="(e) => handleExplanationClick(e, pageIndex, 'left')"
                >
                    <!-- relative 추가 -->
                    <img
                        :src="page.left"
                        class="w-full h-full object-contain object-left-top"
                        v-if="page.left"
                    />
                    <!-- 왼쪽 컬럼 분할점 -->
                    <div
                        v-for="point in manualSplitPoints.filter(
                            (p) =>
                                p.pageIndex === pageIndex && p.side === 'left'
                        )"
                        :key="'left-split-' + point.yPercent"
                        class="absolute left-0 right-2 h-[2px] bg-red-500"
                        :style="{
                            top: `${point.yPercent}%`,
                        }"
                    ></div>
                </div>

                <!-- 중앙 구분선 -->
                <div class="bg-black w-px mt-10 mb-4"></div>

                <!-- 오른쪽 컬럼 -->
                <div
                    class="flex-1 pl-4 relative"
                    @click="
                        (e) => handleExplanationClick(e, pageIndex, 'right')
                    "
                >
                    <!-- relative 추가 -->
                    <img
                        :src="page.right"
                        class="w-full h-full object-contain object-left-top"
                        v-if="page.right"
                    />
                    <!-- 오른쪽 컬럼 분할점 -->
                    <div
                        v-for="point in manualSplitPoints.filter(
                            (p) =>
                                p.pageIndex === pageIndex && p.side === 'right'
                        )"
                        :key="'right-split-' + point.yPercent"
                        class="absolute h-[2px] bg-red-500 right-0 left-2"
                        :style="{
                            top: `${point.yPercent}%`,
                        }"
                    ></div>
                </div>
            </div>

            <!-- 페이지 번호 -->
            <div class="flex items-center justify-center mt-1">
                <div
                    class="border border-black diag flex flex-row w-[80px] leading-6 justify-between font-bold px-1.5 text-lg"
                >
                    <div class="mb-2">{{ pages.length + pageIndex + 1 }}</div>
                    <div class="mt-2">
                        {{ explanationPages.length + pages.length }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 해설 렌더링을 위한 임시 컨테이너 -->
    <div ref="explanationContainer" class="temp-explanation-container pb-4">
        <div class="flex flex-col gap-y-6">
            <div
                v-for="(question, index) in questions"
                :key="'exp-' + index"
                class="flex flex-row gap-x-4"
            >
                <!-- scale: tracking-[7px]  -->
                <h3>{{ index + 1 }})</h3>
                <div class="break-words break-all">
                    <!-- scale: leading-8 -->
                    정답 {{ question.answer }}
                    {{ question.question_type.name }}-레벨{{ question.level }}
                </div>
            </div>
            <div class="w-full h-px bg-black mt-10 mb-4"></div>
            <div v-for="(question, index) in questions" :key="'exp-' + index">
                <!-- scale: tracking-[7px]  -->
                <div class="flex flex-row gap-x-4">
                    <h3>{{ index + 1 }})</h3>
                    <div class="break-words break-all">
                        <!-- scale: leading-8 -->
                        정답 {{ question.answer }}
                        {{ question.question_type.name }}-레벨{{
                            question.level
                        }}
                    </div>
                </div>
                <div class="py-6">
                    <template
                        v-if="question.explanation_display_type === 'image'"
                    >
                        <img
                            class="w-full"
                            :src="'/storage/' + question.explanation_image_path"
                        />
                    </template>
                    <template
                        v-else-if="
                            question.explanation_display_type === 'content'
                        "
                    >
                        <div v-html="question.explanation" class=""></div>
                    </template>
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
    height: 297mm;
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
.temp-explanation-container {
    position: fixed;
    left: -9999px;
    /* width: v-bind("`${(COLUMN_WIDTH * 3.779527559)}px`"); scale: 180 */
    /* width: 304px; */
}
</style>
