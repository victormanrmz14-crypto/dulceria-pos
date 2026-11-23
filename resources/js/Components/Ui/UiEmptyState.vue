<script setup>
import UiIcon from './UiIcon.vue';
defineProps({ icon: { type: String, default: 'inbox' }, title: String, description: String });
</script>

<template>
    <div class="p-empty">
        <span class="p-empty__icon"><UiIcon :name="icon" :size="20" /></span>
        <div class="p-empty__copy">
            <p class="p-empty__title">{{ title }}</p>
            <p v-if="description" class="p-empty__description">{{ description }}</p>
        </div>
        <slot />
    </div>
</template>
