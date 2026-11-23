<script setup>
import { Link } from '@inertiajs/vue3';
import UiIcon from './Ui/UiIcon.vue';

defineProps({
    etiqueta: String,
    valor: [String, Number],
    sufijo: { type: String, default: '' },
    color: { type: String, default: '#8B0000' },
    href: { type: String, default: null },
    premium: { type: Boolean, default: false },
    icon: { type: String, default: 'chart' },
    attention: Boolean,
});
</script>

<template>
    <component v-if="premium" :is="href ? Link : 'div'" :href="href || undefined"
        class="p-metric" :class="{ 'p-metric--link': href, 'p-metric--attention': attention, 'p-metric--long-value': String(valor).length > 12 }">
        <div class="p-metric__top"><span>{{ etiqueta }}</span><UiIcon :name="icon" :size="18" /></div>
        <p class="p-metric__value">{{ valor }}</p>
        <div class="p-metric__bottom"><span>{{ sufijo }}</span><UiIcon v-if="href" name="arrow" :size="15" /></div>
    </component>
    <component
        v-else
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
