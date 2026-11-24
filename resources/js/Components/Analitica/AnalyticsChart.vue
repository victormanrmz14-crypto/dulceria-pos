<script setup lang="ts">
import { computed, onMounted, shallowRef } from 'vue';
import type { ChartOptions } from 'chart.js';
import Grafica from '../Grafica.vue';
import { moneda } from '../../utils';

const props = defineProps<{
    kind: 'line' | 'doughnut';
    data: { labels: string[]; datasets: { label?: string; data: number[] }[] };
    color: string;
    label: string;
}>();

// Texture keeps the second category distinguishable when tenant hues are similar.
const secondaryPattern = shallowRef<CanvasPattern | null>(null);
onMounted(() => {
    const tile = document.createElement('canvas');
    tile.width = tile.height = 6;
    const context = tile.getContext('2d');
    if (!context) return;
    context.fillStyle = '#737b8c';
    context.fillRect(0, 0, 6, 6);
    context.strokeStyle = '#ffffff';
    context.lineWidth = 1;
    context.beginPath();
    context.moveTo(-1, 1); context.lineTo(1, -1);
    context.moveTo(-1, 7); context.lineTo(7, -1);
    context.moveTo(5, 7); context.lineTo(7, 5);
    context.stroke();
    secondaryPattern.value = context.createPattern(tile, 'repeat');
});

// Only visual options change here. Labels, values and their order pass through.
const styledData = computed(() => ({
    ...props.data,
    datasets: props.data.datasets.map((dataset) => props.kind === 'line' ? {
        ...dataset, borderColor: props.color, backgroundColor: 'transparent', fill: false,
        tension: 0, borderWidth: 2, pointBackgroundColor: props.color,
        pointBorderColor: '#ffffff', pointBorderWidth: 1.5,
        pointRadius: props.data.labels.length > 31 ? 0 : 3,
        pointHoverRadius: 5, pointHitRadius: 16,
    } : {
        ...dataset, backgroundColor: [props.color, secondaryPattern.value || '#737b8c'],
        borderColor: '#ffffff', borderWidth: 2, hoverOffset: 2,
    }),
}));

const options = computed<ChartOptions<'line' | 'doughnut'>>(() => ({
    animation: false,
    font: { family: "'DM Sans', system-ui, sans-serif", size: 12 },
    interaction: { intersect: false, mode: props.kind === 'line' ? 'index' : 'nearest' },
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#202126', padding: 12, cornerRadius: 6, displayColors: false,
            titleFont: { size: 12, weight: 500 }, bodyFont: { size: 13 },
            callbacks: { label: (context) => moneda(props.kind === 'line' ? (context.parsed as { y: number }).y : context.parsed as number) },
        },
    },
    ...(props.kind === 'line' ? {
        layout: { padding: { top: 12, right: 12 } },
        scales: {
            x: {
                grid: { display: false }, border: { display: false },
                ticks: { color: '#666973', maxRotation: 0, autoSkip: true, maxTicksLimit: 5, padding: 10, font: { size: 12 } },
            },
            y: {
                beginAtZero: true, border: { display: false },
                grid: { color: '#eef0f3', drawTicks: false },
                ticks: {
                    color: '#666973', padding: 8, maxTicksLimit: 5, font: { size: 12 },
                    // Compact axis labels only; the tooltip and data list keep full currency values.
                    callback: (value) => '$' + new Intl.NumberFormat('es-MX', { notation: 'compact', maximumFractionDigits: 1 }).format(Number(value)),
                },
            },
        },
    } : { cutout: '72%' }),
}));
</script>

<template>
    <div class="analytics-chart" :class="`analytics-chart--${kind}`" role="img" :aria-label="label">
        <Grafica :tipo="kind" :datos="styledData" :opciones="options" :alto="kind === 'line' ? 220 : 152" aria-hidden="true" />
    </div>
</template>

<style scoped>
.analytics-chart { min-width: 0; width: 100%; }
.analytics-chart--doughnut { width: 152px; margin: 0 auto; }
.analytics-chart--line :deep(canvas) { max-width: 100%; }
</style>
