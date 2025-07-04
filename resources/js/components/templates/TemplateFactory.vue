<script setup>
import { defineProps } from "vue";
import DefaultTemplate from "./DefaultTemplate.vue";
import High3Template from "./High3Template.vue";
import SimpleTemplate from "./SimpleTemplate.vue";
import FriendlyTemplate from "./FriendlyTemplate.vue";
import RoundTemplate from "./RoundTemplate.vue";

const props = defineProps({
  templateMode: {
    type: String,
    required: true,
  },
  // 모든 템플릿에서 공통으로 사용하는 props
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

const getTemplateComponent = () => {
  if (props.isExplanation) {
    // 해설 페이지 템플릿
    switch (props.templateMode) {
      case "default":
        return DefaultExplanationTemplate;
      case "high3":
        return High3ExplanationTemplate;
      default:
        return DefaultExplanationTemplate;
    }
  } else {
    // 문제 페이지 템플릿
    switch (props.templateMode) {
      case "default":
        return DefaultTemplate;
      case "high3":
        return High3Template;
      case "simple":
        return SimpleTemplate;
      case "friendly":
        return FriendlyTemplate;
      case "round":
        return RoundTemplate;
      default:
        return DefaultTemplate;
    }
  }
};
</script>

<template>
  <component
    :is="getTemplateComponent()"
    :page-index="pageIndex"
    :page="page"
    :pages="pages"
    :questions="questions"
    :color="color"
    :title="title"
    :sub-title="subTitle"
    :grade="grade"
    :custom-logo="customLogo"
    :starting-number="startingNumber"
    :margin-rights="marginRights"
    :MARGIN_BOTTOM="MARGIN_BOTTOM"
    :selected-index="selectedIndex"
    :readonly="readonly"
    :get-page-layout-mode="getPageLayoutMode"
    :get-question-number="getQuestionNumber"
    :handle-drag-start="handleDragStart"
    @click="$emit('click')" />
</template>
