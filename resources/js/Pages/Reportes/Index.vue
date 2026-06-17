<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import StatCard from '../../Components/StatCard.vue';
import Grafica from '../../Components/Grafica.vue';
import Paginacion from '../../Components/Paginacion.vue';
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
        borderColor: '#8B0000',
        backgroundColor: 'rgba(139,0,0,0.08)',
        fill: true,
        tension: 0.35,
        pointBackgroundColor: '#8B0000',
    }],
}));

const datosPagos = computed(() => ({
    labels: ['💵 Efectivo', '💳 Tarjeta'],
    datasets: [{
        data: [props.totalEfectivo, props.totalTarjeta],
        backgroundColor: ['#17a2b8', '#6f42c1'],
        borderWidth: 0,
    }],
}));
</script>

<template>
    <AppLayout>
        <Head title="Reportes" />

        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
            <div>
                <h2 class="text-primary fs-3 mb-1">📊 Reportes</h2>
                <p class="text-secondary small mb-0">{{ rangoTexto }}</p>
            </div>

            <!-- Filtros -->
            <div class="d-flex gap-2 flex-wrap align-items-center">
                <Link
                    v-for="p in periodos"
                    :key="p.clave"
                    :href="`/reportes?periodo=${p.clave}`"
                    class="btn btn-sm fw-semibold"
                    :class="periodo === p.clave ? 'btn-primary' : 'btn-outline-secondary'"
                >
                    {{ p.texto }}
                </Link>

                <form class="d-flex gap-2 align-items-center flex-wrap" @submit.prevent="aplicarPersonalizado">
                    <input v-model="desdeLocal" type="date" class="form-control form-control-sm" style="width: auto;">
                    <span class="text-secondary small">al</span>
                    <input v-model="hastaLocal" type="date" class="form-control form-control-sm" style="width: auto;">
                    <button type="submit" class="btn btn-sm btn-primary fw-semibold">Aplicar</button>
                </form>
            </div>
        </div>

        <!-- Métricas -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-3">
                <StatCard etiqueta="Total ventas" :valor="totalVentas" sufijo="transacciones" color="#8B0000" />
            </div>
            <div class="col-6 col-xl-3">
                <StatCard etiqueta="Ingresos totales" :valor="moneda(totalIngresos)" sufijo="en el período" color="#28a745" />
            </div>
            <div class="col-6 col-xl-3">
                <StatCard etiqueta="💵 Efectivo" :valor="moneda(totalEfectivo)" sufijo="cobrado" color="#17a2b8" />
            </div>
            <div class="col-6 col-xl-3">
                <StatCard etiqueta="💳 Tarjeta" :valor="moneda(totalTarjeta)" sufijo="cobrado" color="#6f42c1" />
            </div>
        </div>

        <!-- Gráficas -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-xl-8">
                <div class="card h-100">
                    <div class="card-header encabezado">📈 Ventas por día</div>
                    <div class="card-body">
                        <Grafica tipo="line" :datos="datosLinea" :alto="250"
                                 :opciones="{ plugins: { legend: { display: false } } }" />
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-4">
                <div class="card h-100">
                    <div class="card-header encabezado">💳 Por método de pago</div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <Grafica tipo="doughnut" :datos="datosPagos" :alto="220" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Más vendidos + listado -->
        <div class="row g-3">
            <div class="col-12 col-xl-4">
                <div class="card h-100">
                    <div class="card-header encabezado">🏆 Productos más vendidos</div>
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
                            Sin ventas en este período.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-8">
                <div class="card">
                    <div class="card-header encabezado">Listado de ventas</div>
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Folio</th>
                                    <th>Fecha</th>
                                    <th>Cajero</th>
                                    <th>Método</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="v in ventas.data" :key="v.id">
                                    <td>
                                        <Link :href="`/ventas/${v.id}/ticket`" class="fw-bold text-primary text-decoration-none">
                                            {{ v.folio }}
                                        </Link>
                                    </td>
                                    <td class="small">{{ v.fecha }}</td>
                                    <td class="small">{{ v.cajero }}</td>
                                    <td class="small">{{ v.metodo_pago === 'efectivo' ? '💵 Efectivo' : '💳 Tarjeta' }}</td>
                                    <td class="text-end fw-bold text-primary">{{ moneda(v.total) }}</td>
                                </tr>
                                <tr v-if="!ventas.data.length">
                                    <td colspan="5" class="text-center text-secondary py-4">
                                        Sin ventas en este período.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-white py-3">
                        <Paginacion :links="ventas.links" />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
