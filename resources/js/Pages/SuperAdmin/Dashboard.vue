<script setup>
import { Head, Link } from "@inertiajs/vue3";
import SuperAdminLayout from "../../Layouts/SuperAdminLayout.vue";
import SaHeading from "../../Components/SuperAdmin/SaHeading.vue";
import SaMetric from "../../Components/SuperAdmin/SaMetric.vue";
import SaChart from "../../Components/SuperAdmin/SaChart.vue";
import SaQueryError from "../../Components/SuperAdmin/SaQueryError.vue";
const props = defineProps({
    kpis: Object,
    periodo: Object,
    grafica: Array,
    alertas: Array,
    criterios: Array,
});
const money = (n) =>
    new Intl.NumberFormat("es-MX", {
        style: "currency",
        currency: "MXN",
    }).format(n);
</script>
<template>
    <SuperAdminLayout>
        <Head title="Vista general · Superadministración" />
        <SaHeading
            title="Vista general"
            description="Una mirada a los negocios y a su actividad comercial."
        >
            <span class="sa-status">
                <span>{{ periodo.label }}</span>
            </span>
        </SaHeading>
        <SaQueryError v-if="!kpis" />
        <template v-else>
            <section v-if="!kpis.registradas" class="sa-panel sa-panel-body">
                <h2>Aún no hay dulcerías registradas</h2>
                <p>La activación y las señales de seguimiento aparecerán cuando se registren negocios.</p>
            </section>
            <div class="sa-metrics">
                <SaMetric
                    label="Dulcerías registradas"
                    :value="kpis.registradas"
                    detail="Total de negocios registrados"
                    icon="store"
                />
                <SaMetric
                    label="Habilitadas"
                    :value="kpis.habilitadas"
                    detail="Estado administrativo habilitado"
                    icon="shield"
                />
                <SaMetric
                    label="Con ventas en 30 días"
                    :value="kpis.con_ventas"
                    detail="Al menos una venta en el período"
                    icon="receipt"
                />
                <SaMetric
                    label="Nuevas en el período"
                    :value="kpis.nuevas"
                    detail="Registros en los últimos 30 días"
                    icon="plus"
                />
            </div>
            <p class="sa-note sa-section-gap">
                Período: {{ periodo.label }} · {{ periodo.zona }}. Incluye hoy hasta
                la hora de consulta y los 29 días anteriores. La actividad incluye
                negocios habilitados y deshabilitados con ventas registradas.
            </p>
            <div class="sa-grid sa-section-gap" style="align-items: start">
                <section class="sa-panel" style="margin-bottom: 0">
                    <header><h2>Activación comercial</h2></header>
                    <div class="sa-attention-summary">
                        <strong>{{ kpis.sin_primera_venta }}</strong>
                        <Link href="/superadmin/dulcerias?actividad=nunca" class="sa-link">Pendientes de primera venta</Link>
                    </div>
                    <div class="sa-panel-body">
                        <p>No existe ninguna venta válida en su historial hasta la consulta, sin importar su fecha de registro o estado administrativo.</p>
                        <p class="sa-note">Un negocio habilitado tiene permiso administrativo de uso. La actividad comercial se comprueba con ventas registradas.</p>
                        <Link href="/superadmin/atencion" class="sa-link">Consultar seguimiento de activación</Link>
                    </div>
                </section>
                <section class="sa-panel" style="margin-bottom: 0">
                    <header><h2>Negocios que requieren atención</h2></header>
                    <div class="sa-attention-summary">
                        <strong>{{ kpis.atencion }}</strong>
                        <Link
                            href="/superadmin/atencion"
                            class="sa-link"
                        >
                            Revisar negocios
                        </Link>
                    </div>
                    <ul v-if="alertas.length" class="sa-alert-list">
                        <li v-for="tenant in alertas" :key="tenant.id">
                            <Link :href="`/superadmin/dulcerias/${tenant.id}`">
                                {{ tenant.nombre }}
                            </Link>
                            <p>{{ tenant.problemas.join(" · ") }}</p>
                        </li>
                    </ul>
                    <p v-else class="sa-empty">
                        Sin situaciones de atención según estos criterios.
                    </p>
                    <details class="sa-chart-data">
                        <summary>Ver criterios de seguimiento</summary>
                        <ul>
                            <li v-for="criterion in criterios" :key="criterion.tipo">{{ criterion.definicion }}</li>
                        </ul>
                    </details>
                    <div class="sa-panel-body sa-note">
                        Un negocio se cuenta una sola vez. Son señales de seguimiento, no pruebas de abandono ni de fallos técnicos.
                        <br />
                        Se muestran hasta seis negocios.
                        <Link href="/superadmin/monitoreo" class="sa-link">
                            Consultar monitoreo técnico
                        </Link>
                    </div>
                </section>
            </div>
            <div class="sa-two sa-section-gap">
                <SaChart
                    title="Nuevas dulcerías"
                    description="Registros diarios durante el período seleccionado."
                    :rows="grafica"
                    field="registros"
                    label="Registros"
                />
                <SaChart
                    title="Ventas registradas"
                    description="Cantidad de operaciones comerciales por día."
                    :rows="grafica"
                    field="ventas"
                    label="Ventas"
                />
            </div>
            <section class="sa-panel">
                <header>
                    <div>
                        <h2>Volumen comercial registrado</h2>
                        <p>Importes de las dulcerías durante el período</p>
                    </div>
                    <strong class="sa-money">{{ money(kpis.importe) }}</strong>
                </header>
                <div class="sa-panel-body">
                    <p>{{ kpis.ventas }} {{ kpis.ventas === 1 ? 'venta registrada' : 'ventas registradas' }} en el período.</p>
                    <p class="sa-note" style="margin: 0">
                        Estos importes corresponden a las ventas de los negocios.
                        No representan ingresos de Dulcería POS.
                    </p>
                </div>
            </section>
        </template>
    </SuperAdminLayout>
</template>
