<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import StatCard from '../Components/StatCard.vue';
import Grafica from '../Components/Grafica.vue';
import WelcomeModal from '../Components/WelcomeModal.vue';
import UiIcon from '../Components/Ui/UiIcon.vue';
import UiButton from '../Components/Ui/UiButton.vue';
import UiEmptyState from '../Components/Ui/UiEmptyState.vue';
import { premiumTheme } from '../Components/Ui/theme';
import '../../css/premium.css';
import { moneda, numero } from '../utils';

const props = defineProps({
    ventasHoy: Number,
    totalHoy: [Number, String],
    productosActivos: Number,
    stockBajo: Number,
    ultimasVentas: Array,
    ultimos7Dias: Array,
    masVendidos: Array,
    misVentasHoy: Number,
    miTotalHoy: [Number, String],
    miEfectivo: [Number, String],
    miTarjeta: [Number, String],
    misUltimasVentas: Array,
    ultimoCorte: Object,
    miEfectivoModal: [Number, String],
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const esAdmin = computed(() => user.value?.rol === 'admin');
const mostrarBienvenida = computed(() => !!page.props.flash?.bienvenida);

const datosGrafica = computed(() => ({
    labels: props.ultimos7Dias.map((d) => d.fecha),
    datasets: [{
        label: 'Ventas $',
        data: props.ultimos7Dias.map((d) => d.total),
        borderColor: premiumTheme(page.props.tenantConfig?.colores)['--p-brand-ink'],
        backgroundColor: 'transparent',
        fill: false,
        tension: 0.2,
        pointBackgroundColor: premiumTheme(page.props.tenantConfig?.colores)['--p-brand-ink'],
        pointRadius: 2.5,
        pointHoverRadius: 5,
        pointHitRadius: 16,
        borderWidth: 2,
    }],
}));
const hayIngresosGrafica = computed(() => props.ultimos7Dias.some((dia) => Number(dia.total) !== 0));
const fechaHoy = new Date().toLocaleDateString('es-MX', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
const opcionesGrafica = {
    animation: false,
    interaction: { intersect: false, mode: 'index' },
    layout: { padding: { top: 8, right: 8 } },
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#202126', padding: 12, cornerRadius: 6, displayColors: false,
            titleFont: { family: 'DM Sans, sans-serif', size: 12, weight: '500' },
            bodyFont: { family: 'DM Sans, sans-serif', size: 13 },
            callbacks: { label: (context) => moneda(context.parsed.y) },
        },
    },
    scales: {
        x: { grid: { display: false }, border: { display: false }, ticks: { color: '#666973', padding: 10, maxRotation: 0, maxTicksLimit: 7, font: { family: 'DM Sans, sans-serif', size: 11 } } },
        y: {
            beginAtZero: true, border: { display: false }, grid: { color: '#eef0f3', drawTicks: false },
            ticks: {
                color: '#666973', padding: 10, maxTicksLimit: 5, font: { family: 'DM Sans, sans-serif', size: 11 },
                // Compact axis labels only; tooltips and the data disclosure retain full amounts.
                callback: (value) => '$' + new Intl.NumberFormat('es-MX', { notation: 'compact', maximumFractionDigits: 1 }).format(value),
            },
        },
    },
};
</script>

<template>
    <AppLayout :premium="esAdmin" v-slot="{ theme }">
        <Head title="Inicio" />

        <WelcomeModal v-if="mostrarBienvenida" :premium="esAdmin" :theme="theme" />

        <div v-if="!esAdmin" class="mb-4">
            <h2 class="text-primary fs-3 mb-1">Bienvenido, {{ user?.nombre }} 👋</h2>
            <p class="text-secondary small mb-0">
                {{ new Date().toLocaleDateString('es-MX', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}
            </p>
        </div>

        <!-- Premium is deliberately opt-in for this administrator dashboard only. -->
        <template v-if="esAdmin">
            <div class="p-page-heading">
                <div>
                    <p class="p-page-context">Hola, {{ user?.nombre }}</p>
                    <h1 class="p-page-title">Resumen del día</h1>
                    <p class="p-page-date"><UiIcon name="calendar" :size="15" />{{ fechaHoy }}</p>
                </div>
                <div class="p-page-actions">
                    <UiButton href="/caja" variant="secondary"><UiIcon name="cash" />Ir a Caja</UiButton>
                    <UiButton href="/ventas"><UiIcon name="cart" />Ir al punto de venta</UiButton>
                </div>
            </div>

            <section class="p-metrics" aria-label="Indicadores de hoy">
                <StatCard premium etiqueta="Ventas hoy" :valor="ventasHoy" sufijo="transacciones" icon="receipt" href="/ventas/historial" />
                <StatCard premium etiqueta="Total del día" :valor="moneda(totalHoy)" sufijo="ingresos" icon="cash" />
                <StatCard premium etiqueta="Productos activos" :valor="productosActivos" sufijo="en catálogo" icon="package" href="/productos" />
                <StatCard premium etiqueta="Stock bajo" :valor="stockBajo" :sufijo="Number(stockBajo) > 0 ? 'requieren reabasto' : 'sin alertas de inventario'"
                    :icon="Number(stockBajo) > 0 ? 'warning' : 'package'" :attention="Number(stockBajo) > 0" href="/productos?stock_bajo=1" />
            </section>

            <div v-if="ultimoCorte" class="p-cash-context"><UiIcon name="receipt" :size="16" /><span>Último corte: <strong>{{ ultimoCorte.fecha }}</strong></span><Link href="/caja">Consultar caja<UiIcon name="arrow" :size="15" /></Link></div>

            <div class="p-insights" :class="{ 'p-insights--empty': !hayIngresosGrafica && !masVendidos.length }">
                <section class="p-panel p-sales-chart" aria-labelledby="sales-chart-title">
                    <div class="p-panel__heading"><div><h2 id="sales-chart-title" class="p-panel__title">Ventas de la semana</h2><p class="p-panel__description">Ingresos por día</p></div><span class="p-period">Últimos 7 días</span></div>
                    <div v-if="hayIngresosGrafica" class="p-chart-body">
                        <div role="img" aria-label="Gráfica de ingresos por día durante los últimos siete días">
                            <Grafica tipo="line" :datos="datosGrafica" :alto="220" :opciones="opcionesGrafica" aria-hidden="true" />
                        </div>
                        <details class="p-chart-data"><summary>Ver datos de la gráfica</summary><dl><div v-for="dia in ultimos7Dias" :key="dia.fecha"><dt>{{ dia.fecha }}</dt><dd>{{ moneda(dia.total) }}</dd></div></dl></details>
                    </div>
                    <UiEmptyState v-else icon="chart" title="Aún no hay ingresos en este período" description="Aquí podrás consultar el total vendido de cada día." />
                    <div class="p-panel__footnote"><UiIcon name="info" :size="14" />Importes en pesos mexicanos</div>
                </section>

                <section class="p-panel p-top-products" aria-labelledby="top-products-title">
                    <div class="p-panel__heading"><div><h2 id="top-products-title" class="p-panel__title">Más vendidos</h2><p class="p-panel__description">Productos destacados</p></div><UiIcon name="package" class="p-panel__heading-icon" /></div>
                    <ol v-if="masVendidos.length" class="p-ranking">
                        <li v-for="(item, i) in masVendidos" :key="item.nombre + i" class="p-ranking__item"
                            :class="{ 'p-ranking__item--long-amount': moneda(item.total_importe).length > 12 }">
                            <span class="p-ranking__position">{{ i + 1 }}</span>
                            <div class="p-ranking__product"><strong>{{ item.nombre }}</strong><span>{{ numero(item.total_vendido) }} unidades</span></div>
                            <span class="p-ranking__amount">{{ moneda(item.total_importe) }}</span>
                        </li>
                    </ol>
                    <UiEmptyState v-else icon="package" title="Sin productos vendidos" description="Aquí aparecerán tus productos más vendidos cuando registres ventas." />
                </section>
            </div>

            <section class="p-panel p-recent-sales" aria-labelledby="recent-sales-title">
                <div class="p-panel__heading"><div><h2 id="recent-sales-title" class="p-panel__title">Últimas ventas del día</h2><p class="p-panel__description">El detalle de tu operación, a un clic</p></div><Link href="/ventas/historial" class="p-text-link">Ver historial<UiIcon name="arrow" :size="16" /></Link></div>
                <template v-if="ultimasVentas.length">
                    <p class="p-table-hint">Desliza para consultar todas las columnas.</p>
                    <div class="p-table-scroll" tabindex="0" role="region" aria-label="Últimas ventas, tabla desplazable">
                        <table class="p-table">
                            <thead><tr><th scope="col">Folio</th><th scope="col">Hora</th><th scope="col">Cajero</th><th scope="col">Método</th><th scope="col" class="p-table__amount">Total</th></tr></thead>
                            <tbody><tr v-for="v in ultimasVentas" :key="v.id">
                                <td><Link :href="`/ventas/${v.id}/ticket`" class="p-ticket-link" :aria-label="`Ver ticket ${v.folio}`">{{ v.folio }}<UiIcon name="external" :size="14" /></Link></td>
                                <td class="p-table__muted">{{ v.hora }}</td><td>{{ v.cajero }}</td>
                                <td><span class="p-payment"><UiIcon :name="v.metodo_pago === 'efectivo' ? 'cash' : 'card'" :size="15" />{{ v.metodo_pago === 'efectivo' ? 'Efectivo' : 'Tarjeta' }}</span></td>
                                <td class="p-table__amount">{{ moneda(v.total) }}</td>
                            </tr></tbody>
                        </table>
                    </div>
                </template>
                <UiEmptyState v-else icon="receipt" title="Todavía no hay ventas hoy" description="Las ventas de hoy aparecerán con su folio, cajero y forma de pago." />
            </section>
        </template>

        <!-- ======== CAJERO ======== -->
        <template v-else>
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <StatCard etiqueta="Mis ventas" :valor="misVentasHoy" sufijo="desde mi último corte" color="#8B0000" />
                </div>
                <div class="col-6 col-md-3">
                    <StatCard etiqueta="Mi total" :valor="moneda(miTotalHoy)" sufijo="cobrado" color="#28a745" />
                </div>
                <div class="col-6 col-md-3">
                    <StatCard etiqueta="💵 Efectivo" :valor="moneda(miEfectivo)" sufijo="en ventas" color="#17a2b8" />
                </div>
                <div class="col-6 col-md-3">
                    <StatCard etiqueta="💳 Tarjeta" :valor="moneda(miTarjeta)" sufijo="en ventas" color="#6f42c1" />
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                    <Link href="/ventas" class="stat-card-link d-block">
                        <div class="card border-0 text-white text-center" style="background: var(--dulce-rojo);">
                            <div class="card-body py-4">
                                <div class="fs-2">🛒</div>
                                <div class="fw-bold fs-5">Ir al Punto de Venta</div>
                            </div>
                        </div>
                    </Link>
                </div>
                <div class="col-12 col-md-6">
                    <Link href="/caja" class="stat-card-link d-block">
                        <div class="card border-0 text-center" style="border: 2px solid var(--dulce-rojo) !important;">
                            <div class="card-body py-4">
                                <div class="fs-2">🏦</div>
                                <div class="fw-bold fs-5 text-primary">Mi Caja</div>
                                <small class="text-secondary">
                                    Efectivo esperado: {{ moneda(miEfectivoModal) }}
                                </small>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>

            <div class="card">
                <div class="card-header encabezado">Mis últimas ventas</div>
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <tbody>
                            <tr v-for="v in misUltimasVentas" :key="v.id">
                                <td>
                                    <Link :href="`/ventas/${v.id}/ticket`" class="fw-bold text-primary text-decoration-none">
                                        {{ v.folio }}
                                    </Link>
                                    <div class="text-secondary small">{{ v.hora }}</div>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold text-primary">{{ moneda(v.total) }}</span>
                                    <div class="text-secondary small">
                                        {{ v.metodo_pago === 'efectivo' ? '💵 Efectivo' : '💳 Tarjeta' }}
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!misUltimasVentas.length">
                                <td class="text-center text-secondary small py-4">Sin ventas en este período.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>
    </AppLayout>
</template>
