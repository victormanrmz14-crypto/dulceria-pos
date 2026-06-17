<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import StatCard from '../Components/StatCard.vue';
import Grafica from '../Components/Grafica.vue';
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

const datosGrafica = computed(() => ({
    labels: props.ultimos7Dias.map((d) => d.fecha),
    datasets: [{
        label: 'Ventas $',
        data: props.ultimos7Dias.map((d) => d.total),
        borderColor: '#8B0000',
        backgroundColor: 'rgba(139,0,0,0.08)',
        fill: true,
        tension: 0.35,
        pointBackgroundColor: '#8B0000',
    }],
}));
</script>

<template>
    <AppLayout>
        <Head title="Inicio" />

        <div class="mb-4">
            <h2 class="text-primary fs-3 mb-1">Bienvenido, {{ user?.nombre }} 👋</h2>
            <p class="text-secondary small mb-0">
                {{ new Date().toLocaleDateString('es-MX', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}
            </p>
        </div>

        <!-- ======== ADMIN ======== -->
        <template v-if="esAdmin">
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-4 col-xl">
                    <StatCard etiqueta="Ventas hoy" :valor="ventasHoy" sufijo="transacciones"
                              color="#8B0000" href="/ventas/historial" />
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <StatCard etiqueta="Total del día" :valor="moneda(totalHoy)" sufijo="ingresos" color="#28a745" />
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <StatCard etiqueta="Productos activos" :valor="productosActivos" sufijo="en catálogo"
                              color="#ffc107" href="/productos" />
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <StatCard etiqueta="Stock bajo" :valor="stockBajo" sufijo="requieren reabasto"
                              color="#dc3545" href="/productos?stock_bajo=1" />
                </div>
                <div class="col-12 col-md-4 col-xl">
                    <Link href="/caja" class="stat-card-link d-block h-100">
                        <div class="card h-100 border-0 text-white text-center" style="background: var(--dulce-rojo);">
                            <div class="card-body py-3 d-flex flex-column justify-content-center">
                                <div class="fs-3">🏦</div>
                                <div class="fw-bold">Ir a Caja</div>
                                <small v-if="ultimoCorte" class="opacity-75">Último corte: {{ ultimoCorte.fecha }}</small>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-xl-8">
                    <div class="card h-100">
                        <div class="card-header encabezado">📈 Ventas últimos 7 días</div>
                        <div class="card-body">
                            <Grafica tipo="line" :datos="datosGrafica" :alto="260"
                                     :opciones="{ plugins: { legend: { display: false } } }" />
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-4">
                    <div class="card h-100">
                        <div class="card-header encabezado">🏆 Más vendidos</div>
                        <div class="card-body">
                            <div v-for="(item, i) in masVendidos" :key="i"
                                 class="d-flex align-items-center gap-2 py-2 border-bottom">
                                <span class="badge rounded-pill bg-primary">{{ i + 1 }}</span>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold small">{{ item.nombre }}</div>
                                    <div class="text-secondary" style="font-size: 0.75rem;">
                                        {{ numero(item.total_vendido) }} unidades
                                    </div>
                                </div>
                                <span class="fw-bold text-primary small">{{ moneda(item.total_importe) }}</span>
                            </div>
                            <p v-if="!masVendidos.length" class="text-secondary text-center small py-4 mb-0">
                                Sin ventas registradas.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header encabezado d-flex justify-content-between align-items-center">
                    <span>Últimas ventas del día</span>
                    <Link href="/ventas/historial" class="small fw-semibold">Ver todas →</Link>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <tbody>
                            <tr v-for="v in ultimasVentas" :key="v.id">
                                <td>
                                    <Link :href="`/ventas/${v.id}/ticket`" class="fw-bold text-primary text-decoration-none">
                                        {{ v.folio }}
                                    </Link>
                                    <div class="text-secondary small">{{ v.hora }} — {{ v.cajero }}</div>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold text-primary">{{ moneda(v.total) }}</span>
                                    <div class="text-secondary small">
                                        {{ v.metodo_pago === 'efectivo' ? '💵 Efectivo' : '💳 Tarjeta' }}
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!ultimasVentas.length">
                                <td class="text-center text-secondary small py-4">Aún no hay ventas hoy.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
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
