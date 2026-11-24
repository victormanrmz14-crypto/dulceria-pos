<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import UiIcon from '../Ui/UiIcon.vue';
import UiButton from '../Ui/UiButton.vue';
import UiEmptyState from '../Ui/UiEmptyState.vue';
import Paginacion from '../Paginacion.vue';
import { moneda } from '../../utils';

withDefaults(defineProps<{
    sales: { id: number; folio: string; fecha: string; cajero: string; metodo_pago: string; total: number }[];
    links: { url: string | null; label: string; active: boolean }[];
    ticketAction?: boolean;
    caption?: string;
    emptyTitle?: string;
    emptyDescription?: string;
}>(), {
    ticketAction: false,
    caption: 'Ventas del período seleccionado, con acceso a su ticket',
    emptyTitle: 'Sin ventas en este período',
    emptyDescription: 'Las ventas del rango seleccionado aparecerán aquí con su ticket y cajero.',
});
</script>

<template>
    <template v-if="sales.length">
        <table class="finance-table analytics-sales-table">
            <caption class="finance-table-caption">{{ caption }}</caption>
            <thead><tr><th scope="col">Folio</th><th scope="col">Fecha</th><th scope="col">Cajero</th><th scope="col">Método</th><th scope="col" class="finance-number">Total</th><th v-if="ticketAction" scope="col" class="finance-number"><span class="visually-hidden">Acciones</span></th></tr></thead>
            <tbody>
                <tr v-for="v in sales" :key="v.id">
                    <td><Link v-if="!ticketAction" :href="`/ventas/${v.id}/ticket`" class="finance-link" :aria-label="`Ver ticket ${v.folio}`">{{ v.folio }}<UiIcon name="external" :size="14" /></Link><span v-else class="analytics-sale-folio">{{ v.folio }}</span></td>
                    <td>{{ v.fecha }}</td><td>{{ v.cajero }}</td><td><span class="finance-payment"><UiIcon :name="v.metodo_pago === 'efectivo' ? 'cash' : 'card'" :size="16" />{{ v.metodo_pago === 'efectivo' ? 'Efectivo' : 'Tarjeta' }}</span></td><td class="finance-number finance-money">{{ moneda(v.total) }}</td><td v-if="ticketAction"><UiButton :href="`/ventas/${v.id}/ticket`" variant="secondary" :aria-label="`Ver ticket ${v.folio}`">Ver ticket</UiButton></td>
                </tr>
            </tbody>
        </table>
        <div class="finance-cards analytics-sales-cards">
            <article v-for="v in sales" :key="v.id" class="finance-record">
                <div class="finance-record__heading"><Link v-if="!ticketAction" :href="`/ventas/${v.id}/ticket`" class="finance-link" :aria-label="`Ver ticket ${v.folio}`">{{ v.folio }}<UiIcon name="external" :size="14" /></Link><span v-else class="analytics-sale-folio">{{ v.folio }}</span><span class="finance-payment"><UiIcon :name="v.metodo_pago === 'efectivo' ? 'cash' : 'card'" :size="16" />{{ v.metodo_pago === 'efectivo' ? 'Efectivo' : 'Tarjeta' }}</span></div>
                <dl class="finance-pairs">
                    <div class="finance-pair"><dt>Fecha</dt><dd class="analytics-sale-text">{{ v.fecha }}</dd></div>
                    <div class="finance-pair"><dt>Cajero</dt><dd class="analytics-sale-text">{{ v.cajero }}</dd></div>
                    <div class="finance-pair finance-pair--total"><dt>Total</dt><dd class="finance-money">{{ moneda(v.total) }}</dd></div>
                </dl>
                <UiButton v-if="ticketAction" :href="`/ventas/${v.id}/ticket`" variant="secondary" class="finance-record__action" :aria-label="`Ver ticket ${v.folio}`">Ver ticket<UiIcon name="external" :size="14" /></UiButton>
            </article>
        </div>
    </template>
    <UiEmptyState v-else icon="receipt" :title="emptyTitle" :description="emptyDescription"><slot name="empty-action" /></UiEmptyState>
    <div class="finance-pagination"><Paginacion :links="links" premium /></div>
</template>

<style scoped>
.analytics-sale-folio { font-size: 13px; font-weight: 600; overflow-wrap: anywhere; }
.analytics-sales-cards { padding: 16px; }
.analytics-sales-cards :deep(.finance-record .finance-record__heading) { margin-bottom: 8px; }
.analytics-sales-cards :deep(.finance-pair) { padding: 6px 0; }
.analytics-sales-cards :deep(.finance-record .finance-pair) { border-bottom: 0; }
.analytics-sales-cards :deep(.finance-pair--total) { border-top: 1px solid var(--p-border); padding-top: 12px; margin-top: 6px; }
.analytics-sale-text { overflow-wrap: anywhere; }
.analytics-sales-table :deep(td) { max-width: 280px; }
</style>
