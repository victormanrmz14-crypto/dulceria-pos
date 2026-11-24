<script setup lang="ts">
import UiOverlay from '../Ui/UiOverlay.vue';
defineProps<{ open: boolean; title: string; theme?: Record<string, string>; wide?: boolean; focusFallback?: string }>();
defineEmits<{ close: [] }>();
</script>

<template>
    <UiOverlay :open="open" :title="title" :theme="theme" :focus-fallback="focusFallback" class="admin-dialog" :class="{ 'admin-dialog--wide': wide }" @close="$emit('close')"><div class="admin-dialog__body"><slot /></div></UiOverlay>
</template>

<style scoped>
.admin-dialog { width: min(440px, calc(100% - 32px)); }
.admin-dialog--wide { width: min(520px, calc(100% - 32px)); }
.admin-dialog__body { padding: 20px; }
.admin-dialog__body :deep(.admin-dialog-form) { display: grid; gap: 20px; }
.admin-dialog__body :deep(.admin-dialog-grid) { display: grid; gap: 20px; grid-template-columns: minmax(0, 1fr); }
.admin-dialog__body :deep(.admin-dialog-actions) { display: flex; flex-wrap: wrap; gap: 8px; padding-top: 4px; }
.admin-dialog__body :deep(.admin-dialog-actions .p-button) { flex: 1 1 140px; }
.admin-dialog__body :deep(.p-button--primary:not(:disabled)) { border-color: var(--p-brand-ink); }
.admin-dialog__body :deep(textarea) { resize: vertical; }
@media (min-width: 600px) { .admin-dialog__body :deep(.admin-dialog-grid) { grid-template-columns: minmax(0, 1.2fr) minmax(0, 1fr); } }
@media (max-width: 359.98px) { .admin-dialog__body { padding: 16px; } }
</style>
