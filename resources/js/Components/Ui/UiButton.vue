<script setup>
import { Link } from '@inertiajs/vue3';
import UiIcon from './UiIcon.vue';

defineProps({
    href: { type: String, default: null },
    variant: { type: String, default: 'primary' },
    type: { type: String, default: 'button' },
    disabled: Boolean,
    loading: Boolean,
});
</script>

<template>
    <component :is="href && !disabled && !loading ? Link : 'button'"
        :href="href && !disabled && !loading ? href : undefined"
        :type="!href || disabled || loading ? type : undefined"
        :disabled="disabled || loading || undefined" :aria-busy="loading || undefined"
        class="p-button" :class="`p-button--${variant}`">
        <UiIcon v-if="loading" name="loading" class="p-spin" />
        <slot />
    </component>
</template>
