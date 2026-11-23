<script setup>
import { Link } from '@inertiajs/vue3';

// Recibe el arreglo `links` del paginador de Laravel
defineProps({
    links: { type: Array, required: true },
    premium: { type: Boolean, default: false },
});
</script>

<template>
    <nav v-if="links.length > 3 && !premium" aria-label="Paginación">
        <ul class="pagination pagination-sm justify-content-center flex-wrap mb-0">
            <li
                v-for="(link, i) in links"
                :key="i"
                class="page-item"
                :class="{ active: link.active, disabled: !link.url }"
            >
                <Link
                    v-if="link.url"
                    class="page-link"
                    :href="link.url"
                    v-html="link.label"
                    preserve-scroll
                />
                <span v-else class="page-link" v-html="link.label" />
            </li>
        </ul>
    </nav>
    <nav v-else-if="links.length > 3 && premium" class="p-pagination" aria-label="Paginación">
        <template v-for="(link, i) in links" :key="i">
            <Link v-if="link.url" :href="link.url" class="p-pagination__item" :class="{ 'is-active': link.active }"
                :aria-current="link.active ? 'page' : undefined" preserve-scroll v-html="link.label" />
            <span v-else class="p-pagination__item is-disabled" aria-disabled="true" v-html="link.label" />
        </template>
    </nav>
</template>

<style scoped>
.p-pagination { display: flex; justify-content: center; flex-wrap: wrap; gap: 8px; }
.p-pagination__item { display: inline-flex; align-items: center; justify-content: center; min-width: 44px; min-height: 44px; padding: 10px 12px; border: 1px solid var(--p-border); border-radius: var(--p-radius-control); background: var(--p-surface); color: var(--p-muted); font: inherit; font-size: 12px; font-weight: 500; line-height: 1.5; text-decoration: none; }
.p-pagination__item:hover { background: var(--p-muted-surface); color: var(--p-text); }
.p-pagination__item.is-active { background: var(--p-brand); color: var(--p-on-brand); border-color: var(--p-brand-ink); font-weight: 600; }
.p-pagination__item.is-disabled { color: var(--p-muted); background: var(--p-canvas); }
</style>
