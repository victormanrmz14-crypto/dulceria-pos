<script setup lang="ts">
import UiButton from '../Ui/UiButton.vue';
import { ArrowLeft } from '@lucide/vue';
defineProps<{ title: string; description: string; backHref: string }>();
</script>

<template>
    <div class="admin-editor">
        <div class="p-page-heading admin-editor__heading"><div><h1 class="p-page-title">{{ title }}</h1><p class="admin-editor__description">{{ description }}</p></div><UiButton :href="backHref" variant="secondary"><ArrowLeft :size="16" :stroke-width="1.75" aria-hidden="true" />Volver</UiButton></div>
        <div class="p-panel"><slot /></div>
    </div>
</template>

<style scoped>
.admin-editor { max-width: 1040px; margin-inline: auto; min-width: 0; }
.admin-editor__heading { align-items: flex-start; gap: 16px; }
.admin-editor__description { margin: 8px 0 0; color: var(--p-muted); font-size: 13px; line-height: 1.6; max-width: 62ch; }
.admin-editor__heading > :deep(.p-button) { flex-shrink: 0; }
</style>
