<script setup lang="ts">
import UiButton from '../Ui/UiButton.vue';
import { ArrowLeft } from '@lucide/vue';
defineProps<{ title: string; description: string }>();
</script>

<template>
    <div class="product-editor">
        <div class="p-page-heading product-editor-heading">
            <div><h1 class="p-page-title">{{ title }}</h1><p class="product-editor-description">{{ description }}</p></div>
            <UiButton href="/productos" variant="secondary"><ArrowLeft :size="16" :stroke-width="1.75" aria-hidden="true" />Volver</UiButton>
        </div>
        <div class="p-panel"><slot /></div>
    </div>
</template>

<style scoped>
.product-editor { max-width: 1040px; margin-inline: auto; }
.product-editor-heading { align-items: flex-start; gap: 16px; }
.product-editor-description { margin: 8px 0 0; color: var(--p-muted); font-size: 13px; max-width: 62ch; }
.product-editor-heading > .p-button { flex-shrink: 0; }
@media (max-width: 767.98px) { .product-editor-heading > .p-button { min-height: 44px; } }
</style>
