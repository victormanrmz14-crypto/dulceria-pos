<script setup>
import { computed, onBeforeUnmount, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import FinanceWorkspace from '../../Components/Finanzas/FinanceWorkspace.vue';
import FinancialMetric from '../../Components/Finanzas/FinancialMetric.vue';
import UiButton from '../../Components/Ui/UiButton.vue';
import UiIcon from '../../Components/Ui/UiIcon.vue';
import UiFormField from '../../Components/Ui/UiFormField.vue';
import UiEmptyState from '../../Components/Ui/UiEmptyState.vue';
import AnalyticsChart from '../../Components/Analitica/AnalyticsChart.vue';
import AnalyticsRanking from '../../Components/Analitica/AnalyticsRanking.vue';
import AnalyticsSalesList from '../../Components/Analitica/AnalyticsSalesList.vue';
import { moneda, numero } from '../../utils';

const props = defineProps({
    periodo: String,
    desde: String,
    hasta: String,
    rangoTexto: String,
    totalVentas: Number,
    totalIngresos: Number,
    totalEfectivo: Number,
    totalTarjeta: Number,
    graficaDias: Array,
    masVendidos: Array,
    ventas: Object,
});

const periodos = [
    { clave: 'hoy', texto: 'Hoy' },
    { clave: 'ayer', texto: 'Ayer' },
    { clave: '7dias', texto: 'Últimos 7 días' },
    { clave: '30dias', texto: 'Últimos 30 días' },
];

const desdeLocal = ref(props.desde);
const hastaLocal = ref(props.hasta);

const aplicarPersonalizado = () => {
    if (!desdeLocal.value || !hastaLocal.value) return;
    router.get('/reportes', {
        periodo: 'personalizado',
        desde: desdeLocal.value,
        hasta: hastaLocal.value,
    }, { preserveState: true });
};

const datosLinea = computed(() => ({
    labels: props.graficaDias.map((d) => d.fecha),
    datasets: [{
        label: 'Ventas $',
        data: props.graficaDias.map((d) => d.total),
    }],
}));

const datosPagos = computed(() => ({
    labels: ['Efectivo', 'Tarjeta'],
    datasets: [{
        data: [props.totalEfectivo, props.totalTarjeta],
    }],
}));
const page = usePage();
const filtering = ref(false);
const pendingFilterVisits = new Set();
const stopStart = router.on('start', (event) => {
    if (event.detail.visit.url.pathname !== '/reportes') return;
    pendingFilterVisits.add(event.detail.visit);
    filtering.value = true;
});
const stopFinish = router.on('finish', (event) => {
    pendingFilterVisits.delete(event.detail.visit);
    filtering.value = pendingFilterVisits.size > 0;
});
onBeforeUnmount(() => { stopStart(); stopFinish(); });

const hayVentas = computed(() => props.totalVentas > 0);
const hayImportesPagos = computed(() => props.totalEfectivo !== 0 || props.totalTarjeta !== 0);
const ranking = computed(() => props.masVendidos.map((item) => ({
    label: item.nombre,
    quantity: `${numero(item.total_vendido)} unidades`,
    value: moneda(item.total_importe),
})));
</script>

<template>
    <AppLayout premium v-slot="{ theme }">
        <Head title="Reportes" />
        <FinanceWorkspace>
            <div class="p-page-heading reports-heading">
                <div><h1 class="p-page-title">Reportes y analítica</h1><p class="finance-description">Ventas, ingresos y productos del período seleccionado.</p></div>
                <p class="reports-range" role="status"><UiIcon name="calendar" :size="16" /><span>{{ rangoTexto }}</span></p>
            </div>

            <section class="p-panel reports-filters" aria-labelledby="reports-filter-title" :aria-busy="filtering || undefined">
                <div class="reports-filter-periods">
                    <h2 id="reports-filter-title" class="reports-filter-label">Período</h2>
                    <nav class="reports-periods" aria-label="Seleccionar período">
                        <Link v-for="p in periodos" :key="p.clave" :href="`/reportes?periodo=${p.clave}`" class="reports-period" :class="{ 'is-active': periodo === p.clave }" :aria-current="periodo === p.clave ? 'page' : undefined">{{ p.texto }}</Link>
                    </nav>
                    <p v-if="page.props.errors?.periodo" class="reports-error" role="alert"><UiIcon name="warning" :size="14" />{{ page.props.errors.periodo }}</p>
                </div>
                <form @submit.prevent="aplicarPersonalizado" class="reports-date-form">
                    <p class="reports-filter-label reports-custom-label">Rango personalizado<span v-if="periodo === 'personalizado'" class="reports-custom-active"><UiIcon name="success" :size="14" />Activo</span></p>
                    <div class="reports-date-fields">
                        <UiFormField etiqueta="Desde" :error="page.props.errors?.desde"><template #default="{ control }"><input v-bind="control" v-model="desdeLocal" name="desde" type="date" autocomplete="off" class="p-field-control"></template></UiFormField>
                        <UiFormField etiqueta="Hasta" :error="page.props.errors?.hasta"><template #default="{ control }"><input v-bind="control" v-model="hastaLocal" name="hasta" type="date" autocomplete="off" class="p-field-control"></template></UiFormField>
                        <UiButton type="submit" :loading="filtering" class="reports-apply">{{ filtering ? 'Aplicando…' : 'Aplicar rango' }}</UiButton>
                    </div>
                </form>
            </section>

            <div class="finance-metrics reports-metrics" aria-label="Indicadores del período">
                <FinancialMetric label="Ingresos por ventas" :value="moneda(totalIngresos)" icon="chart" description="Total vendido en el período" prominent />
                <FinancialMetric label="Total de ventas" :value="numero(totalVentas)" icon="receipt" description="Transacciones registradas" />
                <FinancialMetric label="Ventas en efectivo" :value="moneda(totalEfectivo)" icon="cash" description="Cobrado en efectivo" />
                <FinancialMetric label="Ventas con tarjeta" :value="moneda(totalTarjeta)" icon="card" description="Cobrado con tarjeta" />
            </div>

            <div class="reports-charts">
                <section class="p-panel reports-trend" aria-labelledby="reports-trend-title">
                    <div class="p-panel__heading"><div><h2 id="reports-trend-title" class="p-panel__title">Ingresos por día</h2><p class="p-panel__description">Importes de ventas, en pesos mexicanos</p></div><UiIcon name="chart" class="finance-muted" /></div>
                    <template v-if="hayVentas">
                        <div class="reports-trend-body"><AnalyticsChart kind="line" :data="datosLinea" :color="theme['--p-brand-ink']" label="Gráfica de ingresos por ventas para cada día del período. Los valores completos están en Datos por día." /></div>
                        <details class="reports-chart-data">
                            <summary>Datos por día</summary>
                            <dl class="reports-days" tabindex="0" aria-label="Ingresos diarios del período"><div v-for="(dia, index) in graficaDias" :key="index"><dt>{{ dia.fecha }}</dt><dd>{{ moneda(dia.total) }}</dd></div></dl>
                        </details>
                    </template>
                    <UiEmptyState v-else icon="chart" title="Sin ventas para graficar" description="Selecciona otro período para consultar los ingresos por día." />
                </section>
                <section class="p-panel reports-payments" aria-labelledby="reports-payments-title">
                    <div class="p-panel__heading"><div><h2 id="reports-payments-title" class="p-panel__title">Por método de pago</h2><p class="p-panel__description">Importes cobrados en ventas</p></div><UiIcon name="card" class="finance-muted" /></div>
                    <template v-if="hayVentas && hayImportesPagos">
                        <div class="reports-payment-chart"><AnalyticsChart kind="doughnut" :data="datosPagos" :color="theme['--p-brand-ink']" label="Distribución de ingresos por ventas entre efectivo y tarjeta. Los importes completos aparecen debajo." /></div>
                        <dl class="reports-payment-values">
                            <div><dt><span class="reports-payment-dot" :style="{ backgroundColor: theme['--p-brand-ink'] }" aria-hidden="true"></span><UiIcon name="cash" :size="16" />Efectivo</dt><dd>{{ moneda(totalEfectivo) }}</dd></div>
                            <div><dt><span class="reports-payment-dot reports-payment-dot--card" aria-hidden="true"></span><UiIcon name="card" :size="16" />Tarjeta</dt><dd>{{ moneda(totalTarjeta) }}</dd></div>
                        </dl>
                    </template>
                    <UiEmptyState v-else icon="card" :title="hayVentas ? 'Sin importes por método de pago' : 'Sin ventas en este período'" description="La distribución se mostrará cuando haya importes de ventas para comparar." />
                </section>
            </div>

            <section class="p-panel finance-section" aria-labelledby="reports-ranking-title">
                <div class="p-panel__heading"><div><h2 id="reports-ranking-title" class="p-panel__title">Productos más vendidos</h2><p class="p-panel__description">Ordenados por cantidad vendida</p></div><UiIcon name="package" class="finance-muted" /></div>
                <AnalyticsRanking :items="ranking" />
            </section>

            <section class="p-panel finance-section" aria-labelledby="reports-sales-title">
                <div class="p-panel__heading"><div><h2 id="reports-sales-title" class="p-panel__title">Historial de ventas</h2><p class="p-panel__description">Fecha, cajero y método de pago de cada venta</p></div><UiIcon name="receipt" class="finance-muted" /></div>
                <AnalyticsSalesList :sales="ventas.data" :links="ventas.links" />
            </section>
        </FinanceWorkspace>
    </AppLayout>
</template>

<style scoped>
.reports-heading { margin-bottom: 24px; }
.reports-range { display: flex; align-items: flex-start; gap: 8px; margin: 0; font-size: 12px; color: var(--p-muted); line-height: 1.6; }
.reports-range .p-icon { margin-top: 2px; }
.reports-filters { display: grid; grid-template-columns: minmax(0, 1fr); margin-bottom: 24px; }
.reports-filter-periods { min-width: 0; padding: 16px; }
.reports-filter-label { font: inherit; font-size: 13px; font-weight: 500; margin: 0 0 12px; color: var(--p-text); }
.reports-periods { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
.reports-period { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 10px; border: 1px solid #c9ccd3; border-radius: var(--p-radius-control); color: var(--p-text); background: var(--p-surface); text-decoration: none; font-size: 13px; line-height: 1.4; font-weight: 500; }
.reports-period:hover { background: var(--p-muted-surface); border-color: #9196a1; }
.reports-period.is-active { background: var(--p-surface); border-color: var(--p-brand-ink); color: var(--p-brand-ink); font-weight: 600; box-shadow: inset 0 -2px 0 var(--p-brand-ink); }
.reports-date-form { padding: 16px; border-top: 1px solid var(--p-border); min-width: 0; }
.reports-custom-label { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
.reports-custom-active { display: inline-flex; align-items: center; gap: 4px; font-size: 12px; color: var(--p-brand-ink); }
.reports-date-fields { display: grid; grid-template-columns: minmax(0, 1fr); gap: 12px; align-items: end; }
.reports-date-fields :deep(.p-field-control) { padding-inline: 10px; }
.reports-error { display: flex; gap: 6px; align-items: baseline; font-size: 12px; line-height: 1.5; margin: 8px 0 0; color: var(--p-danger); }
.reports-charts { display: grid; grid-template-columns: minmax(0, 1fr); gap: 24px; align-items: start; }
.reports-charts :deep(.p-empty) { min-height: 128px; padding: 24px 16px; }
.reports-trend-body { padding: 8px 8px 0; }
.reports-chart-data { border-top: 1px solid var(--p-border); padding: 4px 16px; color: var(--p-muted); font-size: 12px; }
.reports-chart-data summary { min-height: 44px; align-content: center; cursor: pointer; width: fit-content; max-width: 100%; }
.reports-chart-data summary:hover { color: var(--p-text); }
.reports-days { max-height: 260px; overflow-y: auto; overscroll-behavior: contain; margin: 0 0 8px; padding: 0; }
.reports-days > div { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: baseline; gap: 4px 12px; border-top: 1px solid var(--p-border); padding: 10px 0; }
.reports-days dt { font-weight: 400; }
.reports-days dd { margin: 0 0 0 auto; font-variant-numeric: tabular-nums; white-space: nowrap; font-weight: 500; color: var(--p-text); }
.reports-payment-chart { padding: 16px 16px 8px; }
.reports-payment-values { margin: 0; padding: 0 16px 8px; }
.reports-payment-values > div { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: baseline; gap: 6px 12px; padding: 12px 0; }
.reports-payment-values dt { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 400; color: var(--p-muted); }
.reports-payment-values dd { margin: 0 0 0 auto; font-size: 14px; font-weight: 600; font-variant-numeric: tabular-nums; white-space: nowrap; }
.reports-payment-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.reports-payment-dot--card { width: 12px; height: 12px; border-radius: 2px; border: 1px solid #737b8c; background: repeating-linear-gradient(135deg, #737b8c 0 3px, #ffffff 3px 4px); }
@media (min-width: 360px) { .reports-date-fields { grid-template-columns: repeat(2, minmax(0, 1fr)); } .reports-apply { grid-column: 1 / -1; } }
@media (min-width: 600px) { .reports-periods { display: flex; flex-wrap: wrap; } .reports-period { flex: 1 1 auto; } .reports-date-fields { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) auto; } .reports-apply { grid-column: auto; } }
@media (min-width: 1200px) {
    .reports-filters { grid-template-columns: minmax(0, 1fr) minmax(0, 1.15fr); }
    .reports-filter-periods, .reports-date-form { padding: 20px; }
    .reports-date-form { border-top: 0; border-left: 1px solid var(--p-border); }
    .reports-date-fields { gap: 12px; }
    .reports-charts { grid-template-columns: minmax(0, 1.8fr) minmax(0, 1fr); }
}
</style>
