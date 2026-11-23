<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import UiButton from '../../Components/Ui/UiButton.vue';
import UiIcon from '../../Components/Ui/UiIcon.vue';
import UiEmptyState from '../../Components/Ui/UiEmptyState.vue';
import FinanceWorkspace from '../../Components/Finanzas/FinanceWorkspace.vue';
import FinancialMetric from '../../Components/Finanzas/FinancialMetric.vue';
import { ArrowLeft } from '@lucide/vue';
import { moneda } from '../../utils';

defineProps({
    corte: Object,
    ventas: Array,
});
</script>

<template>
    <AppLayout premium>
        <Head :title="`Corte #${corte.id}`" />
        <FinanceWorkspace>
            <div class="p-page-heading">
                <div><h1 class="p-page-title">Corte de caja #{{ corte.id }}</h1><p class="finance-description">Resumen financiero y recuento del período.</p></div>
                <UiButton href="/cortes" variant="secondary"><ArrowLeft :size="18" :stroke-width="1.75" aria-hidden="true" />Volver a cortes</UiButton>
            </div>

            <section class="p-panel cut-info" aria-labelledby="cut-info-title">
                <div class="p-panel__heading"><h2 id="cut-info-title" class="p-panel__title">Información del corte</h2><UiIcon name="receipt" class="finance-muted" /></div>
                <dl class="cut-info__grid">
                    <div><dt>Cajero</dt><dd>{{ corte.cajero }}</dd></div>
                    <div><dt>Inicio del período</dt><dd>{{ corte.fecha_inicio }}</dd></div>
                    <div><dt>Fecha del corte</dt><dd>{{ corte.fecha_corte }}</dd></div>
                    <div><dt>Transacciones</dt><dd>{{ corte.num_transacciones }}</dd></div>
                </dl>
            </section>

            <div class="finance-metrics finance-metrics--three" aria-label="Totales del corte">
                <FinancialMetric label="Efectivo esperado" :value="moneda(corte.total_efectivo)" icon="cash" description="Saldo en efectivo del corte" />
                <FinancialMetric label="Tarjeta" :value="moneda(corte.total_tarjeta)" icon="card" description="Cobrado en el período" />
                <FinancialMetric label="Total general" :value="moneda(corte.total_general)" icon="chart" description="Efectivo + tarjeta" prominent />
            </div>

            <div class="cut-reconciliation">
                <section class="p-panel" aria-labelledby="cut-count-title">
                    <div class="p-panel__heading"><div><h2 id="cut-count-title" class="p-panel__title">Recuento de caja</h2><p class="p-panel__description">Comparación con el efectivo esperado</p></div><UiIcon name="cash" class="finance-muted" /></div>
                    <dl class="finance-pairs">
                        <div class="finance-pair"><dt>Efectivo contado</dt><dd class="finance-money">{{ corte.efectivo_contado !== null ? moneda(corte.efectivo_contado) : '—' }}</dd></div>
                        <div class="finance-pair cut-difference" :class="corte.efectivo_contado === null ? '' : corte.diferencia === 0 ? 'cut-difference--balanced' : 'cut-difference--warning'">
                            <dt>Diferencia</dt>
                            <dd v-if="corte.efectivo_contado !== null" class="cut-difference__value"><UiIcon :name="corte.diferencia === 0 ? 'success' : 'warning'" :size="16" /><span class="finance-money">{{ corte.diferencia === 0 ? 'Cuadra exacto' : (corte.diferencia > 0 ? `Sobran ${moneda(corte.diferencia)}` : `Faltan ${moneda(-corte.diferencia)}`) }}</span></dd>
                            <dd v-else class="finance-muted">No capturado</dd>
                        </div>
                        <div v-if="corte.dinero_en_caja !== null" class="finance-pair"><dt>Dinero conservado en caja</dt><dd class="finance-money">{{ moneda(corte.dinero_en_caja) }}</dd></div>
                    </dl>
                    <p v-if="corte.dinero_en_caja !== null" class="cut-count__note">Fondo para el siguiente turno.</p>
                </section>
                <section v-if="corte.notas" class="p-panel" aria-labelledby="cut-notes-title">
                    <div class="p-panel__heading"><h2 id="cut-notes-title" class="p-panel__title">Notas del corte</h2><UiIcon name="info" class="finance-muted" /></div>
                    <p class="cut-notes">{{ corte.notas }}</p>
                </section>
            </div>

            <section class="p-panel finance-section" aria-labelledby="cut-sales-title">
                <div class="p-panel__heading"><div><h2 id="cut-sales-title" class="p-panel__title">Ventas incluidas en el corte</h2><p class="p-panel__description">Consulta el ticket de cada venta</p></div><UiIcon name="receipt" class="finance-muted" /></div>
                <template v-if="ventas.length">
                    <table class="finance-table">
                        <caption class="finance-table-caption">Ventas incluidas en el corte</caption>
                        <thead><tr><th scope="col">Folio</th><th scope="col">Fecha</th><th scope="col">Método</th><th scope="col" class="finance-number">Total</th></tr></thead>
                        <tbody>
                            <tr v-for="v in ventas" :key="v.id"><td><Link :href="`/ventas/${v.id}/ticket`" class="finance-link" :aria-label="`Ver ticket ${v.folio}`">{{ v.folio }}<UiIcon name="external" :size="14" /></Link></td><td>{{ v.hora }}</td><td><span class="finance-payment"><UiIcon :name="v.metodo_pago === 'efectivo' ? 'cash' : 'card'" :size="16" />{{ v.metodo_pago === 'efectivo' ? 'Efectivo' : 'Tarjeta' }}</span></td><td class="finance-number finance-money">{{ moneda(v.total) }}</td></tr>
                        </tbody>
                    </table>
                    <div class="finance-cards cut-sales-cards">
                        <article v-for="v in ventas" :key="v.id" class="finance-record">
                            <div class="finance-record__heading"><Link :href="`/ventas/${v.id}/ticket`" class="finance-link" :aria-label="`Ver ticket ${v.folio}`">{{ v.folio }}<UiIcon name="external" :size="14" /></Link><span class="finance-payment"><UiIcon :name="v.metodo_pago === 'efectivo' ? 'cash' : 'card'" :size="16" />{{ v.metodo_pago === 'efectivo' ? 'Efectivo' : 'Tarjeta' }}</span></div>
                            <p class="finance-record__meta">{{ v.hora }}</p>
                            <dl class="finance-pairs"><div class="finance-pair finance-pair--total"><dt>Total</dt><dd class="finance-money">{{ moneda(v.total) }}</dd></div></dl>
                        </article>
                    </div>
                </template>
                <UiEmptyState v-else icon="receipt" title="Sin ventas en el período" description="Este corte no incluye ventas para mostrar." />
            </section>
        </FinanceWorkspace>
    </AppLayout>
</template>

<style scoped>
.cut-info { margin-bottom: 24px; }
.cut-info__grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; padding: 20px 16px; margin: 0; }
.cut-info__grid dt { font-size: 12px; font-weight: 400; color: var(--p-muted); margin-bottom: 6px; }
.cut-info__grid dd { margin: 0; font-size: 13px; font-weight: 500; overflow-wrap: anywhere; }
.cut-reconciliation { display: grid; grid-template-columns: minmax(0, 1fr); align-items: start; gap: 24px; }
.cut-difference__value { display: inline-flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: 8px; }
.cut-difference--balanced { color: var(--p-success); }
.cut-difference--warning { color: var(--p-warning); background: var(--p-warning-bg); }
.cut-count__note { padding: 0 16px 16px; margin: 0; color: var(--p-muted); font-size: 12px; }
.cut-notes { margin: 0; padding: 16px; font-size: 13px; line-height: 1.7; white-space: pre-wrap; overflow-wrap: anywhere; }
.cut-sales-cards { padding: 16px; }
@media (min-width: 600px) { .cut-info__grid { grid-template-columns: repeat(2, minmax(0, 1fr)); padding: 20px; } .cut-count__note { padding-inline: 20px; } .cut-notes { padding: 20px; } }
@media (min-width: 1200px) { .cut-info__grid { grid-template-columns: 1.1fr 1fr 1fr .7fr; } .cut-reconciliation { grid-template-columns: minmax(0, 1.2fr) minmax(0, 1fr); } }
</style>
