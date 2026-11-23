<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Pencil, Mail, Power } from '@lucide/vue';

interface ProductActionData { id: number; nombre: string; activo: boolean; stock_bajo: boolean; proveedor: string | null }
defineProps<{ producto: ProductActionData; labels?: boolean }>();
const emit = defineEmits<{ toggle: [producto: ProductActionData]; notify: [producto: ProductActionData] }>();
</script>

<template>
    <div class="product-actions" :class="{ 'product-actions--labels': labels }">
        <Link :href="`/productos/${producto.id}/edit`" class="product-action" :aria-label="`Editar ${producto.nombre}`" title="Editar">
            <Pencil :size="16" :stroke-width="1.75" aria-hidden="true" /><span v-if="labels">Editar</span>
        </Link>
        <button v-if="producto.stock_bajo && producto.proveedor" type="button" class="product-action"
            :aria-label="`Notificar proveedor de ${producto.nombre}`" title="Notificar proveedor" @click="emit('notify', producto)">
            <Mail :size="16" :stroke-width="1.75" aria-hidden="true" /><span v-if="labels">Notificar proveedor</span>
        </button>
        <button type="button" class="product-action" :class="{ 'product-action--deactivate': producto.activo }"
            :aria-label="`${producto.activo ? 'Desactivar' : 'Activar'} ${producto.nombre}`"
            :title="producto.activo ? 'Desactivar' : 'Activar'" @click="emit('toggle', producto)">
            <Power :size="16" :stroke-width="1.75" aria-hidden="true" /><span v-if="labels">{{ producto.activo ? 'Desactivar' : 'Activar' }}</span>
        </button>
    </div>
</template>

<style scoped>
.product-actions { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: 8px; }
.product-action { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-width: 44px; min-height: 44px; padding: 10px; border: 1px solid var(--p-border); border-radius: var(--p-radius-control); background: var(--p-surface); color: var(--p-muted); font: inherit; font-size: 12px; font-weight: 500; line-height: 1.5; text-decoration: none; cursor: pointer; }
.product-action svg { flex-shrink: 0; }
.product-action:hover { background: var(--p-muted-surface); color: var(--p-text); border-color: #858a94; }
.product-action--deactivate:hover { color: var(--p-danger); }
.product-action:active { box-shadow: inset 0 1px 2px #20212626; }
.product-actions--labels { justify-content: flex-start; }
</style>
