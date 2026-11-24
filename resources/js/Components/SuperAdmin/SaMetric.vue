<script setup lang="ts">
import UiIcon from "../Ui/UiIcon.vue";
defineProps<{
    label: string;
    value: string | number;
    detail: string;
    icon?: string;
}>();
</script>
<template>
    <article class="sa-metric">
        <div class="sa-metric-label">
            <span>{{ label }}</span>
            <UiIcon v-if="icon" :name="icon" :size="17" />
        </div>
        <strong>{{ value }}</strong>
        <p>{{ detail }}</p>
    </article>
</template>
