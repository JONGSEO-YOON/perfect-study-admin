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
    <!-- 배경 장식 요소들 -->
    <div class="absolute inset-0 pointer-events-none opacity-10">
      <!-- 왼쪽 상단 별 -->
      <div class="absolute top-4 left-4">
        <svg class="w-8 h-8" :style="{ color: color }" fill="currentColor" viewBox="0 0 24 24">
          <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
        </svg>
      </div>

      <!-- 오른쪽 상단 하트 -->
      <div class="absolute top-4 right-4">
        <svg class="w-8 h-8" :style="{ color: color }" fill="currentColor" viewBox="0 0 24 24">
          <path
            d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
        </svg>
      </div>

      <!-- 왼쪽 하단 연필 -->
      <div class="absolute bottom-4 left-4">
        <svg class="w-8 h-8" :style="{ color: color }" fill="currentColor" viewBox="0 0 24 24">
          <path
            d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" />
        </svg>
      </div>

      <!-- 오른쪽 하단 삼각형 -->
      <div class="absolute bottom-4 right-4">
        <svg class="w-8 h-8" :style="{ color: color }" fill="currentColor" viewBox="0 0 24 24">
          <path d="M12 2L2 19h20L12 2z" />
        </svg>
      </div>
    </div>

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
      <img :src="customLogo" alt="" class="absolute max-w-40 max-h-12 right-20 top-20" />
      <div class="border-b pb-2 mb-3" :style="{ borderColor: color }">
        <div class="p-2 rounded-lg mb-2 relative">
          <!-- 귀여운 곰돌이 아이콘 (헤더 왼쪽) -->
          <div class="absolute left-0 top-3">
            <svg
              version="1.0"
              xmlns="http://www.w3.org/2000/svg"
              class="w-12 h-12"
              viewBox="0 0 561 738"
              style="min-width: 1.5rem; min-height: 1.5rem"
              preserveAspectRatio="xMidYMid meet">
              <g
                transform="translate(0.000000,738.000000) scale(0.100000,-0.100000)"
                :fill="color"
                stroke="#7B4A4A"
                stroke-width="30">
                <path
                  d="M4107 7345 c-183 -35 -346 -123 -488 -264 -105 -104 -165 -195 -204
-308 -21 -58 -28 -68 -47 -66 -13 0 -92 6 -176 13 -249 19 -552 -9 -810 -75
-242 -61 -503 -173 -670 -287 -24 -17 -44 -28 -46 -26 -112 165 -275 285 -471
344 -83 26 -104 28 -260 28 -200 1 -247 -9 -400 -84 -272 -133 -459 -406 -496
-723 -23 -204 34 -431 153 -606 45 -65 153 -170 223 -216 33 -21 92 -53 133
-71 40 -17 75 -34 77 -36 2 -2 -7 -58 -20 -123 -59 -296 -59 -696 -1 -998 90
-468 315 -868 610 -1086 63 -47 85 -69 81 -80 -19 -47 -47 -177 -55 -257 -19
-181 22 -366 111 -498 56 -85 107 -132 195 -183 l60 -35 -15 -69 c-15 -67 -17
-69 -46 -69 -38 0 -137 -25 -190 -47 -182 -77 -340 -261 -384 -445 -49 -208 0
-432 142 -643 53 -79 176 -200 257 -253 312 -204 811 -169 1190 83 l66 44 202
-10 c140 -7 256 -7 373 0 l169 10 68 -43 c327 -211 717 -272 1035 -161 196 68
377 235 483 447 66 131 88 220 88 358 0 79 -5 133 -17 171 -9 31 -28 122 -41
201 -14 80 -36 189 -50 243 l-25 98 29 36 c52 65 100 155 127 236 24 71 27 95
27 215 0 118 -4 146 -27 223 -48 155 -128 295 -260 454 l-76 91 67 44 c356
230 599 555 697 933 55 211 73 513 45 765 -50 466 -226 911 -489 1235 l-48 60
47 82 c208 361 154 809 -130 1093 -110 110 -270 199 -430 240 -110 29 -276 35
-383 15z m321 -131 c310 -64 545 -291 606 -586 44 -215 0 -434 -128 -622 -25
-38 -46 -80 -46 -93 0 -16 19 -47 48 -82 273 -319 438 -706 503 -1181 8 -54
13 -185 13 -305 0 -173 -4 -231 -22 -327 -80 -427 -295 -741 -689 -1004 -86
-57 -219 -128 -305 -163 -34 -13 -47 -55 -27 -90 23 -41 63 -38 155 9 l79 41
54 -58 c134 -143 250 -340 287 -488 24 -98 22 -241 -5 -325 -42 -131 -155
-281 -277 -366 l-59 -41 -40 15 c-63 23 -256 28 -335 7 -89 -22 -214 -85 -289
-146 l-62 -50 -32 22 c-54 37 -118 102 -138 141 -32 62 -26 149 15 233 62 129
126 185 386 339 169 101 200 125 200 156 0 29 -34 62 -62 62 -43 0 -408 -227
-500 -311 -105 -96 -188 -265 -188 -383 0 -116 66 -237 168 -308 28 -19 51
-38 52 -42 0 -4 -18 -31 -40 -60 -49 -65 -150 -263 -150 -295 0 -26 32 -63 56
-63 34 0 57 25 89 93 124 271 334 455 567 498 182 33 395 -55 505 -208 116
-163 135 -369 51 -571 -81 -196 -238 -360 -411 -430 -129 -52 -352 -68 -517
-36 -152 28 -338 107 -470 198 l-45 31 -390 0 c-214 1 -401 5 -415 9 -20 5
-41 -4 -110 -50 -219 -145 -404 -204 -642 -204 -194 0 -331 37 -452 124 -133
94 -247 248 -304 408 -34 97 -42 257 -17 344 42 147 138 265 266 328 93 46
204 70 281 61 234 -25 475 -220 602 -485 42 -87 61 -110 93 -110 29 0 63 33
63 63 0 31 -79 184 -136 265 -132 186 -313 320 -507 377 -64 18 -62 11 -31
115 28 93 17 112 -96 171 -77 40 -164 124 -203 196 -131 246 -63 633 163 917
108 136 273 247 425 286 95 24 242 26 334 5 237 -56 399 -287 361 -514 -14
-87 -52 -166 -133 -284 -36 -51 -71 -105 -79 -122 -15 -29 -32 -114 -42 -204
-8 -76 26 -113 89 -97 26 7 45 53 45 113 0 81 22 137 97 245 117 170 153 267
153 418 0 164 -52 284 -179 410 -125 124 -271 180 -470 180 -177 0 -314 -42
-465 -141 -126 -83 -284 -255 -345 -376 -11 -23 -25 -44 -30 -47 -22 -14 -187
134 -282 252 -270 336 -415 882 -380 1426 12 175 38 343 71 450 20 62 20 69 6
92 -9 13 -29 26 -44 30 -208 46 -411 214 -498 411 -47 106 -67 215 -61 343 6
142 23 207 88 338 115 229 320 378 572 413 161 22 368 -18 507 -98 78 -45 196
-161 235 -229 57 -99 80 -106 159 -45 227 173 564 305 924 362 233 36 615 35
755 -2 65 -17 86 2 112 96 33 127 86 217 190 321 165 167 334 240 563 243 46
0 118 -6 160 -15z m408 -5797 c5 -16 -2 -13 -37 14 -24 19 -46 35 -48 36 -2 1
10 13 25 27 l29 25 12 -41 c7 -22 15 -50 19 -61z" />
                <path
                  d="M3726 5044 c-187 -53 -212 -64 -216 -94 -7 -49 16 -80 58 -80 51 0
416 108 429 126 31 43 -2 105 -54 103 -16 0 -113 -25 -217 -55z" />
                <path
                  d="M3785 4340 c-51 -32 -80 -94 -80 -170 1 -211 237 -302 327 -127 52
102 6 251 -94 303 -39 20 -117 18 -153 -6z" />
                <path
                  d="M2214 4101 c-71 -33 -111 -114 -101 -206 6 -62 24 -100 67 -142 65
-63 165 -62 227 3 42 45 56 92 51 170 -3 50 -10 72 -34 106 -54 77 -136 104
-210 69z" />
                <path
                  d="M3047 3905 c-21 -7 -40 -15 -41 -17 -16 -18 -25 -52 -20 -73 7 -29
54 -58 78 -49 13 5 16 -1 16 -28 0 -19 3 -62 6 -94 l6 -59 -58 -40 c-32 -22
-66 -48 -76 -57 -22 -22 -23 -71 -2 -92 27 -28 64 -18 141 35 l74 51 106 -26
c107 -26 134 -24 155 8 14 20 8 63 -9 78 -10 9 -62 26 -115 39 l-97 23 -3 85
c-2 59 1 88 9 94 49 31 60 94 21 121 -28 20 -138 21 -191 1z" />
              </g>
            </svg>
          </div>

          <div class="ml-10">
            <h1 class="text-3xl font-bold">
              {{ grade }}
              <span :style="{ color: color }">{{ title }}</span>
            </h1>
            <p class="text-sm text-gray-600">
              {{ subTitle }}
              <span
                class="text-xs px-2 py-0.5 rounded-full ml-2"
                :style="{ backgroundColor: color + '20', color: color }">
                {{ questions.length }} 문제
              </span>
            </p>
          </div>
        </div>

        <!-- Student Info -->
        <div class="flex justify-end mt-2">
          <div class="flex items-center gap-2">
            <span class="text-sm text-gray-700">이름:</span>
            <div class="w-32 h-8 border-2 border-dashed rounded" :style="{ borderColor: color + '60' }"></div>
          </div>
        </div>
      </div>
    </template>

    <!-- 다른 페이지일 경우 간단한 헤더 -->
    <template v-else>
      <div class="border-b pb-1.5 mb-3" :style="{ borderColor: color }">
        <div class="p-1.5 rounded-lg relative">
          <!-- 귀여운 곰돌이 아이콘 -->
          <div class="absolute left-0 -top-1">
            <svg
              version="1.0"
              xmlns="http://www.w3.org/2000/svg"
              class="w-12 h-12"
              viewBox="0 0 561 738"
              style="min-width: 1.5rem; min-height: 1.5rem"
              preserveAspectRatio="xMidYMid meet">
              <g
                transform="translate(0.000000,738.000000) scale(0.100000,-0.100000)"
                :fill="color"
                stroke="#7B4A4A"
                stroke-width="30">
                <path
                  d="M4107 7345 c-183 -35 -346 -123 -488 -264 -105 -104 -165 -195 -204
-308 -21 -58 -28 -68 -47 -66 -13 0 -92 6 -176 13 -249 19 -552 -9 -810 -75
-242 -61 -503 -173 -670 -287 -24 -17 -44 -28 -46 -26 -112 165 -275 285 -471
344 -83 26 -104 28 -260 28 -200 1 -247 -9 -400 -84 -272 -133 -459 -406 -496
-723 -23 -204 34 -431 153 -606 45 -65 153 -170 223 -216 33 -21 92 -53 133
-71 40 -17 75 -34 77 -36 2 -2 -7 -58 -20 -123 -59 -296 -59 -696 -1 -998 90
-468 315 -868 610 -1086 63 -47 85 -69 81 -80 -19 -47 -47 -177 -55 -257 -19
-181 22 -366 111 -498 56 -85 107 -132 195 -183 l60 -35 -15 -69 c-15 -67 -17
-69 -46 -69 -38 0 -137 -25 -190 -47 -182 -77 -340 -261 -384 -445 -49 -208 0
-432 142 -643 53 -79 176 -200 257 -253 312 -204 811 -169 1190 83 l66 44 202
-10 c140 -7 256 -7 373 0 l169 10 68 -43 c327 -211 717 -272 1035 -161 196 68
377 235 483 447 66 131 88 220 88 358 0 79 -5 133 -17 171 -9 31 -28 122 -41
201 -14 80 -36 189 -50 243 l-25 98 29 36 c52 65 100 155 127 236 24 71 27 95
27 215 0 118 -4 146 -27 223 -48 155 -128 295 -260 454 l-76 91 67 44 c356
230 599 555 697 933 55 211 73 513 45 765 -50 466 -226 911 -489 1235 l-48 60
47 82 c208 361 154 809 -130 1093 -110 110 -270 199 -430 240 -110 29 -276 35
-383 15z m321 -131 c310 -64 545 -291 606 -586 44 -215 0 -434 -128 -622 -25
-38 -46 -80 -46 -93 0 -16 19 -47 48 -82 273 -319 438 -706 503 -1181 8 -54
13 -185 13 -305 0 -173 -4 -231 -22 -327 -80 -427 -295 -741 -689 -1004 -86
-57 -219 -128 -305 -163 -34 -13 -47 -55 -27 -90 23 -41 63 -38 155 9 l79 41
54 -58 c134 -143 250 -340 287 -488 24 -98 22 -241 -5 -325 -42 -131 -155
-281 -277 -366 l-59 -41 -40 15 c-63 23 -256 28 -335 7 -89 -22 -214 -85 -289
-146 l-62 -50 -32 22 c-54 37 -118 102 -138 141 -32 62 -26 149 15 233 62 129
126 185 386 339 169 101 200 125 200 156 0 29 -34 62 -62 62 -43 0 -408 -227
-500 -311 -105 -96 -188 -265 -188 -383 0 -116 66 -237 168 -308 28 -19 51
-38 52 -42 0 -4 -18 -31 -40 -60 -49 -65 -150 -263 -150 -295 0 -26 32 -63 56
-63 34 0 57 25 89 93 124 271 334 455 567 498 182 33 395 -55 505 -208 116
-163 135 -369 51 -571 -81 -196 -238 -360 -411 -430 -129 -52 -352 -68 -517
-36 -152 28 -338 107 -470 198 l-45 31 -390 0 c-214 1 -401 5 -415 9 -20 5
-41 -4 -110 -50 -219 -145 -404 -204 -642 -204 -194 0 -331 37 -452 124 -133
94 -247 248 -304 408 -34 97 -42 257 -17 344 42 147 138 265 266 328 93 46
204 70 281 61 234 -25 475 -220 602 -485 42 -87 61 -110 93 -110 29 0 63 33
63 63 0 31 -79 184 -136 265 -132 186 -313 320 -507 377 -64 18 -62 11 -31
115 28 93 17 112 -96 171 -77 40 -164 124 -203 196 -131 246 -63 633 163 917
108 136 273 247 425 286 95 24 242 26 334 5 237 -56 399 -287 361 -514 -14
-87 -52 -166 -133 -284 -36 -51 -71 -105 -79 -122 -15 -29 -32 -114 -42 -204
-8 -76 26 -113 89 -97 26 7 45 53 45 113 0 81 22 137 97 245 117 170 153 267
153 418 0 164 -52 284 -179 410 -125 124 -271 180 -470 180 -177 0 -314 -42
-465 -141 -126 -83 -284 -255 -345 -376 -11 -23 -25 -44 -30 -47 -22 -14 -187
134 -282 252 -270 336 -415 882 -380 1426 12 175 38 343 71 450 20 62 20 69 6
92 -9 13 -29 26 -44 30 -208 46 -411 214 -498 411 -47 106 -67 215 -61 343 6
142 23 207 88 338 115 229 320 378 572 413 161 22 368 -18 507 -98 78 -45 196
-161 235 -229 57 -99 80 -106 159 -45 227 173 564 305 924 362 233 36 615 35
755 -2 65 -17 86 2 112 96 33 127 86 217 190 321 165 167 334 240 563 243 46
0 118 -6 160 -15z m408 -5797 c5 -16 -2 -13 -37 14 -24 19 -46 35 -48 36 -2 1
10 13 25 27 l29 25 12 -41 c7 -22 15 -50 19 -61z" />
                <path
                  d="M3726 5044 c-187 -53 -212 -64 -216 -94 -7 -49 16 -80 58 -80 51 0
416 108 429 126 31 43 -2 105 -54 103 -16 0 -113 -25 -217 -55z" />
                <path
                  d="M3785 4340 c-51 -32 -80 -94 -80 -170 1 -211 237 -302 327 -127 52
102 6 251 -94 303 -39 20 -117 18 -153 -6z" />
                <path
                  d="M2214 4101 c-71 -33 -111 -114 -101 -206 6 -62 24 -100 67 -142 65
-63 165 -62 227 3 42 45 56 92 51 170 -3 50 -10 72 -34 106 -54 77 -136 104
-210 69z" />
                <path
                  d="M3047 3905 c-21 -7 -40 -15 -41 -17 -16 -18 -25 -52 -20 -73 7 -29
54 -58 78 -49 13 5 16 -1 16 -28 0 -19 3 -62 6 -94 l6 -59 -58 -40 c-32 -22
-66 -48 -76 -57 -22 -22 -23 -71 -2 -92 27 -28 64 -18 141 35 l74 51 106 -26
c107 -26 134 -24 155 8 14 20 8 63 -9 78 -10 9 -62 26 -115 39 l-97 23 -3 85
c-2 59 1 88 9 94 49 31 60 94 21 121 -28 20 -138 21 -191 1z" />
              </g>
            </svg>
          </div>

          <h1 class="text-2xl font-bold ml-12" :style="{ color: color }">{{ title }}</h1>
        </div>
      </div>
    </template>

    <!-- 문제 영역 -->
    <div class="flex-1 flex flex-row overflow-hidden h-0 min-h-0">
      <!-- 왼쪽 컬럼 -->
      <div class="flex-1 pr-4 question-columns flex flex-col">
        <div
          class="flex flex-row gap-x-1"
          v-for="(question, imgIndex) in page.left"
          :class="{
            'flex-1 h-0 overflow-hidden': getPageLayoutMode(pageIndex).includes('Items'),
          }"
          :key="`left-${imgIndex}`">
          <h1 class="font-bold text-lg mr-2">
            <span
              class="inline-flex items-center justify-center w-8 h-8 rounded-full text-white text-sm font-bold"
              :style="{ backgroundColor: color }">
              {{ getQuestionNumber(pageIndex, true, imgIndex) }}
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
        :style="{ borderColor: color }"
        class="border-r"
        :class="{
          'w-px': pageIndex === 0,
          'w-px mt-10 mb-4': pageIndex !== 0,
        }"></div>

      <!-- 오른쪽 컬럼 -->
      <div class="flex-1 pl-4 question-columns flex flex-col">
        <div
          class="flex flex-row gap-x-1"
          :class="{
            'flex-1 h-0 overflow-hidden': getPageLayoutMode(pageIndex).includes('Items'),
          }"
          v-for="(question, imgIndex) in page.right"
          :key="`right-${imgIndex}`">
          <h1 class="font-bold text-lg mr-2">
            <span
              class="inline-flex items-center justify-center w-8 h-8 rounded-full text-white text-sm font-bold"
              :style="{ backgroundColor: color }">
              {{ getQuestionNumber(pageIndex, false, imgIndex) }}
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
    <div class="flex items-center justify-center mt-4">
      <div class="flex items-center gap-x-2 px-4 py-2 rounded-full" :style="{ backgroundColor: color + '10' }">
        <span class="text-sm font-bold" :style="{ color: color }">{{ pageIndex + 1 }}</span>
        <span class="text-sm text-gray-500">/</span>
        <span class="text-sm text-gray-600">{{ pages.length }}</span>
      </div>
    </div>

    <!-- 로고 이미지 -->
    <img src="/logo-grayscaled.png" alt="" class="w-28 opacity-40 left-10 bottom-10 absolute" />
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
