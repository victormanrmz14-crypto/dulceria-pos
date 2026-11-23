<script setup>
import { Link } from '@inertiajs/vue3';
import UiIcon from './UiIcon.vue';

/** items: role-filtered destinations from tenantNavigation; current is exact. */
defineProps({
    items: { type: Array, required: true },
    drawerOpen: Boolean,
    drawerId: { type: String, required: true },
    suppressed: Boolean,
});
defineEmits(['open-menu']);
</script>

<template>
    <nav class="pos-premium p-bottom-nav" :class="{ 'is-suppressed': suppressed }"
        :inert="suppressed" :aria-hidden="suppressed ? 'true' : undefined" aria-label="Navegación móvil">
        <template v-for="item in items" :key="item.id">
            <button v-if="item.id === 'menu'" type="button" data-tenant-menu-trigger class="p-bottom-nav__item"
                :class="{ 'is-active': item.active }" :aria-expanded="drawerOpen"
                :aria-controls="drawerId" aria-haspopup="dialog" @click="$emit('open-menu')">
                <span class="p-bottom-nav__icon"><UiIcon :name="item.icon" :size="21" /></span>
                <span>{{ item.label }}</span>
                <span v-if="item.active" class="visually-hidden">, sección actual</span>
            </button>
            <Link v-else :href="item.href" class="p-bottom-nav__item"
                :class="{ 'is-active': item.active, 'p-bottom-nav__item--sell': item.id === 'sell' }"
                :aria-current="item.current ? 'page' : undefined">
                <span class="p-bottom-nav__icon"><UiIcon :name="item.icon" :size="21" /></span>
                <span>{{ item.label }}</span>
                <span v-if="item.active && !item.current" class="visually-hidden">, sección actual</span>
            </Link>
        </template>
    </nav>
</template>
