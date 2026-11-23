<script setup lang="ts">
import UiOverlay from '../Ui/UiOverlay.vue';

defineProps<{ open: boolean; title: string; theme?: Record<string, string>; wide?: boolean }>();
defineEmits<{ close: [] }>();
</script>

<template>
    <UiOverlay :open="open" :title="title" :theme="theme" class="cash-dialog" :class="{ 'cash-dialog--wide': wide }" @close="$emit('close')">
        <div class="cash-dialog__body"><slot /></div>
    </UiOverlay>
</template>

<style scoped>
.cash-dialog--wide { width: min(520px, calc(100% - 32px)); }
.cash-dialog__body { padding: 20px; }
.cash-dialog__body :deep(.p-button--primary:not(.cash-form__withdraw):not(:disabled)) { border-color: var(--p-brand-ink); }
.cash-dialog__body :deep(.cash-form) { display: grid; gap: 20px; }
.cash-dialog__body :deep(.cash-form__context) { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: baseline; gap: 8px 16px; background: var(--p-canvas); padding: 12px; border: 1px solid var(--p-border); border-radius: var(--p-radius-control); margin: 0; font-size: 13px; }
.cash-dialog__body :deep(.cash-form__context strong) { margin-left: auto; white-space: nowrap; font-variant-numeric: tabular-nums; }
.cash-dialog__body :deep(.cash-form__money) { text-align: right; font-variant-numeric: tabular-nums; }
.cash-dialog__body :deep(.cash-form__grid) { display: grid; gap: 20px; }
.cash-dialog__body :deep(.cash-form__actions) { display: flex; flex-wrap: wrap; gap: 8px; padding-top: 4px; }
.cash-dialog__body :deep(.cash-form__actions .p-button) { flex: 1 1 150px; }
.cash-dialog__body :deep(textarea) { resize: vertical; }
.cash-dialog__body :deep(.cash-form__withdraw:not(:disabled)) { background: var(--p-danger); border-color: var(--p-danger); color: #fff; }
.cash-dialog__body :deep(.cash-form__withdraw:hover:not(:disabled)) { background: color-mix(in srgb, var(--p-danger) 90%, black); }
@media (min-width: 520px) { .cash-dialog__body :deep(.cash-form__grid) { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 359.98px) { .cash-dialog__body { padding: 16px; } }
</style>
