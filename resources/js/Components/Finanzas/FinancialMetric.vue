<script setup lang="ts">
import UiIcon from '../Ui/UiIcon.vue';

defineProps<{ label: string; value: string; icon: string; description?: string; prominent?: boolean }>();
</script>

<template>
    <div class="financial-metric" :class="{ 'financial-metric--prominent': prominent, 'financial-metric--long': value.length > 12 }">
        <div class="financial-metric__label"><span>{{ label }}</span><UiIcon :name="icon" :size="18" /></div>
        <p class="financial-metric__value">{{ value }}</p>
        <p v-if="description" class="financial-metric__description">{{ description }}</p>
    </div>
</template>

<style scoped>
.financial-metric { min-width: 0; container-type: inline-size; padding: 16px; border: 1px solid var(--p-border); border-radius: var(--p-radius-panel); background: var(--p-surface); }
.financial-metric--prominent { border-top: 3px solid var(--p-brand-ink); padding-top: 14px; }
.financial-metric__label { display: flex; justify-content: space-between; align-items: center; gap: 12px; color: var(--p-muted); font-size: 13px; font-weight: 500; }
.financial-metric__value { margin: 12px 0 0; font-size: clamp(22px, 11cqi, 30px); font-weight: 600; letter-spacing: -.7px; line-height: 1.3; white-space: nowrap; font-variant-numeric: tabular-nums; }
.financial-metric--long .financial-metric__value { font-size: clamp(18px, 8.5cqi, 28px); letter-spacing: -.5px; }
.financial-metric__description { margin: 8px 0 0; font-size: 12px; color: var(--p-muted); line-height: 1.5; }
@media (max-width: 599.98px) {
    .financial-metric:not(.financial-metric--prominent):not(.financial-metric--long) { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 6px 12px; align-items: center; }
    .financial-metric:not(.financial-metric--prominent):not(.financial-metric--long) .financial-metric__label { font-size: 12px; }
    .financial-metric:not(.financial-metric--prominent):not(.financial-metric--long) .financial-metric__label .p-icon { display: none; }
    .financial-metric:not(.financial-metric--prominent):not(.financial-metric--long) .financial-metric__value { margin: 0; font-size: 22px; }
    .financial-metric:not(.financial-metric--prominent):not(.financial-metric--long) .financial-metric__description { grid-column: 1 / -1; margin: 0; }
}
@media (min-width: 600px) { .financial-metric { padding: 20px; } .financial-metric--prominent { padding-top: 18px; } }
</style>
