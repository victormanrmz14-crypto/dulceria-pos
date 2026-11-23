<script setup lang="ts">
import { useId } from 'vue';
import UiIcon from './UiIcon.vue';

defineProps<{ etiqueta: string; error?: string | null; requerido?: boolean }>();
const id = useId();
</script>

<template>
    <div class="p-field" :class="{ 'p-field--error': error }">
        <label :for="id" class="p-field__label">{{ etiqueta }} <span v-if="requerido" aria-hidden="true" class="p-field__required">*</span></label>
        <slot :control="{ id, 'aria-invalid': error ? 'true' : undefined, 'aria-describedby': error ? `${id}-error` : undefined }" />
        <p v-if="error" :id="`${id}-error`" class="p-field__error" role="alert"><UiIcon name="warning" :size="14" />{{ error }}</p>
    </div>
</template>

<style scoped>
.p-field { min-width: 0; }
.p-field__label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: var(--p-text); }
.p-field__required { color: var(--p-danger); }
.p-field :deep(.p-field-control) { width: 100%; min-width: 0; min-height: 46px; padding: 10px 12px; border: 1px solid #858a94; border-radius: var(--p-radius-control); background: var(--p-surface); color: var(--p-text); font: inherit; font-size: 16px; line-height: 1.5; }
.p-field :deep(select.p-field-control) { padding-right: 28px; }
.p-field :deep(.p-field-control:focus-visible) { outline: 2px solid var(--p-brand-ink); outline-offset: 2px; }
.p-field :deep(.p-field-control[aria-invalid="true"]) { border-color: var(--p-danger); }
.p-field :deep(.p-field-control:disabled) { background: var(--p-muted-surface); color: var(--p-muted); cursor: not-allowed; }
.p-field__error { display: flex; align-items: flex-start; gap: 6px; margin: 8px 0 0; color: var(--p-danger); font-size: 12px; line-height: 1.5; overflow-wrap: anywhere; }
.p-field__error .p-icon { margin-top: 2px; }
</style>
