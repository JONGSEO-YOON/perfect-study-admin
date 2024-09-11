<script setup>
import { ref, onMounted, watch, onUnmounted } from "vue";

const props = defineProps({
    page: {
        type: Object,
        required: true,
    },
    data: {
        type: Array,
        required: true,
    },
});

const canvasRef = ref(null);
const containerRef = ref(null);
let fabricCanvas = null;

const renderPage = () => {
    if (!fabricCanvas || !containerRef.value) return;

    fabricCanvas.clear();

    fabric.Image.fromURL(props.page.url, (img) => {
        const containerWidth = containerRef.value.clientWidth;
        const containerHeight = 900;

        const scale = Math.min(
            containerWidth / img.width,
            containerHeight / img.height
        );

        const scaledWidth = img.width * scale;
        const scaledHeight = img.height * scale;

        fabricCanvas.setWidth(scaledWidth);
        fabricCanvas.setHeight(scaledHeight);

        fabricCanvas.setBackgroundImage(
            img,
            fabricCanvas.renderAll.bind(fabricCanvas),
            {
                scaleX: scale,
                scaleY: scale,
            }
        );

        props.data.forEach((rect) => {
            const scaledRect = new fabric.Rect({
                left: rect.x * scale,
                top: rect.y * scale,
                width: rect.width * scale,
                height: rect.height * scale,
                fill: "#000000",
                opacity: 0.25,
                // stroke: "#000000",
                // strokeWidth: 2,
            });
            fabricCanvas.add(scaledRect);
        });

        fabricCanvas.renderAll();
    });
};

onMounted(() => {
    fabricCanvas = new fabric.Canvas(canvasRef.value);
    renderPage();

    window.addEventListener("resize", renderPage);
});

watch(() => props.page, renderPage);
watch(() => props.data, renderPage);

onUnmounted(() => {
    window.removeEventListener("resize", renderPage);
});
</script>

<template>
    <div class="page-renderer" ref="containerRef">
        <canvas ref="canvasRef"></canvas>
    </div>
</template>

<style scoped>
.page-renderer {
    width: 100%;
    height: 100%;
    overflow: hidden;
    display: flex;
    justify-content: center;
    align-items: center;
}
</style>
