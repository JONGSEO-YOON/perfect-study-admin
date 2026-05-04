<script setup>
import { defineProps } from "vue";

const props = defineProps({
  pageIndex: {
    type: Number,
    required: true,
  },
  page: {
    type: Object,
    required: true,
  },
  pages: {
    type: Array,
    required: true,
  },
  questions: {
    type: Array,
    required: true,
  },
  color: {
    type: String,
    default: "#0ea5e9",
  },
  title: {
    type: String,
    default: "",
  },
  subTitle: {
    type: String,
    default: "",
  },
  grade: {
    type: String,
    default: "",
  },
  customLogo: {
    type: String,
    default: "",
  },
  startingNumber: {
    type: Number,
    default: 1,
  },
  marginRights: {
    type: Map,
    default: () => new Map(),
  },
  MARGIN_BOTTOM: {
    type: Number,
    default: 130,
  },
  selectedIndex: {
    type: Number,
    default: null,
  },
  readonly: {
    type: Boolean,
    default: false,
  },
  getPageLayoutMode: {
    type: Function,
    required: true,
  },
  getQuestionNumber: {
    type: Function,
    required: true,
  },
  handleDragStart: {
    type: Function,
    required: true,
  },
});

const getHeaderImagePath = () => {
  if (props.color.toLocaleUpperCase() === "#0EA5E9") {
    return "/images/bg-template-blue.png";
  } else if (props.color.toLocaleUpperCase() === "#F43F5E") {
    return "/images/bg-template-red.png";
  } else if (props.color.toLocaleUpperCase() === "#8B5CF6") {
    return "/images/bg-template-purple.png";
  } else if (props.color.toLocaleUpperCase() === "#22C55E") {
    return "/images/bg-template-green.png";
  } else if (props.color.toLocaleUpperCase() === "#EAB308") {
    return "/images/bg-template-amber.png";
  } else if (props.color.toLocaleUpperCase() === "#F97316") {
    return "/images/bg-template-orange.png";
  }

  return "/images/bg-template-blue.png";
};

console.log(props.customLogo);
</script>

<template>
  <div class="page flex flex-col hover:scale-105 cursor-pointer transition-all hover:brightness-90 relative">
    <!-- 선택 표시 아이콘 -->
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
    <div class="left-0 right-0 bottom-0 absolute">
      <img :src="getHeaderImagePath()" alt="" class="w-full h-[40px] object-cover absolute bottom-0 left-0 right-0" />
      <div class="absolute bottom-0 left-0 right-0 h-[40px] bg-gradient-to-b from-white/100 to-white/20"></div>
    </div>

    <!-- 기본 템플릿 푸터 -->
    <div class="absolute px-[20mm] w-full left-0 right-0 top-0 pb-5">
      <img :src="getHeaderImagePath()" alt="" class="w-full h-[110px] object-cover absolute top-0 left-0 right-0" />
      <div class="absolute inset-0 h-[110px] bg-gradient-to-b from-white/0 to-white"></div>
    </div>

    <!-- 로고 이미지 -->
    <img src="/logo-grayscaled.png" alt="" class="w-28 opacity-40 left-10 bottom-10 absolute" />

    <!-- 첫 페이지일 경우 헤더 표시 -->
    <template v-if="pageIndex === 0">
      <div class="w-full h-28"></div>
      <img v-if="customLogo != ''" :src="customLogo" alt="" class="absolute max-w-[120px] max-h-[60px] right-[15mm] top-[8mm] object-contain z-0" />
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

    <!-- 다른 페이지일 경우 간단한 헤더 -->
    <template v-else>
      <div
        class="pb-1 flex items-center relative justify-center text-2xl tracking-tighter font-bold mt-1.5 border-b border-gray-300">
        {{ title }}
      </div>
    </template>

    <!-- 문제 영역 -->
    <div
      class="flex-1 border-black flex flex-row overflow-hidden h-0 min-h-0"
      :class="{
        'border-t border-gray-200': pageIndex === 0,
        'mt-4': pageIndex === 0,
      }">
      <!-- 왼쪽 컬럼 -->
      <div class="flex-1 pt-5 pr-4 question-columns flex flex-col">
        <div
          class="flex flex-row gap-x-1"
          v-for="(question, imgIndex) in page.left"
          :class="{
            'flex-1 h-0 overflow-hidden': getPageLayoutMode(pageIndex).includes('Items'),
          }"
          :key="`left-${imgIndex}`">
          <h1 class="font-bold text-2xl text-sky-500 mr-1">
            <span :style="{ color: color }">
              {{ getQuestionNumber(pageIndex, true, imgIndex)?.toString().padStart(2, "0") }}
            </span>
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
            <template v-if="question.answer_type === 'multiple_choice' && question.choices_display_type === 'seperate'">
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

      <!-- 중앙 구분선 -->
      <div
        class="border-black border-r"
        :class="{
          'w-px': pageIndex === 0,
          'w-px mt-10 mb-4': pageIndex !== 0,
        }"></div>

      <!-- 오른쪽 컬럼 -->
      <div class="flex-1 pt-5 pl-4 question-columns flex flex-col">
        <div
          class="flex flex-row gap-x-1"
          :class="{
            'flex-1 h-0 overflow-hidden': getPageLayoutMode(pageIndex).includes('Items'),
          }"
          v-for="(question, imgIndex) in page.right"
          :key="`right-${imgIndex}`">
          <h1 class="font-bold text-2xl text-sky-500 mr-1">
            <span :style="{ color: color }">
              {{ getQuestionNumber(pageIndex, false, imgIndex)?.toString().padStart(2, "0") }}
            </span>
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
            <template v-if="question.answer_type === 'multiple_choice' && question.choices_display_type === 'seperate'">
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

    <!-- 페이지 번호 -->
    <div class="flex items-center justify-center mt-1 relative">
      <div class="text-base font-medium text-gray-500 flex items-center gap-x-1 mt-2">
        <div :style="{ color: color }" class="text-sky-500 font-bold">
          {{ pageIndex + 1 }}
        </div>
        /
        <div>{{ pages.length }}</div>
      </div>
    </div>
  </div>
</template>

<style scoped>
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
</style>
