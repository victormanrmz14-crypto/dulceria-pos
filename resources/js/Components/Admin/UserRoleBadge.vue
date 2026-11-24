<script setup lang="ts">
import UiIcon from '../Ui/UiIcon.vue';
defineProps<{ role: string }>();
</script>

<template><span class="user-role"><UiIcon :name="role === 'admin' ? 'shield' : 'receipt'" :size="15" />{{ role === 'admin' ? 'Administrador' : 'Cajero' }}</span></template>

<style scoped>
.user-role { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; color: var(--p-text); }
</style>
