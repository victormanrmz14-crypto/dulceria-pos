<script setup lang="ts">
import UiEmptyState from '../Ui/UiEmptyState.vue';

defineProps<{ items: { label: string; quantity: string; value: string }[] }>();
</script>

<template>
    <ol v-if="items.length" class="analytics-ranking" role="list" aria-label="Clasificación de productos más vendidos">
        <li v-for="(item, index) in items" :key="index" class="analytics-ranking__item">
            <span class="analytics-ranking__position" aria-hidden="true">{{ index + 1 }}</span>
            <div class="analytics-ranking__body" :class="{ 'analytics-ranking__body--long': item.value.length > 12 }"><p class="analytics-ranking__name">{{ item.label }}</p><p class="analytics-ranking__quantity">{{ item.quantity }}</p><p class="analytics-ranking__value">{{ item.value }}</p></div>
        </li>
    </ol>
    <UiEmptyState v-else icon="package" title="Sin productos vendidos" description="Aquí aparecerán los productos con actividad en el período seleccionado." />
</template>

<style scoped>
.analytics-ranking { list-style: none; display: grid; grid-template-columns: minmax(0, 1fr); margin: 0; padding: 0 16px; }
.analytics-ranking__item { display: grid; grid-template-columns: 24px minmax(0, 1fr); gap: 12px; padding: 16px 0; border-bottom: 1px solid var(--p-border); min-width: 0; }
.analytics-ranking__item:last-child { border-bottom: 0; }
.analytics-ranking__position { display: grid; place-items: center; align-self: start; width: 24px; height: 24px; border-radius: var(--p-radius-control); background: var(--p-muted-surface); color: var(--p-muted); font-size: 12px; font-variant-numeric: tabular-nums; }
.analytics-ranking__body { min-width: 0; display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 4px 12px; align-items: center; }
.analytics-ranking__name, .analytics-ranking__quantity { grid-column: 1; }
.analytics-ranking__body--long { grid-template-columns: minmax(0, 1fr); }
.analytics-ranking__name { margin: 0; color: var(--p-text); font-size: 13px; font-weight: 500; line-height: 1.6; overflow-wrap: anywhere; }
.analytics-ranking__quantity { margin: 0; color: var(--p-muted); font-size: 12px; overflow-wrap: anywhere; }
.analytics-ranking__value { grid-column: 2; grid-row: 1 / span 2; margin: 0; font-size: 14px; font-weight: 600; font-variant-numeric: tabular-nums; white-space: nowrap; }
.analytics-ranking__body--long .analytics-ranking__value { grid-column: 1; grid-row: auto; margin-top: 4px; }
@media (min-width: 768px) and (max-width: 1199.98px) { .analytics-ranking { grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 24px; padding-inline: 20px; } .analytics-ranking__item:last-child { border-bottom: 1px solid var(--p-border); } }
@media (min-width: 1200px) {
    .analytics-ranking { grid-template-columns: repeat(5, minmax(0, 1fr)); padding: 20px 0; }
    .analytics-ranking__body { display: block; }
    .analytics-ranking__quantity { margin-top: 4px; }
    .analytics-ranking__body--long .analytics-ranking__value { margin-top: 12px; }
    .analytics-ranking__item { display: block; padding: 0 20px; border-bottom: 0; border-right: 1px solid var(--p-border); }
    .analytics-ranking__item:last-child { border-right: 0; }
    .analytics-ranking__position { margin-bottom: 12px; }
    .analytics-ranking__value { margin-top: 12px; }
    .analytics-ranking__item:only-child { grid-column: 1 / -1; display: grid; border-right: 0; }
    .analytics-ranking__item:only-child .analytics-ranking__body { display: grid; }
    .analytics-ranking__item:only-child .analytics-ranking__position { margin-bottom: 0; }
    .analytics-ranking__item:only-child .analytics-ranking__value { margin-top: 0; }
}
</style>
