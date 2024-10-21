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

const emit = defineEmits(["questionClicked"]);

const canvasRef = ref(null);
const containerRef = ref(null);
let fabricCanvas = null;

const handleRectClick = (rectData) => {
    emit("questionClicked", rectData);
};

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
        props.data.forEach((rectData) => {
            const scaledRect = new fabric.Rect({
                left: rectData.x * scale,
                top: rectData.y * scale,
                width: rectData.width * scale,
                height: rectData.height * scale,
                fill: "#000000",
                opacity: 0.25,
            });
            scaledRect.set("originalData", rectData);
            scaledRect.on("mousedown", () => {
                handleRectClick({
                    ...rectData,
                    // id is page number x y width height
                    id: `${props.page.number}_${rectData.x}_${rectData.y}_${rectData.width}_${rectData.height}`,
                });
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
