<script setup lang="ts">
defineProps<{ title: string; description?: string }>();
</script>
<template>
    <div class="sa-heading">
        <div>
            <h1>{{ title }}</h1>
            <p v-if="description">{{ description }}</p>
        </div>
        <div v-if="$slots.default" class="sa-heading-actions"><slot /></div>
    </div>
</template>
