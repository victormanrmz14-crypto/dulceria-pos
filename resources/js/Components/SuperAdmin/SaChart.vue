<script setup lang="ts">
import { computed } from "vue";
import Grafica from "../Grafica.vue";
const props = defineProps<{
    title: string;
    description: string;
    rows: Array<{ fecha: string; [key: string]: number | string }>;
    field: string;
    label: string;
    currency?: boolean;
}>();
const value = (n: number | string) =>
    new Intl.NumberFormat(
        "es-MX",
        props.currency ? { style: "currency", currency: "MXN" } : {},
    ).format(Number(n));
const date = (s: string) =>
    new Intl.DateTimeFormat("es-MX", {
        day: "numeric",
        month: "short",
        timeZone: "UTC",
    }).format(new Date(s + "T12:00:00Z"));
const data = computed(() => ({
    labels: props.rows.map((d) => date(d.fecha)),
    datasets: [
        {
            label: props.label,
            data: props.rows.map((d) => Number(d[props.field])),
            borderColor: "#960A2C",
            backgroundColor: "#F8EFF1",
            borderWidth: 2,
            pointRadius: 2,
            fill: true,
            tension: 0,
        },
    ],
}));
const hasData = computed(() =>
    props.rows.some((d) => Number(d[props.field]) !== 0),
);
const options = computed(() => ({
    animation: false,
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: { label: (ctx) => props.label + ": " + value(ctx.raw) },
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                color: "#666973",
                precision: props.currency ? 2 : 0,
                callback: (n) =>
                    props.currency
                        ? new Intl.NumberFormat("es-MX", {
                              notation: "compact",
                              style: "currency",
                              currency: "MXN",
                          }).format(n)
                        : n,
            },
            grid: { color: "#EDEEF1" },
        },
        x: {
            ticks: { color: "#666973", maxTicksLimit: 6, maxRotation: 0 },
            grid: { display: false },
        },
    },
}));
</script>
<template>
    <section class="sa-panel sa-chart">
        <header>
            <h2>{{ title }}</h2>
            <p>{{ description }}</p>
        </header>
        <div
            v-if="hasData"
            class="sa-chart-canvas"
            role="img"
            :aria-label="title + '. Datos disponibles en la tabla inferior.'"
        >
            <Grafica :datos="data" :opciones="options" :alto="210" />
        </div>
        <p v-else class="sa-empty">
            Sin {{ label.toLowerCase() }} durante este período.
        </p>
        <details class="sa-chart-data">
            <summary>Consultar datos de la gráfica</summary>
            <table>
                <caption>
                    {{ label }}
                    por día
                </caption>
                <thead>
                    <tr>
                        <th scope="col">Fecha</th>
                        <th scope="col">{{ label }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in rows" :key="row.fecha">
                        <th scope="row">{{ row.fecha }}</th>
                        <td>{{ value(row[field]) }}</td>
                    </tr>
                </tbody>
            </table>
        </details>
    </section>
</template>
