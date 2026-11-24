<script setup lang="ts">
import { computed, ref, watch } from 'vue';

const props = withDefaults(defineProps<{ name: string; src?: string | null; size?: number; decorative?: boolean }>(), { size: 32, decorative: true });
const failed = ref(false);
watch(() => props.src, () => { failed.value = false; });
const initials = computed(() => {
    const words = props.name.trim().split(/\s+/).filter(Boolean);
    return (words.length > 1 ? [words[0], words[words.length - 1]] : words)
        .map(word => Array.from(word)[0]).join('').toLocaleUpperCase('es-MX') || 'U';
});
</script>

<template>
    <span class="user-avatar" :style="{ '--avatar-size': `${size}px` }" :aria-hidden="decorative || undefined"
        :role="decorative ? undefined : 'img'" :aria-label="decorative ? undefined : name">
        <img v-if="src && !failed" :src="src" alt="" :width="size" :height="size" @error="failed = true" />
        <span v-else>{{ initials }}</span>
    </span>
</template>

<style scoped>
.user-avatar { display: inline-grid; place-items: center; flex-shrink: 0; width: var(--avatar-size); height: var(--avatar-size); border-radius: 50%; overflow: hidden; background: #edf0f4; color: #4d5563; font-size: calc(var(--avatar-size) * .34); font-weight: 600; line-height: 1; }
.user-avatar img { display: block; width: var(--avatar-size); height: var(--avatar-size); min-width: 0; min-height: 0; object-fit: cover; }
</style>
