<script setup>
import { Link } from '@inertiajs/vue3';

// Recibe el arreglo `links` del paginador de Laravel
defineProps({
    links: { type: Array, required: true },
});
</script>

<template>
    <nav v-if="links.length > 3" aria-label="Paginación">
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
</template>
