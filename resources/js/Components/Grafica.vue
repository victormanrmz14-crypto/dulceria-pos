<script setup>
import { onMounted, onBeforeUnmount, ref, watch } from 'vue';
import {
    Chart,
    LineController, LineElement, PointElement,
    BarController, BarElement,
    DoughnutController, ArcElement,
    CategoryScale, LinearScale, Filler, Tooltip, Legend,
} from 'chart.js';

Chart.register(
    LineController, LineElement, PointElement,
    BarController, BarElement,
    DoughnutController, ArcElement,
    CategoryScale, LinearScale, Filler, Tooltip, Legend,
);

const props = defineProps({
    tipo: { type: String, default: 'line' },
    datos: { type: Object, required: true },   // { labels: [], datasets: [] }
    opciones: { type: Object, default: () => ({}) },
    alto: { type: Number, default: 220 },
});

const canvas = ref(null);
let chart = null;

const render = () => {
    if (chart) chart.destroy();
    chart = new Chart(canvas.value, {
        type: props.tipo,
        data: props.datos,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            ...props.opciones,
        },
    });
};

onMounted(render);
watch(() => props.datos, render, { deep: true });
onBeforeUnmount(() => chart?.destroy());
</script>

<template>
    <div :style="{ height: alto + 'px', position: 'relative' }">
        <canvas ref="canvas" />
    </div>
</template>
