<script setup lang="ts">
import UiIcon from '../Ui/UiIcon.vue';
withDefaults(defineProps<{ active: boolean; activeLabel?: string; inactiveLabel?: string }>(), { activeLabel: 'Activo', inactiveLabel: 'Inactivo' });
</script>

<template>
    <span class="admin-status" :class="{ 'admin-status--active': active }"><UiIcon :name="active ? 'success' : 'close'" :size="14" />{{ active ? activeLabel : inactiveLabel }}</span>
</template>

<style scoped>
.admin-status { display: inline-flex; align-items: center; gap: 6px; padding: 4px 8px; border: 1px solid var(--p-border); border-radius: var(--p-radius-control); background: var(--p-canvas); color: var(--p-muted); font-size: 12px; font-weight: 500; line-height: 1.5; white-space: nowrap; }
.admin-status--active { color: var(--p-success); }
</style>
