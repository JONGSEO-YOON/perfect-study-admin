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
    default: "수학 영역(미적분)",
  },
  subTitle: {
    type: String,
    default: "2023년 대학수학능력시험 실전 모의고사 22회",
  },
  grade: {
    type: String,
    default: "중1",
  },
  customLogo: {
    type: String,
    default: "/test_removed.png",
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

    <!-- 첫 페이지일 경우 헤더 표시 -->
    <template v-if="pageIndex === 0">
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

    <!-- 다른 페이지일 경우 간단한 헤더 -->
    <template v-else>
      <div
        class="pb-1 flex items-center relative justify-center text-3xl tracking-tighter font-semibold mt-1.5 border-b-[2px] border-black">
        <div class="font-bold absolute" :class="pageIndex % 2 === 0 ? 'right-0' : 'left-0'">
          {{ pageIndex + 1 }}
        </div>
        {{ title }}
      </div>
    </template>

    <!-- 문제 영역 -->
    <div
      class="flex-1 border-black flex flex-row overflow-hidden h-0 min-h-0"
      :class="{
        'border-t-[2px] border-black': pageIndex === 0,
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
          <span>{{ getQuestionNumber(pageIndex, true, imgIndex) }}.</span>
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
        class="border-black border-[1px]"
        :class="{
          '!w-[2px]': pageIndex === 0,
          '!w-[2px] mt-10 mb-4': pageIndex !== 0,
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
          <span>{{ getQuestionNumber(pageIndex, false, imgIndex) }}.</span>

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
      <div class="border border-black diag flex flex-row w-[80px] leading-6 justify-between font-bold px-1.5 text-lg">
        <div class="mb-2">{{ pageIndex + 1 }}</div>
        <div class="mt-2">{{ pages.length }}</div>
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

.diag {
  background: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' version='1.1' preserveAspectRatio='none' viewBox='0 0 100 100'><path d='M0 99 L99 0 L100 1 L1 100' fill='black' /></svg>");
  background-repeat: no-repeat;
  -webkit-print-color-adjust: exact;
  background-position: center center;
  background-size: 100% 100%, auto;
}
</style>
