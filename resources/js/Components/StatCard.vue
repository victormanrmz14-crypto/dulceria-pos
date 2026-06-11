<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    etiqueta: String,
    valor: [String, Number],
    sufijo: { type: String, default: '' },
    color: { type: String, default: '#8B0000' },
    href: { type: String, default: null },
});
</script>

<template>
    <component
        :is="href ? Link : 'div'"
        :href="href"
        :class="href ? 'stat-card-link' : ''"
    >
        <div class="card stat-card h-100" :style="{ '--stat-color': color }">
            <div class="card-body py-3">
                <p class="text-secondary small mb-1">{{ etiqueta }}</p>
                <p class="valor mb-0">{{ valor }}</p>
                <p v-if="sufijo" class="text-secondary small mb-0 mt-1">{{ sufijo }}</p>
                <slot />
            </div>
        </div>
    </component>
</template>
