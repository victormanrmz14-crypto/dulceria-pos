<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import UiIcon from '../Ui/UiIcon.vue';
import UiButton from '../Ui/UiButton.vue';
import UiEmptyState from '../Ui/UiEmptyState.vue';
import StatCard from '../StatCard.vue';
import { moneda } from '../../utils';

defineProps<{
    nombre?: string;
    fecha: string;
    misVentasHoy: number;
    miTotalHoy: number | string;
    miEfectivo: number | string;
    miTarjeta: number | string;
    miEfectivoModal: number | string;
    ultimoCorte?: { fecha: string; dinero_en_caja: number } | null;
    misUltimasVentas: { id: number; folio: string; hora: string; total: number; metodo_pago: string }[];
}>();
</script>

<template>
    <div class="p-page-heading"><div><p class="p-page-context">Hola, {{ nombre }}</p><h1 class="p-page-title">Mi actividad</h1><p class="p-page-date"><UiIcon name="calendar" :size="15" />{{ fecha }}</p></div><div class="p-page-actions"><UiButton href="/caja" variant="secondary"><UiIcon name="cash" />Mi caja</UiButton><UiButton href="/ventas"><UiIcon name="cart" />Ir al punto de venta</UiButton></div></div>
    <section class="p-metrics" aria-label="Mi actividad desde el último corte">
        <StatCard premium etiqueta="Mis ventas" :valor="misVentasHoy" sufijo="desde mi último corte" icon="receipt" href="/ventas/historial" />
        <StatCard premium etiqueta="Mi total" :valor="moneda(miTotalHoy)" sufijo="cobrado" icon="cash" />
        <StatCard premium etiqueta="Efectivo" :valor="moneda(miEfectivo)" sufijo="en ventas" icon="cash" />
        <StatCard premium etiqueta="Tarjeta" :valor="moneda(miTarjeta)" sufijo="en ventas" icon="card" />
    </section>
    <div class="cashier-context"><div><UiIcon name="cash" :size="18" /><span>Efectivo esperado en mi caja <strong>{{ moneda(miEfectivoModal) }}</strong></span></div><Link href="/caja" class="p-text-link">Consultar caja<UiIcon name="arrow" :size="15" /></Link><p v-if="ultimoCorte">Último corte: {{ ultimoCorte.fecha }}</p><p v-else>Actividad desde el inicio de operaciones. Aún no tienes un corte.</p></div>
    <section class="p-panel" aria-labelledby="cashier-sales-title">
        <div class="p-panel__heading"><div><h2 id="cashier-sales-title" class="p-panel__title">Mis últimas ventas</h2><p class="p-panel__description">Desde mi último corte</p></div><Link href="/ventas/historial" class="p-text-link">Ver historial<UiIcon name="arrow" :size="16" /></Link></div>
        <template v-if="misUltimasVentas.length">
            <div class="p-table-scroll cashier-sales-desktop" tabindex="0" role="region" aria-label="Mis últimas ventas, tabla desplazable"><table class="p-table"><thead><tr><th scope="col">Folio</th><th scope="col">Hora</th><th scope="col">Método</th><th scope="col" class="p-table__amount">Total</th></tr></thead><tbody><tr v-for="venta in misUltimasVentas" :key="venta.id"><td><Link :href="`/ventas/${venta.id}/ticket`" class="p-ticket-link" :aria-label="`Ver ticket ${venta.folio}`">{{ venta.folio }}<UiIcon name="external" :size="14" /></Link></td><td class="p-table__muted">{{ venta.hora }}</td><td><span class="p-payment"><UiIcon :name="venta.metodo_pago === 'efectivo' ? 'cash' : 'card'" :size="15" />{{ venta.metodo_pago === 'efectivo' ? 'Efectivo' : 'Tarjeta' }}</span></td><td class="p-table__amount">{{ moneda(venta.total) }}</td></tr></tbody></table></div>
            <ul class="cashier-sales-mobile" aria-label="Mis últimas ventas"><li v-for="venta in misUltimasVentas" :key="venta.id"><div><Link :href="`/ventas/${venta.id}/ticket`" class="p-ticket-link" :aria-label="`Ver ticket ${venta.folio}`">{{ venta.folio }}<UiIcon name="external" :size="14" /></Link><span class="cashier-sales-mobile__meta">{{ venta.hora }}<span class="p-payment"><UiIcon :name="venta.metodo_pago === 'efectivo' ? 'cash' : 'card'" :size="14" />{{ venta.metodo_pago === 'efectivo' ? 'Efectivo' : 'Tarjeta' }}</span></span></div><strong>{{ moneda(venta.total) }}</strong></li></ul>
        </template>
        <UiEmptyState v-else icon="receipt" title="Todavía no tienes ventas en este período" description="Aquí aparecerán tus ventas con su folio, hora y forma de pago." />
    </section>
</template>

<style scoped>
.cashier-context { display: flex; align-items: center; flex-wrap: wrap; gap: 8px 24px; padding: 20px 24px; margin-bottom: 24px; border: 1px solid var(--p-border); border-radius: var(--p-radius-panel); background: var(--p-surface); }
.cashier-context > div { display: flex; align-items: center; gap: 12px; min-width: 0; }
.cashier-context > div > span { color: var(--p-muted); font-size: 13px; }
.cashier-context strong { display: block; margin-top: 4px; color: var(--p-text); font-size: 20px; font-weight: 600; font-variant-numeric: tabular-nums; white-space: nowrap; }
.cashier-context > a { display: inline-flex; align-items: center; gap: 6px; min-height: 44px; margin-left: auto; font-size: 13px; }
.cashier-context > p { width: 100%; margin: 4px 0 0; color: var(--p-muted); font-size: 12px; }
.cashier-sales-mobile { display: none; }
@media (max-width: 767.98px) {
    .cashier-sales-desktop { display: none; }
    .cashier-sales-mobile { display: block; list-style: none; padding: 0; margin: 0; }
    .cashier-sales-mobile li { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px 16px; padding: 12px 16px 16px; }
    .cashier-sales-mobile li + li { border-top: 1px solid var(--p-border); }
    .cashier-sales-mobile li > div { min-width: 0; }
    .cashier-sales-mobile .p-ticket-link { white-space: normal; overflow-wrap: anywhere; }
    .cashier-sales-mobile__meta { display: flex; align-items: center; flex-wrap: wrap; gap: 8px 12px; color: var(--p-muted); font-size: 12px; }
    .cashier-sales-mobile strong { font-size: 16px; font-weight: 600; font-variant-numeric: tabular-nums; white-space: nowrap; }
}
@media (max-width: 479.98px) { .cashier-context { padding: 16px; margin-bottom: 16px; } .cashier-context > a { margin-left: 0; } }
</style>
