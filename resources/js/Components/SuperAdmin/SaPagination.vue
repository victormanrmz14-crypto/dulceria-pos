<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
defineProps<{
    paginator: {
        current_page: number;
        last_page: number;
        total: number;
        from: number | null;
        to: number | null;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
}>();
</script>
<template>
    <nav class="sa-pagination" aria-label="Paginación">
        <span>
            {{ paginator.from || 0 }}–{{ paginator.to || 0 }} de
            {{ paginator.total }} registros
        </span>
        <div>
            <Link
                v-if="paginator.prev_page_url"
                :href="paginator.prev_page_url"
                class="sa-button"
                preserve-scroll
            >
                Anterior
            </Link>
            <span>
                Página {{ paginator.current_page }} de {{ paginator.last_page }}
            </span>
            <Link
                v-if="paginator.next_page_url"
                :href="paginator.next_page_url"
                class="sa-button"
                preserve-scroll
            >
                Siguiente
            </Link>
        </div>
    </nav>
</template>
