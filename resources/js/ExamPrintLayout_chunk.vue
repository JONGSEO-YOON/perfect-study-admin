<script setup>
import { ref, onMounted, nextTick, watch } from "vue";
import { defineProps } from "vue";
import html2canvas from "html2canvas";

const props = defineProps({
  wire: {},
  mingleData: {},
});

const { wire, mingleData } = props;
const readonly = ref(props.mingleData.readonly);

const templateMode = ref("default");
const color = ref("#0ea5e9");

const selectedIndex = ref(null);
const subTitle = ref("2023년 대학수학능력시험 실전 모의고사 22회");
const title = ref("수학 영역(미적분)");
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
const PAGE_CONTENT_HEIGHT = 225; // 297mm - 40mm (padding) in mm, converted to appropriate unit
const COLUMN_WIDTH = (PAGE_WIDTH - PAGE_PADDING) / 2; // Single column width in mm

const globalLayoutMode = ref(props.mingleData.layoutMode ?? "default");
// const globalLayoutMode = ref("4Items");
const pageLayoutModes = ref(new Map()); // 페이지별 레이아웃 모드를 저장

const manualSplitPoints = ref([]); // 사용자가 지정한 분할점 저장

const imageHeights = ref(new Map());
const pages = ref([]);
const explanationContainer = ref(null);
const explanationPages = ref([]);

const startingNumber = ref(1);

const customLogo = ref("");
const grade = ref("중1");

// 청크 처리 관련 변수 추가
const CHUNK_SIZE = 100; // 한 번에 처리할 문제 수
const explanationChunks = ref([]);
const currentChunkIndex = ref(0);
const isProcessingChunks = ref(false);

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
  const originalMarginRatio = (originalColumnWidth - width) / originalColumnWidth;

  // 3. 현재 페이지에서의 마진 픽셀 계산
  const currentColumnWidth = COLUMN_WIDTH * 3.779527559; // mm를 px로 변환
  const currentMargin = currentColumnWidth * originalMarginRatio;

  return Math.max(currentMargin, 0);
};

const calculateContentHeight = async (content) => {
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

  //give time to render
  await new Promise((resolve) => setTimeout(resolve, 200));
  const height = tempDiv.offsetHeight;

  // 임시 요소 제거
  document.body.removeChild(tempDiv);
  return Math.min(height, PAGE_CONTENT_HEIGHT * 3.779527559 - 150);
  // return 600;
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

const getPageLayoutMode = (pageIndex) => {
  return pageLayoutModes.value.get(pageIndex) || globalLayoutMode.value;
};

const getPrintLayoutData = () => {
  return {
    globalLayoutMode: globalLayoutMode.value,
    pageLayoutModes: Array.from(pageLayoutModes.value).map(([key, value]) => ({
      pageNumber: key,
      mode: value,
    })),
    manualSplitPoints: manualSplitPoints.value,
    marginRights: Array.from(marginRights.value).map(([key, value]) => ({
      pageNumber: key,
      margin: value,
    })),
    title: title.value,
    subTitle: subTitle.value,
    templateMode: templateMode.value,
    color: color.value,
    customLogo: customLogo.value,
    grade: grade.value,
    startingNumber: startingNumber.value,
  };
};

// 페이지의 레이아웃 모드를 설정하는 함수
const setPageLayoutMode = (pageIndex, mode) => {
  pageLayoutModes.value.set(pageIndex, mode);
  calculatePages(); // 레이아웃 변경 시 페이지 재계산
};

const calculatePages = async () => {
  await Promise.all(
    questions.value.map(async (question) => {
      const img = "https://perfectstudy.co.kr/storage" + (question.image_path ?? question.id);
      const height = await calculateImageHeight(img, question);
      imageHeights.value.set(img, height);
    })
  );

  const result = [];
  const maxColumnHeight = PAGE_CONTENT_HEIGHT * 3.779527559;
  // const maxColumnHeight = 600;

  let currentQuestions = [...questions.value];

  while (currentQuestions.length > 0) {
    const currentPageIndex = result.length;
    const currentLayoutMode = getPageLayoutMode(currentPageIndex);
    if (currentLayoutMode !== "default") {
      const nonDefaultPages = calculateNonDefaultPages(currentQuestions, currentLayoutMode, maxColumnHeight);
      result.push(nonDefaultPages);
      const totalProcessed = nonDefaultPages.left.length + nonDefaultPages.right.length;
      currentQuestions = currentQuestions.slice(totalProcessed);
      continue;
    }

    // 기존 로직 유지
    let currentPage = { left: [], right: [] };
    let leftColumnHeight = 0;
    let rightColumnHeight = 0;

    for (let i = 0; i < currentQuestions.length; i++) {
      const currentQuestion = currentQuestions[i];
      const currentImage = "https://perfectstudy.co.kr/storage" + (currentQuestion.image_path ?? currentQuestion.id);
      let choicesHeight = 0;
      if (currentQuestion.choices_display_type === "seperate" && currentQuestion.answer_type === "multiple_choice") {
        choicesHeight = CHOICE_ITEM_HEIGHT * Math.ceil(currentQuestion.choices.length / 3);
      }

      const imageHeight = imageHeights.value.get(currentImage) + MARGIN_BOTTOM - 20 + choicesHeight;

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
  }

  pages.value = result;
};

const calculateNonDefaultPages = (questions, layoutMode, maxColumnHeight) => {
  let leftCount, rightCount;

  // 레이아웃 모드에 따른 설정
  switch (layoutMode) {
    case "2Items":
      [leftCount, rightCount] = [1, 1];
      break;
    case "4Items":
      [leftCount, rightCount] = [2, 2];
      break;
    case "6Items":
      [leftCount, rightCount] = [3, 3];
      break;
    case "3-1Items":
      [leftCount, rightCount] = [1, 2];
      break;
    case "3-2Items":
      [leftCount, rightCount] = [2, 1];
      break;
    default:
      return { left: [], right: [] };
  }

  // 각 열의 최소 확보 높이 계산
  const leftItemHeight = maxColumnHeight / leftCount;
  const rightItemHeight = maxColumnHeight / rightCount;

  const leftQuestions = [];
  const rightQuestions = [];
  let currentIndex = 0;
  let leftColumnHeight = 0;
  let rightColumnHeight = 0;

  // 왼쪽 열 문제 배치
  if (currentIndex < questions.length) {
    // 첫 번째 문제는 무조건 배치
    const firstQuestion = questions[currentIndex];
    const firstImage = "https://perfectstudy.co.kr/storage" + (firstQuestion.image_path ?? firstQuestion.id);
    const firstChoicesHeight = calculateChoicesHeight(firstQuestion);
    const firstQuestionHeight = imageHeights.value.get(firstImage) + MARGIN_BOTTOM + firstChoicesHeight;

    leftQuestions.push(firstQuestion);
    leftColumnHeight += firstQuestionHeight;
    currentIndex++;
  }

  // 나머지 왼쪽 열 문제 배치
  while (leftQuestions.length < leftCount && currentIndex < questions.length) {
    const question = questions[currentIndex];
    const currentImage = "https://perfectstudy.co.kr/storage" + (question.image_path ?? question.id);
    const choicesHeight = calculateChoicesHeight(question);
    const questionHeight = imageHeights.value.get(currentImage) + MARGIN_BOTTOM + choicesHeight;

    // 현재 문제를 추가했을 때의 총 높이 계산
    const projectedHeight = leftColumnHeight + questionHeight;
    const avgHeight = projectedHeight / (leftQuestions.length + 1);

    // 평균 높이가 최소 확보 높이보다 작거나, 첫 번째 위치면 추가
    if (avgHeight <= leftItemHeight || leftQuestions.length === 0) {
      leftQuestions.push(question);
      leftColumnHeight += questionHeight;
      currentIndex++;
    } else {
      break;
    }
  }

  // 오른쪽 열 문제 배치
  if (currentIndex < questions.length) {
    // 첫 번째 문제는 무조건 배치
    const firstQuestion = questions[currentIndex];
    const firstImage = "https://perfectstudy.co.kr/storage" + (firstQuestion.image_path ?? firstQuestion.id);
    const firstChoicesHeight = calculateChoicesHeight(firstQuestion);
    const firstQuestionHeight = imageHeights.value.get(firstImage) + MARGIN_BOTTOM + firstChoicesHeight;

    rightQuestions.push(firstQuestion);
    rightColumnHeight += firstQuestionHeight;
    currentIndex++;
  }

  // 나머지 오른쪽 열 문제 배치
  while (rightQuestions.length < rightCount && currentIndex < questions.length) {
    const question = questions[currentIndex];

    const currentImage = "https://perfectstudy.co.kr/storage" + (question.image_path ?? question.id);
    const choicesHeight = calculateChoicesHeight(question);
    const questionHeight = imageHeights.value.get(currentImage) + MARGIN_BOTTOM + choicesHeight;

    // 현재 문제를 추가했을 때의 총 높이 계산
    const projectedHeight = rightColumnHeight + questionHeight;
    const avgHeight = projectedHeight / (rightQuestions.length + 1);

    // 평균 높이가 최소 확보 높이보다 작거나, 첫 번째 위치면 추가
    if (avgHeight <= rightItemHeight || rightQuestions.length === 0) {
      rightQuestions.push(question);
      rightColumnHeight += questionHeight;
      currentIndex++;
    } else {
      break;
    }
  }

  return {
    left: leftQuestions,
    right: rightQuestions,
  };
};

const calculateChoicesHeight = (question) => {
  if (question.choices_display_type === "seperate" && question.answer_type === "multiple_choice") {
    return CHOICE_ITEM_HEIGHT * Math.ceil(question.choices.length / 3);
  }
  return 0;
};

const getQuestionNumber = (pageIndex, isLeft, imgIndex) => {
  let number = startingNumber.value;
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
    pageIndex === explanationPages.value.length - 1 && (side === "right" || !explanationPages.value[pageIndex].right);

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
  let newMargin = (marginRights.value.get(currentImageId.value) || 0) - deltaX;

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

  document.querySelector(".temp-explanation-container").style.width = `304.77px`;

  // 청크로 나누고 순차 처리
  createExplanationChunks();

  if (explanationChunks.value.length > 0) {
    isProcessingChunks.value = true;
    await processExplanationChunk(0);
  }
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
const restorePrintLayout = (layoutData) => {
  if (!layoutData) return;

  if (Object.keys(layoutData).length === 0) return;

  // 전역 레이아웃 모드 복원
  if (layoutData.globalLayoutMode) globalLayoutMode.value = layoutData.globalLayoutMode;

  // 페이지별 레이아웃 모드 복원
  if (layoutData.pageLayoutModes)
    pageLayoutModes.value = new Map(layoutData.pageLayoutModes.map((item) => [item.pageNumber, item.mode]));

  // 수동 분할 지점 복원
  if (layoutData.manualSplitPoints) manualSplitPoints.value = layoutData.manualSplitPoints;

  // 여백 설정 복원
  if (layoutData.marginRights)
    marginRights.value = new Map(layoutData.marginRights.map((item) => [item.pageNumber, item.margin]));

  if (layoutData.templateMode) templateMode.value = layoutData.templateMode;

  if (layoutData.color) color.value = layoutData.color;

  title.value = layoutData.title ?? "수학 영역(미적분)";

  subTitle.value = layoutData.subTitle ?? "2023년 대학수학능력시험 실전 모의고사 22회";

  customLogo.value = layoutData.customLogo ?? "/test_removed.png";
  grade.value = layoutData.grade ?? "중1";

  startingNumber.value = layoutData.startingNumber ?? 1;
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

const getHeaderImagePath = () => {
  if (color.value.toLocaleUpperCase() === "#0EA5E9") {
    return "/images/bg-template-blue.png";
  } else if (color.value.toLocaleUpperCase() === "#F43F5E") {
    return "/images/bg-template-red.png";
  } else if (color.value.toLocaleUpperCase() === "#8B5CF6") {
    return "/images/bg-template-purple.png";
  } else if (color.value.toLocaleUpperCase() === "#22C55E") {
    return "/images/bg-template-green.png";
  } else if (color.value.toLocaleUpperCase() === "#EAB308") {
    return "/images/bg-template-amber.png";
  } else if (color.value.toLocaleUpperCase() === "#F97316") {
    return "/images/bg-template-orange.png";
  }

  return "/images/bg-template-blue.png";
};

// 청크로 나누는 함수 추가
const createExplanationChunks = () => {
  const chunks = [];
  for (let i = 0; i < questions.value.length; i += CHUNK_SIZE) {
    chunks.push(questions.value.slice(i, i + CHUNK_SIZE));
  }
  explanationChunks.value = chunks;
  currentChunkIndex.value = 0;
  explanationPages.value = [];
};

// 청크 단위로 해설 페이지 처리
const processExplanationChunk = async (chunkIndex) => {
  if (chunkIndex >= explanationChunks.value.length) {
    isProcessingChunks.value = false;
    return;
  }

  currentChunkIndex.value = chunkIndex;
  const chunk = explanationChunks.value[chunkIndex];
  const chunkStartIndex = chunkIndex * CHUNK_SIZE;

  // 임시 컨테이너에 현재 청크만 렌더링
  if (explanationContainer.value) {
    explanationContainer.value.innerHTML = `
      <div class="flex flex-col gap-y-6">
        ${chunk
          .map(
            (question, index) => `
          <div class="flex flex-row gap-x-4">
            <h3>${chunkStartIndex + index + startingNumber.value})</h3>
            <div class="break-words break-all">
              정답 ${question.answer}
            </div>
          </div>
        `
          )
          .join("")}
        <div class="w-full h-px bg-black mt-10 mb-4"></div>
        ${chunk
          .map(
            (question, index) => `
          <div>
            <div class="flex flex-row gap-x-4">
              <h3>${chunkStartIndex + index + startingNumber.value})</h3>
              <div class="break-words break-all">
                정답 ${question.answer}
                <div class="flex items-center break-words break-all">
                  <span>${question.question_type?.name || ""}</span>
                  - 레벨${question.level}
                </div>
              </div>
            </div>
            <div class="py-6">
              ${
                question.explanation_display_type === "image"
                  ? `<img class="w-full" src="https://perfectstudy.co.kr/storage${question.explanation_image_path}" />`
                  : `<div>${
                      question.explanation ? question.explanation.replace("https://perfectstudy.co.kr", "") : ""
                    }</div>`
              }
            </div>
          </div>
        `
          )
          .join("")}
      </div>
    `;
  }

  // MathJax 렌더링
  await window.MathJax.typesetPromise([explanationContainer.value]);

  // html2canvas로 현재 청크 처리
  const canvas = await html2canvas(explanationContainer.value, {
    scale: 1,
    useCORS: true,
    allowTaint: true,
    backgroundColor: "#ffffff",
    logging: false,
    removeContainer: true,
    foreignObjectRendering: false,
  });

  const columnWidth = explanationContainer.value.offsetWidth;
  const columnHeight = 885.36;
  const chunkPages = [];

  let currentY = 0;
  let remainingHeight = canvas.height;

  while (remainingHeight > 0) {
    const pageIndex = chunkPages.length;
    const leftSplitPoint = manualSplitPoints.value.find(
      (point) => point.pageIndex === pageIndex && point.side === "left"
    );
    const rightSplitPoint = manualSplitPoints.value.find(
      (point) => point.pageIndex === pageIndex && point.side === "right"
    );

    // 왼쪽 컬럼 처리
    const leftHeight = leftSplitPoint
      ? (leftSplitPoint.yPercent / 100) * Math.min(columnHeight, remainingHeight)
      : Math.min(columnHeight, remainingHeight);

    const leftColumn = createColumnImage(canvas, columnWidth, leftHeight, currentY);

    currentY += leftHeight;
    remainingHeight = canvas.height - currentY;

    // 오른쪽 컬럼 처리
    let rightColumn = null;
    if (remainingHeight > 0) {
      const rightHeight = rightSplitPoint
        ? (rightSplitPoint.yPercent / 100) * Math.min(columnHeight, remainingHeight)
        : Math.min(columnHeight, remainingHeight);

      rightColumn = createColumnImage(canvas, columnWidth, rightHeight, currentY);

      currentY += rightHeight;
      remainingHeight = canvas.height - currentY;
    }

    chunkPages.push({
      left: leftColumn,
      right: rightColumn,
    });
  }

  // 현재 청크의 페이지들을 전체 페이지에 추가
  explanationPages.value.push(...chunkPages);

  // 다음 청크 처리 (비동기로 처리하여 메모리 해제)
  setTimeout(() => {
    processExplanationChunk(chunkIndex + 1);
  }, 100);
};

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
      await calculatePages();
      calculateExplanationPages();
    } else if (event.data.type === "onSplitChanged") {
      const split = event.data.data.layoutMode;
      const pageIndex = event.data.data.pageIndex;
      if (pageIndex === -1) {
        globalLayoutMode.value = split;
      } else {
        setPageLayoutMode(pageIndex, split);
      }
      calculatePages();
    } else if (event.data.type === "onPageMetaChanged") {
      if (event.data.data.title) {
        title.value = event.data.data.title;
      }
      if (event.data.data.subTitle) {
        subTitle.value = event.data.data.subTitle;
      }
      if (event.data.data.template) {
        templateMode.value = event.data.data.template;
      }
      if (event.data.data.color) {
        color.value = event.data.data.color;
      }
      if (event.data.data.customLogo) {
        customLogo.value = event.data.data.customLogo;
      }
      if (event.data.data.grade) {
        grade.value = event.data.data.grade;
      }
    } else if (event.data.type === "getPrintLayout") {
      window.parent.postMessage(
        {
          type: "printLayoutData",
          data: JSON.parse(JSON.stringify(getPrintLayoutData())),
        },
        "*"
      );
    } else if (event.data.type === "restorePrintLayout") {
      restorePrintLayout(event.data.data);
    }
  });
  document.addEventListener("mousemove", handleDrag);
  document.addEventListener("mouseup", handleDragEnd);
});
</script>

<template>
  <div
    class="exam-container"
    :class="{
      'pointer-events-none': readonly,
    }">
    <div
      v-for="(page, pageIndex) in pages"
      :key="pageIndex"
      @click="selectedIndex = selectedIndex === pageIndex ? null : pageIndex"
      class="page flex flex-col hover:scale-105 cursor-pointer transition-all hover:brightness-90 relative">
      <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 20 20"
        fill="currentColor"
        v-if="selectedIndex === pageIndex"
        class="size-16 absolute top-4 right-4 text-blue-500 z-30">
        <path
          fill-rule="evenodd"
          d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z"
          clip-rule="evenodd" />
      </svg>

      <!-- 기본 템플릿 헤더 -->
      <template v-if="templateMode === 'default'">
        <div class="left-0 right-0 bottom-0 absolute">
          <!-- <div class="absolute w-full left-0 right-0 bottom-0 pb-5">
                        <div
                            class="absolute bottom-0 left-0 right-0 h-10 opacity-60 clip-4"
                            :style="{ backgroundColor: color }"
                        ></div>
                        <div
                            class="absolute bottom-0 left-0 right-0 h-6 opacity-40 clip-5"
                            :style="{ backgroundColor: color }"
                        ></div>
                    </div> -->
          <img
            :src="getHeaderImagePath()"
            alt=""
            class="w-full h-[40px] object-cover absolute bottom-0 left-0 right-0" />
          <div
            class="absolute bottom-0 left-0 right-0 h-[40px] bg-gradient-to-b from-white/100 to-white/20"
            style=""></div>
        </div>
      </template>

      <!-- 기본 템플릿 푸터 -->
      <template v-if="templateMode === 'default'">
        <div class="absolute px-[20mm] w-full left-0 right-0 top-0 pb-5">
          <img :src="getHeaderImagePath()" alt="" class="w-full h-[110px] object-cover absolute top-0 left-0 right-0" />
          <div class="absolute inset-0 h-[110px] bg-gradient-to-b from-white/0 to-white" style=""></div>
        </div>
      </template>

      <!-- 이미지-->
      <img src="/logo-grayscaled.png" alt="" class="w-28 opacity-40 left-10 bottom-10 absolute" />

      <!-- 첫 페이지일 경우 헤더 표시 -->
      <template v-if="pageIndex === 0">
        <template v-if="templateMode === 'default'">
          <div class="w-full h-28"></div>
          <img :src="customLogo" alt="" class="absolute max-w-[200px] max-h-[80px] right-[15mm] top-[8mm] object-contain z-0" style="height: auto; width: auto;" />
          <div class="absolute px-[20mm] w-full left-0 right-0 top-0 pb-5">
            <div class="relative z-10 pt-16">
              <h1 class="text-2xl text-gray-800 m-0 py-2 font-bold">
                <span :style="{ color: color }">{{ grade }}</span>
                {{ title }}
              </h1>
              <p class="text-base text-gray-500 mt-1 font-semibold tracking-tight">
                {{ subTitle }}
                <span class="text-sm text-gray-400 font-medium ml-0.5">| {{ questions.length }} 문제</span>
              </p>
            </div>

            <!-- Student Info -->
            <div class="flex justify-end mt-5">
              <p class="text-sm text-gray-700">&nbsp;&nbsp; 이름 _________________________</p>
            </div>
          </div>
        </template>
        <template v-if="templateMode === 'high3'">
          <div class="flex items-center justify-center text-2xl tracking-tighter">
            {{ subTitle }}
          </div>
          <div class="flex items-center justify-center text-4xl tracking-tighter font-semibold mt-1.5">
            {{ title }}
          </div>
          <div class="flex flex-row items-center justify-between mt-2">
            <div class="border border-black text-lg px-3 rounded font-bold">2교시</div>
            <div class="flex flex-row gap-x-6">
              <div class="flex flex-row border border-black">
                <div class="border-r border-black py-0.5 px-2">성 명</div>
                <div class="w-[80px]"></div>
              </div>
              <div class="flex flex-row border border-black">
                <div class="border-r border-black py-0.5 px-2">수험번호</div>
                <div class="w-[180px]"></div>
              </div>
            </div>
            <div class="border border-black px-4 rounded-full">홀수형</div>
          </div>
        </template>
      </template>

      <!-- 다른 페이지일 경우 간단한 헤더 -->
      <template v-else>
        <div
          v-if="templateMode === 'high3'"
          class="pb-1 flex items-center relative justify-center text-3xl tracking-tighter font-semibold mt-1.5 border-b-[2px] border-black">
          <div class="font-bold absolute" :class="pageIndex % 2 === 0 ? 'right-0' : 'left-0'">
            {{ pageIndex + 1 }}
          </div>
          {{ title }}
        </div>
        <div
          v-if="templateMode === 'default'"
          class="pb-1 flex items-center relative justify-center text-2xl tracking-tighter font-bold mt-1.5 border-b border-gray-300">
          {{ title }}
        </div>
      </template>

      <div
        class="flex-1 border-black flex flex-row overflow-hidden h-0 min-h-0"
        :class="{
          'border-t-[2px] border-black': pageIndex === 0 && templateMode === 'high3',
          'border-t border-gray-200': pageIndex === 0 && templateMode === 'default',
          ' mt-4': pageIndex === 0,
        }">
        <div class="flex-1 pt-5 pr-4 question-columns flex flex-col">
          <div
            class="flex flex-row gap-x-1"
            v-for="(question, imgIndex) in page.left"
            :class="{
              'flex-1 h-0 overflow-hidden': getPageLayoutMode(pageIndex).includes('Items'),
            }"
            :key="`left-${imgIndex}`">
            <h1
              :class="{
                'font-bold text-2xl text-sky-500 mr-1': templateMode === 'default',
              }">
              <span :style="{ color: color }" v-if="templateMode === 'default'">
                {{ getQuestionNumber(pageIndex, true, imgIndex)?.toString().padStart(2, "0") }}
              </span>
              <template v-else>{{ getQuestionNumber(pageIndex, true, imgIndex) }}.</template>
            </h1>
            <div
              class="flex-1 w-0 hover:border hover:border-dashed border-gray-800 pt-1"
              :style="{
                'margin-bottom': `${MARGIN_BOTTOM}px`,
                'margin-right': `${marginRights.get(question.id) || 0}px`,
              }"
              @mousedown="(e) => handleDragStart(e, question.id)">
              <!-- question_display_type에 따른 조건부 렌더링 -->
              <template v-if="question.question_display_type === 'image'">
                <img
                  :src="'https://perfectstudy.co.kr/storage' + question.image_path"
                  :alt="`Question ${getQuestionNumber(pageIndex, true, imgIndex)}`"
                  draggable="false"
                  class="w-full select-none" />
              </template>
              <template v-else-if="question.question_display_type === 'content'">
                <div v-html="question.content" class="content-display"></div>
              </template>
              <template
                v-if="question.answer_type === 'multiple_choice' && question.choices_display_type === 'seperate'">
                <div class="gap-x-1.5 grid grid-cols-3 mt-1.5">
                  <div v-for="(choice, i) in question.choices" class="flex flex-row gap-x-1 items-center h-7">
                    <div>
                      <span v-if="i === 0">①</span>
                      <span v-else-if="i === 1">②</span>
                      <span v-else-if="i === 2">③</span>
                      <span v-else-if="i === 3">④</span>
                      <span v-else-if="i === 4">⑤</span>
                      <span v-else-if="i === 5">⑥</span>
                    </div>
                    <div v-if="choice.display_type === 'content'" v-html="choice.content" class="flex-1"></div>
                    <div class="flex-1" v-else-if="choice.display_type === 'image'">
                      <img
                        :src="'https://perfectstudy.co.kr/storage' + choice.image_path"
                        :alt="`Choice ${i + 1}`"
                        class="w-full" />
                    </div>
                  </div>
                </div>
              </template>
            </div>
          </div>
        </div>
        <div
          class="bg-black"
          v-if="templateMode === 'high3'"
          :class="{
            'w-[2px]': pageIndex === 0,
            'w-px mt-10 mb-4': pageIndex !== 0,
          }"></div>
        <div
          class="bg-gray-200"
          v-if="templateMode === 'default'"
          :class="{
            'w-px': pageIndex === 0,
            'w-px mt-10 mb-4': pageIndex !== 0,
          }"></div>
        <div class="flex-1 pt-5 pl-4 question-columns flex flex-col">
          <div
            class="flex flex-row gap-x-1"
            :class="{
              'flex-1 h-0 overflow-hidden': getPageLayoutMode(pageIndex).includes('Items'),
            }"
            v-for="(question, imgIndex) in page.right"
            :key="`right-${imgIndex}`">
            <h1
              :class="{
                'font-bold text-2xl text-sky-500 mr-1': templateMode === 'default',
              }">
              <span v-if="templateMode === 'default'" :style="{ color: color }">
                {{ getQuestionNumber(pageIndex, false, imgIndex)?.toString().padStart(2, "0") }}
              </span>
              <template v-else>{{ getQuestionNumber(pageIndex, false, imgIndex) }}.</template>
            </h1>

            <div
              class="flex-1 w-0 hover:border hover:border-dashed border-gray-800 pt-1"
              :style="{
                'margin-bottom': `${MARGIN_BOTTOM}px`,
                'margin-right': `${marginRights.get(question.id) || 0}px`,
              }"
              @mousedown="(e) => handleDragStart(e, question.id)">
              <template v-if="question.question_display_type === 'image'">
                <img
                  :src="'https://perfectstudy.co.kr/storage' + question.image_path"
                  :alt="`Question ${getQuestionNumber(pageIndex, true, imgIndex)}`"
                  draggable="false"
                  class="w-full select-none" />
              </template>
              <template v-else-if="question.question_display_type === 'content'">
                <div v-html="question.content" class="content-display"></div>
              </template>
              <template
                v-if="question.answer_type === 'multiple_choice' && question.choices_display_type === 'seperate'">
                <div class="gap-x-1.5 grid grid-cols-3 mt-1.5">
                  <div v-for="(choice, i) in question.choices" class="flex flex-row gap-x-1 items-center h-7">
                    <div>
                      <span v-if="i === 0">①</span>
                      <span v-else-if="i === 1">②</span>
                      <span v-else-if="i === 2">③</span>
                      <span v-else-if="i === 3">④</span>
                      <span v-else-if="i === 4">⑤</span>
                      <span v-else-if="i === 5">⑥</span>
                    </div>
                    <div v-if="choice.display_type === 'content'" v-html="choice.content" class="flex-1"></div>
                    <div class="flex-1" v-else-if="choice.display_type === 'image'">
                      <img
                        :src="'https://perfectstudy.co.kr/storage' + choice.image_path"
                        :alt="`Choice ${i + 1}`"
                        class="w-full" />
                    </div>
                  </div>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>

      <div class="flex items-center justify-center mt-1 relative">
        <div
          v-if="templateMode === 'high3'"
          class="border border-black diag flex flex-row w-[80px] leading-6 justify-between font-bold px-1.5 text-lg">
          <div class="mb-2">{{ pageIndex + 1 }}</div>
          <div class="mt-2">{{ pages.length }}</div>
        </div>
        <div
          v-if="templateMode === 'default'"
          class="text-base font-medium text-gray-500 flex items-center gap-x-1 mt-2">
          <div :style="{ color: color }" class="text-sky-500 font-bold">
            {{ pageIndex + 1 }}
          </div>
          /
          <div>{{ pages.length }}</div>
        </div>
      </div>
    </div>
    <div class="flex no-print items-center justify-center font-semibold text-gray-700 text-xl flex-row gap-x-4">
      <div class="flex-1 h-px bg-gray-500"></div>
      해설 영역 입니다.
      <div class="flex-1 h-px bg-gray-500"></div>
    </div>
    <div v-if="!readonly" class="no-print flex items-center justify-center text-red-400 text-sm flex-row gap-x-4">
      (클릭 시, 페이지가 분할됩니다.)
    </div>
    <div
      v-for="(page, pageIndex) in explanationPages"
      :key="'explanation-' + pageIndex"
      class="page explanation flex flex-col">
      <!-- 페이지 헤더 -->
      <div
        v-if="templateMode === 'high3'"
        class="pb-1 flex items-center relative justify-center text-3xl tracking-tighter font-semibold mt-1.5 border-black border-b-[2px]">
        <div class="font-bold absolute" :class="(pages.length + pageIndex) % 2 === 0 ? 'right-0' : 'left-0'">
          {{ pages.length + pageIndex + 1 }}
        </div>
        {{ title }}
      </div>
      <div
        v-if="templateMode === 'default'"
        class="pb-1 flex items-center relative justify-center text-2xl tracking-tighter font-semibold mt-1.5 border-gray-300 border-b">
        {{ title }}
      </div>

      <!-- 2단 레이아웃의 해설 내용 -->
      <div class="flex-1 border-black flex flex-row overflow-hidden h-0 min-h-0">
        <!-- 왼쪽 컬럼 -->
        <div class="flex-1 pr-4 relative" @click="(e) => handleExplanationClick(e, pageIndex, 'left')">
          <!-- relative 추가 -->
          <img :src="page.left" class="w-full h-full object-contain object-left-top" v-if="page.left" />
          <!-- 왼쪽 컬럼 분할점 -->
          <div
            v-for="point in manualSplitPoints.filter((p) => p.pageIndex === pageIndex && p.side === 'left')"
            :key="'left-split-' + point.yPercent"
            v-show="!readonly"
            class="absolute left-0 right-2 h-[2px] bg-red-500 no-print"
            :style="{
              top: `${point.yPercent}%`,
            }"></div>
        </div>

        <!-- 중앙 구분선 -->
        <div class="bg-black w-px mt-10 mb-4"></div>

        <!-- 오른쪽 컬럼 -->
        <div class="flex-1 pl-4 relative" @click="(e) => handleExplanationClick(e, pageIndex, 'right')">
          <!-- relative 추가 -->
          <img :src="page.right" class="w-full h-full object-contain object-left-top" v-if="page.right" />
          <!-- 오른쪽 컬럼 분할점 -->
          <div
            v-for="point in manualSplitPoints.filter((p) => p.pageIndex === pageIndex && p.side === 'right')"
            v-show="!readonly"
            :key="'right-split-' + point.yPercent"
            class="absolute h-[2px] bg-red-500 right-0 left-2 no-print"
            :style="{
              top: `${point.yPercent}%`,
            }"></div>
        </div>
      </div>

      <!-- 페이지 번호 -->
      <div class="flex items-center justify-center mt-1">
        <div
          v-if="templateMode === 'high3'"
          class="border border-black diag flex flex-row w-[80px] leading-6 justify-between font-bold px-1.5 text-lg">
          <div class="mb-2">{{ pages.length + pageIndex + 1 }}</div>
          <div class="mt-2">
            {{ explanationPages.length + pages.length }}
          </div>
        </div>

        <div
          v-if="templateMode === 'default'"
          class="text-base font-medium text-gray-500 flex items-center gap-x-1 mt-2">
          <div :style="{ color: color }" class="text-sky-500 font-bold">
            {{ pages.length + pageIndex + 1 }}
          </div>
          /
          <div>
            {{ explanationPages.length + pages.length }}
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 해설 렌더링을 위한 임시 컨테이너 -->
  <div ref="explanationContainer" class="temp-explanation-container pb-4 no-print text-sm">
    <!-- 청크 처리 중일 때 로딩 표시 -->
    <div v-if="isProcessingChunks" class="flex items-center justify-center p-8">
      <div class="text-gray-500">
        해설 페이지 생성 중... ({{ currentChunkIndex + 1 }}/{{ explanationChunks.length }})
      </div>
    </div>
    <!-- 청크 처리 완료 후에는 빈 컨테이너로 유지 -->
    <div v-else></div>
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
.clip-1 {
  clip-path: polygon(0 0, 100% 0, 100% 90%, 0 0%);
}

.clip-2 {
  clip-path: polygon(-50% 0%, 100% 0%, 100% 0%, -50% 100%);
}

.clip-3 {
  clip-path: polygon(-200% 0%, 100% 0%, 100% 0%, 100% 25%, -200% 100%);
}

.clip-4 {
  clip-path: polygon(0 90%, 100% 0, 100% 100%, 0 100%);
}

.clip-5 {
  clip-path: polygon(10% 100%, 100% 0, 100% 100%, 10% 100%);
}
</style>
