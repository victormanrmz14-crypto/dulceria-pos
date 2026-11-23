<script setup lang="ts">
import UiIcon from '../Ui/UiIcon.vue';
import { numero } from '../../utils';
defineProps<{ stock: number; minimum: number; unit: string; low: boolean }>();
</script>

<template>
    <div class="product-stock">
        <span class="product-stock__value">{{ numero(stock) }} <span>{{ unit }}</span></span>
        <span v-if="low" class="product-stock__warning"><UiIcon name="warning" :size="13" />Stock bajo</span>
        <span v-if="low" class="product-stock__minimum">Mínimo: {{ numero(minimum) }}</span>
    </div>
</template>

<style scoped>
.product-stock { display: flex; flex-direction: column; gap: 4px; }
.product-stock__value { font-size: 13px; font-weight: 600; font-variant-numeric: tabular-nums; white-space: nowrap; }
.product-stock__value > span { font-size: 12px; font-weight: 400; color: var(--p-muted); }
.product-stock__warning { display: inline-flex; align-items: center; gap: 4px; color: var(--p-warning); font-size: 12px; font-weight: 500; }
.product-stock__minimum { font-size: 12px; color: var(--p-muted); font-variant-numeric: tabular-nums; overflow-wrap: anywhere; }
</style>
