<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Pencil, Power } from '@lucide/vue';

defineProps<{ name: string; active: boolean; editHref?: string; hideToggle?: boolean; labels?: boolean }>();
defineEmits<{ edit: []; toggle: [] }>();
</script>

<template>
    <div class="admin-actions" :class="{ 'admin-actions--labels': labels }">
        <component :is="editHref ? Link : 'button'" :href="editHref || undefined" :type="editHref ? undefined : 'button'" class="admin-action" :aria-label="`Editar ${name}`" title="Editar" @click="!editHref && $emit('edit')">
            <Pencil :size="16" :stroke-width="1.75" aria-hidden="true" /><span v-if="labels">Editar</span>
        </component>
        <button v-if="!hideToggle" type="button" class="admin-action" :aria-label="`${active ? 'Desactivar' : 'Activar'} ${name}`" :title="active ? 'Desactivar' : 'Activar'" @click="$emit('toggle')">
            <Power :size="16" :stroke-width="1.75" aria-hidden="true" /><span v-if="labels">{{ active ? 'Desactivar' : 'Activar' }}</span>
        </button>
    </div>
</template>

<style scoped>
.admin-actions { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: 8px; }
.admin-action { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; min-width: 44px; padding: 10px; border: 1px solid var(--p-border); border-radius: var(--p-radius-control); background: var(--p-surface); color: var(--p-muted); font: inherit; font-size: 12px; font-weight: 500; line-height: 1.5; text-decoration: none; cursor: pointer; }
.admin-action svg { flex-shrink: 0; }
.admin-action:hover { color: var(--p-text); border-color: #858a94; background: var(--p-muted-surface); }
.admin-action:active { box-shadow: inset 0 1px 2px #20212626; }
.admin-actions:not(.admin-actions--labels) { flex-wrap: nowrap; }
.admin-actions--labels { justify-content: flex-start; }
.admin-actions--labels .admin-action { flex: 1 1 auto; }
</style>
