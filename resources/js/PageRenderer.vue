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

const scale = ref(1); // scale을 ref로 관리

const emit = defineEmits(["questionClicked", "updateData"]); // updateData 추가

const canvasRef = ref(null);
const containerRef = ref(null);
let fabricCanvas = null;
let isDrawing = false;
let startPoint = null;
let activeRect = null;

const handleRectClick = (rectData) => {
    emit("questionClicked", rectData);
};

const createRect = (rectData, isNew = false) => {
    const scaledRect = new fabric.Rect({
        left: rectData.x * scale.value,
        top: rectData.y * scale.value,
        width: rectData.width * scale.value,
        height: rectData.height * scale.value,
        fill: "#000000",
        opacity: 0.25,
    });

    scaledRect.set("originalData", rectData);
    scaledRect.on("mousedown", () => {
        handleRectClick({
            ...rectData,
            id: `${props.page.number}_${rectData.x}_${rectData.y}_${rectData.width}_${rectData.height}`,
        });
    });

    // 변경 이벤트들을 모두 처리
    const updateRectData = () => {
        const updatedRect = {
            x: Math.round(scaledRect.left / scale.value),
            y: Math.round(scaledRect.top / scale.value),
            width: Math.round(scaledRect.getScaledWidth() / scale.value),
            height: Math.round(scaledRect.getScaledHeight() / scale.value),
        };
        scaledRect.set("originalData", updatedRect);

        const updatedData = props.data.map((item) => {
            if (item === rectData) {
                return updatedRect;
            }
            return item;
        });

        emit("updateData", updatedData);
    };

    // 이동, 크기 조절 등의 이벤트에 대한 리스너
    scaledRect.on("moved", updateRectData);
    scaledRect.on("scaled", updateRectData);
    scaledRect.on("modified", updateRectData);

    fabricCanvas.add(scaledRect);
    createDeleteButton(scaledRect);

    if (isNew) {
        emit("updateData", [...props.data, rectData]);
    }

    return scaledRect;
};

const createDeleteButton = (rect) => {
    const circlePath = new fabric.Path(
        "M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Z",
        {
            width: 24,
            height: 24,
            fill: "#FF0000",
            selectable: false,
            evented: true,
            opacity: 1,
        }
    );

    // X 표시
    const xPath = new fabric.Path(
        "M10.28 9.22a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z",
        {
            width: 24,
            height: 24,
            fill: "#FFFFFF",
            selectable: false,
            evented: false,
            opacity: 1,
        }
    );

    // 두 path를 그룹으로 만듦
    const deleteIcon = new fabric.Group([circlePath, xPath], {
        width: 24,
        height: 24,
        selectable: false,
        evented: true,
        opacity: 1,
    });

    // rect와 deleteIcon을 그룹으로 관리
    const updateDeleteButtonPosition = () => {
        const rectCenter = rect.getCenterPoint();
        const rectCoords = rect.getCoords();

        // 우측 상단 모서리 위치 계산
        deleteIcon.set({
            left: rectCoords[1].x - 10, // 우측 상단 x좌표
            top: rectCoords[1].y - 20, // 우측 상단 y좌표
            // scaleX: 1 / rect.scaleX,
            // scaleY: 1 / rect.scaleY,
        });

        deleteIcon.setCoords();
        fabricCanvas.renderAll();
    };

    // 초기 위치 설정
    updateDeleteButtonPosition();
    deleteIcon.set({
        left: rect.left + rect.width - 10,
        top: rect.top - 20,
    });
    fabricCanvas.renderAll();

    // rect 변경 이벤트에 대한 리스너
    rect.on("moving", updateDeleteButtonPosition);
    rect.on("scaling", updateDeleteButtonPosition);
    rect.on("rotating", updateDeleteButtonPosition);

    // hover 효과
    rect.on("mouseover", () => {
        if (!deleteIcon) return;
        deleteIcon.set("opacity", 1);
        fabricCanvas.renderAll();
    });

    rect.on("mouseout", () => {
        if (!deleteIcon) return;
        deleteIcon.set("opacity", 1);
        fabricCanvas.renderAll();
    });

    // 삭제 이벤트

    const handleDelete = () => {
        fabricCanvas.remove(rect);
        fabricCanvas.remove(deleteIcon);
        fabricCanvas.renderAll();

        // 삭제된 rect의 데이터를 제외하고 부모에게 알림
        const updatedData = props.data.filter((item) => {
            return !(
                item.x === rect.originalData.x &&
                item.y === rect.originalData.y &&
                item.width === rect.originalData.width &&
                item.height === rect.originalData.height
            );
        });
        emit("updateData", updatedData);
    };

    deleteIcon.on("mousedown", handleDelete);
    deleteIcon.on("touchstart", handleDelete);

    // Canvas에 추가
    fabricCanvas.add(deleteIcon);
    deleteIcon.bringToFront();
};

const initializeDragDrawing = () => {
    fabricCanvas.on("mouse:down", (options) => {
        if (options.target) return;

        isDrawing = true;
        const pointer = fabricCanvas.getPointer(options.e);
        startPoint = pointer;

        activeRect = new fabric.Rect({
            left: pointer.x,
            top: pointer.y,
            width: 0,
            height: 0,
            fill: "#000000",
            opacity: 0.25,
        });

        fabricCanvas.add(activeRect);
    });

    fabricCanvas.on("mouse:move", (options) => {
        if (!isDrawing) return;

        const pointer = fabricCanvas.getPointer(options.e);

        if (startPoint.x > pointer.x) {
            activeRect.set({ left: pointer.x });
        }
        if (startPoint.y > pointer.y) {
            activeRect.set({ top: pointer.y });
        }

        activeRect.set({
            width: Math.abs(pointer.x - startPoint.x),
            height: Math.abs(pointer.y - startPoint.y),
        });

        fabricCanvas.renderAll();
    });

    fabricCanvas.on("mouse:up", () => {
        isDrawing = false;
        if (activeRect) {
            if (activeRect.width < 10 || activeRect.height < 10) {
                fabricCanvas.remove(activeRect);
                fabricCanvas.renderAll();
                activeRect = null;
                return;
            }

            const originalRect = {
                x: Math.round(activeRect.left / scale.value),
                y: Math.round(activeRect.top / scale.value),
                width: Math.round(activeRect.width / scale.value),
                height: Math.round(activeRect.height / scale.value),
            };

            // 기존 activeRect 제거하고 새로운 rect로 교체
            fabricCanvas.remove(activeRect);
            createRect(originalRect, true);
        }
        activeRect = null;
    });
};

const renderPage = () => {
    if (!fabricCanvas || !containerRef.value) return;
    fabricCanvas.clear();

    fabric.Image.fromURL(props.page.url, (img) => {
        const containerWidth = containerRef.value.clientWidth;
        const containerHeight = 900;
        scale.value = Math.min(
            containerWidth / img.width,
            containerHeight / img.height
        );
        const scaledWidth = img.width * scale.value;
        const scaledHeight = img.height * scale.value;

        fabricCanvas.setWidth(scaledWidth);
        fabricCanvas.setHeight(scaledHeight);
        fabricCanvas.setBackgroundImage(
            img,
            fabricCanvas.renderAll.bind(fabricCanvas),
            {
                scaleX: scale.value,
                scaleY: scale.value,
            }
        );

        props.data.forEach((rectData) => {
            createRect(rectData);
            // const scaledRect = new fabric.Rect({
            //     left: rectData.x * scale.value,
            //     top: rectData.y * scale.value,
            //     width: rectData.width * scale.value,
            //     height: rectData.height * scale.value,
            //     fill: "#000000",
            //     opacity: 0.25,
            // });

            // scaledRect.set("originalData", rectData);
            // scaledRect.on("mousedown", () => {
            //     handleRectClick({
            //         ...rectData,
            //         id: `${props.page.number}_${rectData.x}_${rectData.y}_${rectData.width}_${rectData.height}`,
            //     });
            // });

            // fabricCanvas.add(scaledRect);
            // createDeleteButton(scaledRect);
        });

        fabricCanvas.renderAll();
    });
};

onMounted(() => {
    fabricCanvas = new fabric.Canvas(canvasRef.value);
    initializeDragDrawing();
    renderPage();
    window.addEventListener("resize", renderPage);
});

watch(() => props.page, renderPage);
watch(() => props.data, renderPage);

onUnmounted(() => {
    window.removeEventListener("resize", renderPage);
    if (fabricCanvas) {
        fabricCanvas.dispose();
    }
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
